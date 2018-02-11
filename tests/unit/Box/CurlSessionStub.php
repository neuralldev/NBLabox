<?php
/**
 * This file is part of Jeedom's NBLabox plugin.
 * @copyright 2018, Lionel SAURON
 * @licence https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace NBLabox\Box;

use NBLabox\Curl\CurlRequest;
use NBLabox\Curl\CurlResponse;
use NBLabox\Curl\CurlSessionInterface;

/** Stub class to replace {@link CurlSession} into test. */
class CurlSessionStub implements CurlSessionInterface
{
    /** Default values for the response */
    const DEFAULT_RESPONSE = [
        'content' => null,
        'url' => null,
        'httpCode' => -1,
        'contentType' => null,
    ];

    /** @var array Mapping between {@link CurlRequest} and {@link CurlResponse} */
    private $urlMapping;

    /**
     * Constructor.
     * @param array $urlMapping Mapping between {@link CurlRequest} and {@link CurlResponse}
     */
    public function __construct(array $urlMapping)
    {
        $this->urlMapping = $urlMapping;
    }

    /** {@inheritDoc} */
    public function sendRequest(CurlRequest $request)
    {
        $requestCurlOptions = $request->getOptions();

        if (isset($this->urlMapping[$requestCurlOptions[CURLOPT_URL]])) {
            // Complete data from urlMapping with CurlSessionStub::DEFAULT_RESPONSE
            $responseData = $this->urlMapping[$requestCurlOptions[CURLOPT_URL]] + CurlSessionStub::DEFAULT_RESPONSE;
        } else {
            $responseData = CurlSessionStub::DEFAULT_RESPONSE;
        }

        return new CurlResponse($responseData['content'], $responseData['url'], $responseData['httpCode'], $responseData['contentType']);
    }
}
