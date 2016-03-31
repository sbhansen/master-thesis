# import RPi.GPIO as GPIO
import json
import time
import requests

# Settings for program
doorPinMapping         = { 0 : 4 }
doorEndpointUrl        = "http://www.bragehansen.com/dev/hig/master/api/door.php"
holdDoorOpenForSeconds = 1
checkApiEveryNthSecond = 0.5

# Poll the api and act accordingly
def listen( doorId, seconds ):
    elapsedTime = 0
    while elapsedTime <= seconds:
        if( shouldOpenDoor( doorId ) ):
            openDoor( doorId )
            time.sleep( holdDoorOpenForSeconds )
            closeDoor( doorId )
            elapsedTime += holdDoorOpenForSeconds
        else:
            time.sleep( checkApiEveryNthSecond )
            elapsedTime += checkApiEveryNthSecond

def openDoor( doorId ):
    pin = doorPinMapping[ doorId ];
    GPIO.setmode( GPIO.BCM )
    GPIO.setup( pin, GPIO.OUT )
    GPIO.output( pin, GPIO.HIGH )

def closeDoor( doorId ):
    ## hack workaround to
    ## GPIO.output( pin, GPIO.LOW )
    ## not working as expected
    GPIO.cleanup()

def shouldOpenDoor( doorId ):
    state = getDoorState( doorId )
    return( state == "open" )

def getDoorState( doorId ):
    query = { id : doorId }
    response = requests.get( doorEndpointUrl, data=query )
    result = response.json()
    if( result[ "success" ] ):
        return result[ "message" ]
    else:
        return false

listen( 0, 60 )