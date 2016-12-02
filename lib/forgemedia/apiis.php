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

namespace forgemedia;

use forgemedia\apiis\CurrentIsWeather;
use Cmfcmf\OpenWeatherMap\Fetcher\CurlFetcher;
use Cmfcmf\OpenWeatherMap\Fetcher\FetcherInterface;
use Cmfcmf\OpenWeatherMap\Fetcher\FileGetContentsFetcher;


/**
 * Main class for the API.is-PHP-API. Only use this class.
 *
 * @api
 */
class apiis 
{
    /**
     * @var string The basic api url to fetch weather data from.
     */
     private $IsWeatherUrl = 'http://apis.is/weather/observations/en/?';
     
    /**
     * @var FetcherInterface The url fetcher.
     */
    private $fetcher;
     
    /**
     * Constructs the APIis object.
     *
     * @param null|FetcherInterface $fetcher The interface to fetch the data from API.is Defaults to
     *                                       CurlFetcher() if cURL is available. Otherwise defaults to
     *                                       FileGetContentsFetcher() using 'file_get_contents()'.
     *
     * @api
     */
    public function __construct($fetcher = null)
    {
        if (!isset($fetcher)) {
            $fetcher = (function_exists('curl_version')) ? new CurlFetcher() : new FileGetContentsFetcher();
        }

        $this->fetcher = $fetcher;
    }
    
    /**
     * Returns the current weather at the place you specified.
     *
     * @param string        $stations List of station numbers seperated by commas(,) or semicolons(;).
     * @param string        $time       1h = Fetch data from automatic weather stations that are updated on the hour.
     *                                  3h = Only fetch mixed data from manned and automatic weather stations that is updated every 3 hours.
     * @param string        $integrity  0 = an error will be returned if current data is not available.
     *                                  1 = last available numbers will be displayed, regardless of date.
     *
     * @throws OpenWeatherMap\Exception  If OpenWeatherMap returns an error.
     * @throws \InvalidArgumentException If an argument error occurs.
     *
     * @return CurrentWeather The weather object.
     *
     * There are three ways to specify the place to get weather information for:
     * - http://en.vedur.is/weather/stations/
     *
     * @api
     */
    public function getIsWeather($stations, $time, $integrity)
    {
        //Defaults
        if (!empty($time)){
            $time = '1h';
        }
        if (!empty($integrity)){
            $integrity = '0';
        }
        
        print_r('StationID: '.$stations.' Time: '.$time.' Integrity: '.$integrity);
        
        $answer = $this->getRawIsWeatherData($stations, $time, $integrity);
        
        //$xml = $this->parseXML($answer);
        //return new CurrentIsWeather($xml, $units);
        
        return $answer;
        
    }
    
    /**
     * Directly returns the xml/json/html string returned by API.is-PHP-API for the current weather.
     *
     * @param array|int|string $query The place to get weather information for. For possible values see ::getWeather.
     * @param string           $units Can be either 'metric' or 'imperial' (default). This affects almost all units returned.
     * @param string           $lang  The language to use for descriptions, default is 'en'. For possible values see http://openweathermap.org/current#multi.
     * @param string           $mode  The format of the data fetched. Possible values are 'json', 'html' and 'xml' (default).
     *
     * @return string Returns false on failure and the fetched data in the format you specified on success.
     *
     * Warning: If an error occurs, OpenWeatherMap ALWAYS returns json data.
     *
     * @api
     */
    public function getRawIsWeatherData($stations, $time, $integrity)
    {
        $url = $this->buildIsUrl($stations, $time, $integrity, $this->IsWeatherUrl);

        $result = $this->fetcher->fetch($url);
        
        return $result;
    }
    
    /**
     * Build the url to fetch weather data from.
     *
     * @param        $stations
     * @param        $time
     * @param        $integrity
     * @param string $url   The url to prepend.
     *
     * @return bool|string The fetched url, false on failure.
     */
    private function buildIsUrl($stations, $time, $integrity, $url)
    {
        $stationsUrl = $this->buildIsQueryUrlParameter($stations);
        
        $url = $url."$stationsUrl&time=$time&anytime=$integrity";
        
        return $url;
    }
    
    /**
     * Builds the stations string for the url.
     *
     * @param mixed $stations
     *
     * @return string The built query string for the url.
     *
     * @throws \InvalidArgumentException If the query parameter is invalid.
     */
    private function buildIsQueryUrlParameter($stations)
    {
        switch ($stations) {
            case is_string($stations):
                return 'stations='.urlencode($stations);
            default:
                throw new \InvalidArgumentException('Error: $stations has the wrong format. See the documentation of http://docs.apis.is/ to read about valid formats.');
        }
    }

}

?>