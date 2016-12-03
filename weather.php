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
require 'bower_components/indieteq-php-my-sql-pdo-database-class/Db.class.php';
require 'vendor/autoload.php';
require 'config.php';

//API.is class
// Should be added to autoload later via compser
require 'lib/forgemedia/apiis.php';

/*-------Configurations-------*/

// Language of data (try your own language here!):
$lang = 'en';

// Units (can be 'metric' or 'imperial' [default]):
$units = 'metric';

// Error ceching for API.is stations (0 = On)
$integrity = '0';

// 1h or 3h API.is weather data.
$time = '1h';

/*-------Weather Objects-------*/

// Create OpenWeatherMap object. 
// Don't use caching (take a look into Examples/Cache.php to see how it works).
$owm = new OpenWeatherMap('fd516fbde12b8f4e45b9fcb34b80fb84');

// Create API.is object 
$apiis = new apiis();

// Create database object 
$db = new Db();

//Extract weather data
try {
    
    //owmWeather($owm, $configs, $lang, $units);
    
    $isweather = apiisWeather($apiis, $configs, $time, $integrity);
    //print_r($isweather);
    
} catch(OWMException $e) {
    echo 'OpenWeatherMap exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').';
} catch(\Exception $e) {
    echo 'General exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').';
}

/*-------Functions-------*/

//Get OpenWeatherMap Data
function owmWeather($owmF, $configsF, $langF, $unitsF) {
    
    //Check to see if a list of cities exists
    if (!empty($configsF['owmcities'])) {
        
        //Loop through array to create each channel
        foreach ($configsF['owmcities'] as $key => $value) {
        
            //Get Weather data and add to array
            $weather[$key] = array(
                //$value => $owmF->getWeather($value, $unitsF, $langF)
            );
            
            //print_r($weather);
        }
    } else {
        
        //Config Error
        echo 'No cities have been listed in the configs file';
    }    
    
}

//Get API.is Data
function apiisWeather($apiisF, $configsF, $timeF, $integrityF) {
    
    //Check to see if a list of cities exists
    if (!empty($configsF['apiis'])) {
        
        //Loop through array to create each channel
        foreach ($configsF['apiis'] as $key => $value) {
            
            //Get Station ID
            $stationID = $value['sid'];
            
            //Get Weather data
            $weatherobj = $apiisF->getIsWeather($stationID, $timeF, $integrityF);
 
            //Add weather data object to array
            $weather = array(
                "time" =>
                $weatherobj->results['0']
            );
            
            
            
        }
        
        return $weather;
        
    } else {
        
        //Config Error
        echo 'No cities have been listed in the configs file';
    }    
    
}

//Basic database input
function SetWeather($weatherF, $dbF) {
    
// Insert
    $insert = $db->query(
        "INSERT INTO `weather`(`id`, `time`, `name`, `lat`, `lon`, `temperature`, `description`, `clouds`, `winddirection`, `windspeed`, `pressure`, `humidity`) VALUES (:time,:name,:lat,:lon,:temperature,:description,:clouds,:winddirection,:windspeed,:pressure,:humidity)", 
        array("f"=>"Vivek","age"=>"20")
        );

}