<?php
/**
 * This file is part of Jeedom's NBLabox plugin.
 * @copyright 2018, Lionel SAURON
 * @licence https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace NBLabox\Curl;

/**
 * A curl request definition.
 * Immutable object.
 */
class CurlRequest
{
    /** @var array Curl options for the request. */
    private $curlOptions = [];

    /**
     * Constructor.
     * @param array $options Curl options for the request.
     */
    public function __construct(array $options = [])
    {
        // Only keep options with integer key and not null value
        $this->curlOptions = array_filter($options, function ($value, $key) {
            return (is_int($key)) && ($value !== null);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Returns Curl options for this request (to be used with curl_setopt_array)
     * @return array
     */
    public function getOptions()
    {
        return $this->curlOptions;
    }

    /**
     * Returns the value of the option.
     * @param int $key CURLOPT_... constant
     * @return mixed|null the option value or null if options is not set
     */
    public function getOption($key)
    {
        return (isset($this->curlOptions[$key])) ? $this->curlOptions[$key] : null;
    }
}
