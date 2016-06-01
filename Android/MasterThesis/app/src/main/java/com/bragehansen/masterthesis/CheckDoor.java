package com.bragehansen.masterthesis;

import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.Button;
import android.widget.TextView;

import com.estimote.sdk.Beacon;
import com.estimote.sdk.BeaconManager;
import com.estimote.sdk.Region;
import com.estimote.sdk.Utils;

import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.UUID;

public class CheckDoor extends AppCompatActivity {

    TextView theText;
    Button theButton;
    BeaconManager beaconManager;
    Map<String, String> beacons;
    Map<String, String> beaconPosition;
    List<String> doorPosition;

    Double gridSpacingMeeters;
    Integer doorId;
    Double openAtDistance;

    List<String> coordinates;

    public CheckDoor self = this;

    private final String TAG = "CheckDoor";

    private static final UUID ESTIMOTE_PROXIMITY_UUID = UUID.fromString("B9407F30-F5F8-466E-AFF9-25556B57FE6D");
    private static final Region ALL_ESTIMOTE_BEACONS = new Region("rid", ESTIMOTE_PROXIMITY_UUID, null, null);

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_check_door);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        theText   = (TextView) findViewById(R.id.doorStatus);
        theButton = (Button) findViewById(R.id.OpenDoorButton);
        doorId    = 0;
        openAtDistance = 0.5;
        gridSpacingMeeters = 0.5;
        Integer scanForMilliseconds = 100;
        Integer delayForMilliseconds = 100;


        // Specify which beacons the app should be looking for
        // XXXX:xxxx => Major:Minor for beacons and must be replaced
        beacons = new HashMap<>();
        beacons.put("XXXX:xxxx",  "ICE");
        beacons.put("YYYY:yyyy", "BLUEBERRY");
        beacons.put("ZZZZ:zzzz",  "MINT");

        beaconPosition = new HashMap<>();
        beaconPosition.put( "ICE", "1:1" );
        beaconPosition.put( "BLUEBERRY", "4:4" );
        beaconPosition.put( "MINT", "1:4");

        doorPosition = new ArrayList<>();
        doorPosition.add( "5:2" );
        doorPosition.add( "5:3" );

        // A listener that checks if all beacons are in range
        // If all beacons are in range the users position is triangulated and logged.
        // For each new position the last vector of approach is calculated to see if the user is
        // intersecting with the door.
        beaconManager = new BeaconManager(this);
        beaconManager.setForegroundScanPeriod( scanForMilliseconds, delayForMilliseconds );
        beaconManager.setRangingListener(new BeaconManager.RangingListener() {
            @Override
            public void onBeaconsDiscovered(Region region, List<Beacon> list) {
                Map <String, Double> distances = new HashMap<>();
                for (Beacon beacon : list) {
                    Integer minor = beacon.getMinor();
                    Integer major = beacon.getMajor();

                    if (beacons.get( major + ":" + minor ) != null) {
                        Double distance;
                        distance = Utils.computeAccuracy( beacon );
                        String name = beacons.get(major + ":" + minor);
                        distances.put( name, distance );
                    }
                }

                // If all three beacons are in range, plot the phones position.
                if ( distances.size() == 3 ) {

                    // Height of triangle between 1:1, 1:4, and position gives us the x position.
                    // We use to sets of triangulations to get the average reported position
                    // based on the three measurements.
                    List<String> positionList = new ArrayList<>();
                    positionList.add(getDistanceFromPlane( 2.0, distances.get("ICE"), distances.get("MINT"), false ) );
                    positionList.add(getDistanceFromPlane( 2.0, distances.get("MINT"), distances.get("BLUEBERRY"), true ) );
                    coordinates.add( getAveragePosition( positionList ) );

                    if( userOnPathToDoor() ){
                        DoorOpener doorOpener = new DoorOpener();
                        doorOpener.execute( doorId );
                    }
                }
                else {
                    Log.d(TAG, "To few beacons " + distances.size() );
                }

            }

            // Returns boolean (true/false) depending on if coordinates gathered from beacons are
            // on approach vector with door and last position is inside the "openDoorAtDistance" distance.
            // If we are not concerned with Use Case 2 this can also be solved by checking if the user is in in one of the grid positions
            // adjasent to the door on either side.
            private Boolean userOnPathToDoor(){
                String firstCoordinate;
                String secondCoordinate;
                return true;
            }

            // Return the average grid position based on all positions in list
            private String getAveragePosition( List positions ){
                Integer xPos = 0;
                Integer yPos = 0;
                for (int index = 0; index < positions.size(); index++ ) {
                    String[] pos;
                    pos = positions.get( index ).toString().split(":");
                    xPos += Integer.parseInt( pos[0] );
                    yPos += Integer.parseInt( pos[1] );
                }
                return ( Math.round( xPos / positions.size() ) ) + ":" + ( Math.round( yPos / positions.size() ) ) ;
            }

            // Calculates and returns the distance from the base of the triangle using
            // origo (A) and the outermost X or Y (B) as the base of the triangle (c).
            // Distance a and b refers to the length of the two other legs of the triangle
            // Making h the height from the point where a and b meets (C) the distance from b,b to
            // origo (A) and 0,Y (B) in the triangle. See report figure additional information.
            //
            //                       C = unknown point
            //                      /|\
            //                     / | \
            //    legBlength = b  / h|  \ a = legAlength
            //                   /   |   \
            //       beacon 1 = A ---|--- B = bacon 2
            //                     d, c = entire base
            //
            private String getDistanceFromPlane( double legLengthA, double legLengthB, double baseLength, boolean rotate90degrees ){
                Double a = legLengthA;                           // distance to point A
                Double b = legLengthB;                           // distance to point B
                Double c = ( baseLength  / gridSpacingMeeters ); // distance between point A and B
                Double A;                                        // Angle A.
                Double C;                                        // Angle C.
                Double height;                                   // Distance from origo in Y direction
                Double length;                                   // Distance from origo in X direction (d)
                Double cosA;

                // Using Law of Cosine to calculate Cos( A )
                // Cos( A ) = ( b^2 + c^2 - a^2 ) / 2bc
                cosA = ( Math.pow( b, 2 ) + Math.pow( c, 2 ) - Math.pow( a, 2 ) ) / ( 2 * b * c );

                // Using law of cosine to find A in radians
                // A = cos^-1( cos( A ) )
                A = Math.acos(cosA);

                // Using Law of cosine to calculate h form A
                // a = b * Sin( A )
                height = b * Math.sin( A );

                // Using pythagoras theorem to find d
                // leg^2 + leg^2 = hypotenuse^2 =>
                // d^2 + height^2 = b^2
                // d = sqrt( b^2 - h^2  )
                length = Math.sqrt( Math.pow( b, 2 ) - Math.pow( height, 2 ) );

                // Dependent on if we are measuring the baseline or the "edge" of our grid.
                // If we are using the edge (position 4:4 and 1:4) the distances must be adjusted
                // relative to origo (1:1)
                Integer posX;
                Integer posY;
                // Map X/Y and adjust for grid size
                if( rotate90degrees ){
                    posX = (int) Math.round( ( baseLength - height )  / gridSpacingMeeters );
                    posY = (int) Math.round( length / gridSpacingMeeters );
                }
                else {
                    posY = (int) Math.round( height  / gridSpacingMeeters );
                    posX = (int) Math.round( length / gridSpacingMeeters );
                }

                return posY.toString() + ":" + posX.toString();
            }
        });

    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_check_door, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    @Override
    public void onStart() {
        super.onStart();
        beaconManager.connect(new BeaconManager.ServiceReadyCallback() {
            @Override
            public void onServiceReady() {
                try {
                    beaconManager.startRanging(ALL_ESTIMOTE_BEACONS);
                } catch (Exception e) {

                }
            }
        });
    }

    @Override
    public void onStop() {
        super.onStop();
        try {
            beaconManager.stopRanging(ALL_ESTIMOTE_BEACONS);
        } catch ( Exception e ) {
            Log.e(TAG, "Cannot stop but it does not matter now", e);
        }
    }

    // This is the controller that opens a given door.
    // It needs to run as a async task since it connects to the API.
    private class DoorOpener extends AsyncTask<Integer, Void, Boolean>{
        @Override
        protected  Boolean doInBackground( Integer... doorIdList ){
            Boolean success;
            Integer doorId = doorIdList[ 0 ];
            try{
                URL doorApi = new URL("http://www.bragehansen.com/dev/hig/master/api/door.php?id=" + doorId.toString() );
                HttpURLConnection httpCon = (HttpURLConnection) doorApi.openConnection();
                httpCon.setDoOutput(true);
                httpCon.setRequestMethod("PUT");
                OutputStreamWriter out = new OutputStreamWriter( httpCon.getOutputStream() );
                out.write("Resource content");
                out.close();
                httpCon.getInputStream();
                Log.d(TAG, "Opening door " + String.valueOf(doorApi) );
                success = true;
            }
            catch( Exception e ){
                e.printStackTrace();
                success = false;
            }
            return success;
        }
    }
}
