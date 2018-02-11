<?php
/**
 * This file is part of Jeedom's NBLabox plugin.
 * @copyright 2018, Lionel SAURON
 * @licence https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace NBLabox\Curl;

interface CurlSessionInterface
{
    /**
     * Send the request and return the response.
     * @param CurlRequest $request
     * @return CurlResponse
     */
    public function sendRequest(CurlRequest $request);
}
