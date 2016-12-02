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

require 'FetcherInterface.php';

/**
 * Class FileGetContentsFetcher.
 *
 * @internal
 */
class FileGetContentsFetcher implements FetcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetch($url)
    {
        return file_get_contents($url);
    }
}
