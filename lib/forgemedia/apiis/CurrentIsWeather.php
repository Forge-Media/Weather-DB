<?php
/**
 * API.is-PHP-API — A php api to parse weather data from http://docs.apis.is/.
 *
 * @license MIT
 *
 * Please see the LICENSE file distributed with this source code for further
 * information regarding copyright and licensing.
 *
 *
 * @see http://docs.apis.is/
 * 
 * By Jeremy Paton
 */
 
namespace forgemedia\apiis;

use forgemedia\apiis;
use forgemedia\apiis\Util\City;
use forgemedia\apiis\Util\Sun;
use forgemedia\apiis\Util\Temperature;
use forgemedia\apiis\Util\Unit;
use forgemedia\apiis\Util\Weather as WeatherObj;
use forgemedia\apiis\Util\Wind;

/**
 * Weather class used to hold the current weather data.
 */
class CurrentIsWeather
{
    /**
     * The city object.
     *
     * @var Util\City
     */
    public $city;

    /**
     * The temperature object.
     *
     * @var Util\Temperature
     */
    public $temperature;

    /**
     * @var Util\Unit
     */
    public $humidity;

    /**
     * @var Util\Unit
     */
    public $pressure;

    /**
     * @var Util\Wind
     */
    public $wind;

    /**
     * @var Util\Unit
     */
    public $clouds;

    /**
     * @var Util\Unit
     */
    public $precipitation;

    /**
     * @var Util\Weather
     */
    public $weather;

    /**
     * Create a new weather object.
     *
     * @param mixed  $data
     * @param string $units
     *
     * @internal
     */
    public function __construct($data)
    {
        $utctime = new \DateTimeZone('UTC');

        if ($data instanceof \SimpleXMLElement) {
            $this->city = new City($data->city['id'], $data->city['name'], $data->city->coord['lon'], $data->city->coord['lat'], $data->city->country);
            $this->temperature = new Temperature(new Unit($data->temperature['value'], $data->temperature['unit']), new Unit($data->temperature['min'], $data->temperature['unit']), new Unit($data->temperature['max'], $data->temperature['unit']));
            $this->humidity = new Unit($data->humidity['value'], $data->humidity['unit']);
            $this->pressure = new Unit($data->pressure['value'], $data->pressure['unit']);
            $this->wind = new Wind(new Unit($data->wind->speed['value'], $windSpeedUnit, $data->wind->speed['name']), new Unit($data->wind->direction['value'], $data->wind->direction['code'], $data->wind->direction['name']));
            $this->clouds = new Unit($data->clouds['value'], null, $data->clouds['name']);
            $this->precipitation = new Unit($data->precipitation['value'], $data->precipitation['unit'], $data->precipitation['mode']);
            $this->sun = new Sun(new \DateTime($data->city->sun['rise'], $utctime), new \DateTime($data->city->sun['set'], $utctime));
            $this->weather = new WeatherObj($data->weather['number'], $data->weather['value'], $data->weather['icon']);
            $this->lastUpdate = new \DateTime($data->lastupdate['value'], $utctime);
        } else {
            $this->city = new City($data->id, $data->name, $data->coord->lon, $data->coord->lat, $data->sys->country);
            $this->temperature = new Temperature(new Unit($data->main->temp, $units), new Unit($data->main->temp_min, $units), new Unit($data->main->temp_max, $units));
            $this->humidity = new Unit($data->main->humidity, '%');
            $this->pressure = new Unit($data->main->pressure, 'hPa');
            $this->wind = new Wind(new Unit($data->wind->speed, $windSpeedUnit), new Unit($data->wind->deg));
            $this->clouds = new Unit($data->clouds->all, '%');

            // the rain field is not always present in the JSON response
            // and sometimes it contains the field '1h', sometimes the field '3h'
            $rain = isset($data->rain) ? (array) $data->rain : [];
            $rainUnit = !empty($rain) ? key($rain) : '';
            $rainValue = !empty($rain) ? current($rain) : 0.0;
            $this->precipitation = new Unit($rainValue, $rainUnit);

            $this->sun = new Sun(\DateTime::createFromFormat('U', $data->sys->sunrise, $utctime), \DateTime::createFromFormat('U', $data->sys->sunset, $utctime));
            $this->weather = new WeatherObj($data->weather[0]->id, $data->weather[0]->description, $data->weather[0]->icon);
            $this->lastUpdate = \DateTime::createFromFormat('U', $data->dt, $utctime);
        }
    }
}