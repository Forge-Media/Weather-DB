<?PHP

/*
 __    __           _   _                     ___  ___ 
/ / /\ \ \___  __ _| |_| |__   ___ _ __      /   \/ __\
\ \/  \/ / _ \/ _` | __| '_ \ / _ \ '__|    / /\ /__\//
 \  /\  /  __/ (_| | |_| | | |  __/ |      / /_// \/  \
  \/  \/ \___|\__,_|\__|_| |_|\___|_|     /___,'\_____/
                                                       
A simple weather aggregate script written in PHP which stores data from multiple API sources.

Requires:
- OpenWeatherMap PHP API
*/

use forgemedia\apiis;
use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;

// Must point to composer's autoload file.
require 'vendor/autoload.php';
require 'config.php';

//API.is class
// Should be added to autoload later via compser
require 'lib/forgemedia/apiis.php';


// Language of data (try your own language here!):
$lang = 'en';

// Units (can be 'metric' or 'imperial' [default]):
$units = 'metric';

// Create OpenWeatherMap object. 
// Don't use caching (take a look into Examples/Cache.php to see how it works).
$owm = new OpenWeatherMap('fd516fbde12b8f4e45b9fcb34b80fb84');

// Create API.is object 
$apiis = new apiis();

$stations = '1';
$time = '1h';
$integrity = '0';

print_r($apiis->getIsWeather($stations, $time, $integrity));

//Extract weather data
/* try {


    //Check to see if a list of cities exists
    if (!empty($configs['owmcities'])) {
        
        //Loop through array to create each channel
        foreach ($configs['owmcities'] as $key => $value) {
        
            //Get Weather data and add to array
            $weather[$key] = array(
                $value => $owm->getWeather($value, $units, $lang)
            );
        }
    } else {
        
        //Config Error
        echo 'No cities have been listed in the configs file';
    }

    
} catch(OWMException $e) {
    echo 'OpenWeatherMap exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').';
} catch(\Exception $e) {
    echo 'General exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').';
} */

