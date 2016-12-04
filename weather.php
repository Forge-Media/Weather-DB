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
- indieteq-php-my-sql-pdo-database-class
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
$time = '3h';

/*-------Weather Objects-------*/

// Create OpenWeatherMap object. 
// Don't use caching (take a look into Examples/Cache.php to see how it works).
$owm = new OpenWeatherMap('fd516fbde12b8f4e45b9fcb34b80fb84');

// Create API.is object 
$apiis = new apiis();

// Create database object 
$db = new Db();

/**
 * Core statment which runs owmWeather and apiisWeather functions
 *
 * @throws OWMException errors
 *
*/
try {
    
    $current_time = date('H:i a');
    $sunrise = "10:00 am";
    $sunset = "4:00 pm";
    $date1 = DateTime::createFromFormat('H:i a', $current_time);
    $date2 = DateTime::createFromFormat('H:i a', $sunrise);
    $date3 = DateTime::createFromFormat('H:i a', $sunset);
    if ($date1 > $date2 && $date1 < $date3)
    {
        echo 'The time is: '.date('H:i a').' As it is DAY in Iceland no weather records are being kept </br>';
    } else {
        echo 'The time is: '.date('H:i a').' As it is NIGHT in Iceland weather records are being stored every hour </br>';
        
        //Get weather from Open Weather Map
        owmWeather($owm, $db, $configs, $lang, $units);
    
        //Get weather from Iceland Met Office via API.is
        apiisWeather($apiis, $db, $configs, $time, $integrity);  
    }
    
} catch(OWMException $e) {
    echo 'OpenWeatherMap exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').';
} catch(\Exception $e) {
    echo 'General exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').';
}

/*-------Functions-------*/

/**
 * Function which queries the OpenWeatherMap weather data API (openweathermap.org)
 *
 * @param object        $owmF The OpenWeatherMap object needs to be passed as a parameter.
 * @param object        $dbF The database object needs to be passed as a parameter.
 * @param array         $configsF A configuration array set in config.php
 * @param string        $langF      en = English
 * @param string        $unitsF     metric
 *
 * Warning: Minimal error checking.
 *
*/
function owmWeather($owmF, $dbF, $configsF, $langF, $unitsF) {
    
    //Check to see if a list of cities exists
    if (!empty($configsF['owmcities'])) {
        
        //Loop through array for each station
        foreach ($configsF['owmcities'] as $key => $value) {
            
            //Get weather data for station
            $weatherobj = $owmF->getWeather($value, $unitsF, $langF);
            
            //Add weather data to array
            $weather = array(
                "time" => $weatherobj->lastUpdate->format('Y/m/d h:i:s'),
                "name" => $weatherobj->city->name,
                "lat" => $weatherobj->city->lat,
                "lon" => $weatherobj->city->lon,
                "temperature" => $weatherobj->temperature->getValue(),
                "description" => strtolower($weatherobj->weather->description),
                "clouds" => $weatherobj->clouds->getValue(),
                "winddirection" => $weatherobj->wind->direction->getUnit(),
                "windspeed" => $weatherobj->wind->speed->getValue(),
                "pressure" => $weatherobj->pressure->getValue(),
                "humidity" => $weatherobj->humidity->getValue()
            );

        //Send weather data to database
        SetWeather($weather, $dbF); 
        }
    } else {
        
        //Config Error
        echo 'No cities have been listed in the configs file';
    }    
}

/**
 * Function which queries the Iceland Met Office weather data via the Icelandic API (apis.is)
 *
 * @param object        $apiisF The (apis.is) object needs to be passed as a parameter.
 * @param object        $dbF The database object needs to be passed as a parameter.
 * @param array         $configsF A configuration array set in config.php
 * @param string        $timeF          1h = Fetch data from automatic weather stations that are updated on the hour.
 *                                      3h = Only fetch mixed data from manned and automatic weather stations that is updated every 3 hours.
 * @param string        $integrityF     0 = an error will be returned if current data is not available.
 *                                      1 = last available numbers will be displayed, regardless of date.
 *
 * Warning: Minimal error checking.
 *
*/
function apiisWeather($apiisF, $dbF, $configsF, $timeF, $integrityF) {
    
    //Check to see if a list of cities exists
    if (!empty($configsF['apiis'])) {
        
        //Loop through array to create each channel
        foreach ($configsF['apiis'] as $key => $value) {
            
            //Get Station ID
            $stationID = $value['sid'];
            
            //Get weather data for station
            $weatherobj = $apiisF->getIsWeather($stationID, $timeF, $integrityF);
            $weatherobj = $weatherobj->results['0'];

            //Add weather data to array
            $weather = array(
                "time" => $weatherobj->time,
                "name" => $weatherobj->name,
                "lat" => $value['lat'],
                "lon" => $value['lon'],
                "temperature" => $weatherobj->T,
                "description" => strtolower($weatherobj->W),
                "clouds" => $weatherobj->N,
                "winddirection" => $weatherobj->D,
                "windspeed" => $weatherobj->F,
                "pressure" => $weatherobj->P,
                "humidity" => $weatherobj->RH
            );
   
        //Send weather data to database
        SetWeather($weather, $dbF); 
        }
    } else {
        
        //Config Error
        echo 'No cities have been listed in the configs file';
    }    
    
}

/**
 * Populates a SQL Database with weather data passed as an array.
 *
 * @param   array       $weatherF Array containing the filtered weather data.
 * @param   object      $dbF The database object needs to be passed as a parameter.
 *
 *
*/
function SetWeather($weatherF, $dbF) {
    
    // Insert weather data SQL query
    $insert = $dbF->query(
        "INSERT INTO `weather`(`time`, `name`, `lat`, `lon`, `temperature`, `description`, `clouds`, `winddirection`, `windspeed`, `pressure`, `humidity`) VALUES (:time,:name,:lat,:lon,:temperature,:description,:clouds,:winddirection,:windspeed,:pressure,:humidity)", 
        $weatherF
        );
    
    // Echo success
    if($insert > 0 ) {
        echo 'Succesfully added: '.$weatherF['name'].' to the database!'.'<br/>';
    } else {
        echo 'Error adding: '.$weatherF['name'].' to the database!'.'<br/>';
    }
}

/**
 * Returns the description of wind direction when given an angle in degrees.
 *
 * @param   int         $degF The angle in degrees to convert
 *
 * @return  string      Returns the string based description of the angle.
 *
 *
*/
function degConverter ($degF) {

    $val = floor(($degF / 22.5) + 0.5);
    $arr = array("N","NNE","NE","ENE","E","ESE","SE","SSE","S","SSW","SW","WSW","W","WNW","NW","NNW");
    $index = ($val % 16);
    return $arr[$index];

}