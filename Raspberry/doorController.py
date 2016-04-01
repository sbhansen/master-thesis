import RPi.GPIO as GPIO
import json
import time
import requests
# Configure script
listenToDoorId         = 0
listenForSeconds       = 30
doorPinMapping         = { 0 : 4 }
doorEndpointUrl        = "http://www.bragehansen.com/dev/hig/master/api/door.php"
holdDoorOpenForSeconds = 1
checkApiEveryNthSecond = 0.5
openDoorForState       = "open"


# Listen to the API
#
# @param (int) doorId
# @param (int) seconds - how many seconds to listen to the API
#
# @return void
#
def listen( doorId, seconds ):
    if( not doorId in doorPinMapping ):
        print( "Door %s not valid" % doorId )
        return

    elapsedSeconds = 0
    while elapsedSeconds <= seconds:
        if( shouldOpenDoor( doorId ) ):
            print( "Door Is Open" )
            openDoor( doorId )
            time.sleep( holdDoorOpenForSeconds )
            closeDoor( doorId )
            elapsedSeconds += holdDoorOpenForSeconds
        else:
            print( "Door Is Closed" )
            time.sleep( checkApiEveryNthSecond )
            elapsedSeconds += checkApiEveryNthSecond
        print( "Listened for %s seconds" % elapsedSeconds )
    print( "Finished listening" )

# Trigger the relay to switch on current
# by configuring GPIO and applying current to pin.
#
# @param (int) doorId
#
# @return void
#
def openDoor( doorId ):
    print( "Opening Door %s" % doorId )
    pin = doorPinMapping[ doorId ]
    GPIO.setmode( GPIO.BCM )
    GPIO.setup( pin, GPIO.OUT )
    GPIO.output( pin, GPIO.HIGH )

# Trigger the relay to switch off current
# by resetting GPIO setup.
# set a given door's state to "open" in the API.
#
# @param (int) doorId
#
# @return void
#
def closeDoor( doorId ):
    print( "Closing Door %s" % doorId )
    query = { "id": doorId }
    respons = requests.delete( doorEndpointUrl, params=query )
    ## hack workaround to
    ## GPIO.output( pin, GPIO.LOW )
    ## not working as expected
    GPIO.cleanup()

# Check API to see if state of given door is "open".
#
# @param (int) doorId
#
# @return bool
#
def shouldOpenDoor( doorId ):
    state = getDoorState( doorId )
    return( state == openDoorForState )

# Gets a given door's state from the API.
#
# @param (int) doorId
#
# @return string|bool ("open"|"close")
#
def getDoorState( doorId ):
    query = { "id": doorId }
    respons = requests.get( doorEndpointUrl, params=query )
    result = respons.json()
    if( result[ "success" ] ):
        return result[ "message" ]
    else:
        return false

# Off we go!
listen( listenToDoorId, listenForSeconds )