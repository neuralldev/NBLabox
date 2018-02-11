<?php
/**
 * This file is part of Jeedom's NBLabox plugin.
 * @copyright 2018, Lionel SAURON
 * @licence https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace NBLabox\Curl;

/**
 * The response of a curl request.
 * Immutable object.
 */
class CurlResponse
{
    /** @var string The response's content. */
    private $content;
    /** @var string The ending url of the response. */
    private $url;
    /**  @var int The HTTP status code. */
    private $httpCode;
    /** @var string The content type of the response's content. */
    private $contentType;

    /**
     * Constructor.
     * @param string $content
     * @param string $url
     * @param int    $httpCode
     * @param string $contentType
     */
    public function __construct($content, $url, $httpCode, $contentType)
    {
        $this->content = $content;
        $this->url = $url;
        $this->httpCode = $httpCode;
        $this->contentType = $contentType;
    }

    /**
     * Returns the response's content.
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Returns the ending url of the response.
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns the HTTP status code.
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Returns the content type of the response's content.
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }
}
