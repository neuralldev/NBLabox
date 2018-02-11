<?php
/**
 * This file is part of Jeedom's NBLabox plugin.
 * @copyright 2018, Lionel SAURON
 * @licence https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace NBLabox\Curl;

/**
 * Default implementation of {@link CurlSessionInterface} using curl functions.
 */
class CurlSession implements CurlSessionInterface
{
    /** Default curl options needed to send a safe request or retrieve the response. */
    const DEFAULT_CURL_OPTIONS = [
        CURLOPT_RETURNTRANSFER => true, // to return web page
    ];

    /** @var resource Curl handler */
    private $hCurl;

    /** Constructor. */
    public function __construct()
    {
        $this->hCurl = curl_init();
    }

    /** {@inheritDoc} */
    public function sendRequest(CurlRequest $request)
    {
        $requestCurlOptions = $request->getOptions();

        curl_reset($this->hCurl);
        curl_setopt_array($this->hCurl, $requestCurlOptions + CurlSession::DEFAULT_CURL_OPTIONS);

        $content = curl_exec($this->hCurl);
        $infos = curl_getinfo($this->hCurl);

        $response = new CurlResponse($content, $infos['url'], $infos['http_code'], $infos['content_type']);

        return $response;
    }
}
