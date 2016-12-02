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

namespace forgemedia\apiis\Fetcher;

/**
 * Interface FetcherInterface.
 *
 * @api
 */
interface FetcherInterface
{
    /**
     * Fetch contents from the specified url.
     *
     * @param string $url The url to be fetched.
     *
     * @return string The fetched content.
     *
     * @api
     */
    public function fetch($url);
}
