<?php
/**
 * This file is part of Jeedom's NBLabox plugin.
 * @copyright 2018, Lionel SAURON
 * @licence https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace NBLabox\Box;

use DOMDocument;
use DOMXPath;
use NBLabox\Curl\CurlRequest;
use NBLabox\Curl\CurlResponse;
use NBLabox\Curl\CurlSessionInterface;

class NumericableBox
{
    /** @var string IP address of the box. */
    private $ip;

    /** @var CurlSessionInterface To send curl request. */
    private $curlSession;

    /** @var array Cache of response to accelerate process. */
    private $responseCache = [];

    /** @var bool Is logged in the box. */
    private $isLoggedIn = false;

    /**
     * Constructor.
     * @param string               $ip          IP address of the box
     * @param CurlSessionInterface $curlSession To send curl request.
     */
    public function __construct($ip, CurlSessionInterface $curlSession)
    {
        if (is_string($ip)) {
            $this->ip = $ip;
        } else {
            throw new \InvalidArgumentException("IP is required");
        }

        $this->curlSession = $curlSession;
    }

    /**
     * Requests the index page.
     * @return CurlResponse
     */
    private function requestIndexPage()
    {
        $response = $this->readResponseFromCache('INDEX');
        if ($response !== null) {
            return $response;
        }

        $request = new CurlRequest([
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_AUTOREFERER => true, // to set referer on redirect
            CURLOPT_SSL_VERIFYPEER => 0,// to disable SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => 'https://' . $this->ip,
        ]);

        $response = $this->curlSession->sendRequest($request);

        if (($response->getHttpCode() !== 200)
            || ($response->getUrl() !== 'https://' . $this->ip . '/')
            || (stripos($response->getContent(), 'Votre adresse IP') === false)) {
            throw new \UnexpectedValueException('The index page is not as expected.');
        }

        $this->storeResponseIntoCache('INDEX', $response);
        return $response;
    }

    /**
     * Returns the response stored in the cache or null if not found.
     * @param string $requestName The name of the request
     * @return CurlResponse|null the response stored in the cache or null if not found
     */
    private function readResponseFromCache($requestName)
    {
        if (isset($this->responseCache[$requestName])) {
            return $this->responseCache[$requestName];
        }
        return null;
    }

    /**
     * Stores the response into the cache.
     * @param string       $requestName The name of the request
     * @param CurlResponse $response    The response to store
     */
    private function storeResponseIntoCache($requestName, CurlResponse $response)
    {
        $this->responseCache[$requestName] = $response;
    }

    public function getPublicIp()
    {
        $response = $this->requestIndexPage();
        $content = $response->getContent();

        $doc = new DOMDocument();
        @$doc->loadHTML($content);

        $xpath = new DOMXpath($doc);

        $nodes = $xpath->evaluate("//tr/td[starts-with(text(),'Votre adresse IP')]/following-sibling::td[1]/text()");

        return (($nodes->length > 0) && ($nodes[0]->nodeType === XML_TEXT_NODE)) ? $nodes[0]->wholeText : null;
    }

    public function getSoftwareVersion()
    {
        $response = $this->requestIndexPage();
        $content = $response->getContent();

        $doc = new DOMDocument();
        @$doc->loadHTML($content);

        $xpath = new DOMXpath($doc);

        $nodes = $xpath->evaluate("//tr/td[starts-with(text(),'Version logiciel')]/following-sibling::td[1]/text()");

        return (($nodes->length > 0) && ($nodes[0]->nodeType === XML_TEXT_NODE)) ? $nodes[0]->wholeText : null;
    }

    public function getHardwareVersion()
    {
        $response = $this->requestIndexPage();
        $content = $response->getContent();

        $doc = new DOMDocument();
        @$doc->loadHTML($content);

        $xpath = new DOMXpath($doc);

        $nodes = $xpath->evaluate("//tr/td[starts-with(text(),'Version matériel')]/following-sibling::td[1]/text()");

        return (($nodes->length > 0) && ($nodes[0]->nodeType === XML_TEXT_NODE)) ? $nodes[0]->wholeText : null;
    }

    public function getDownloadBandwidth()
    {
        $response = $this->requestIndexPage();
        $content = $response->getContent();

        $doc = new DOMDocument();
        @$doc->loadHTML($content);

        $xpath = new DOMXpath($doc);

        $nodes = $xpath->evaluate("//tr/td[starts-with(text(),'Débit Descendant maximum')]/following-sibling::td[1]/text()");
        $rawValue = (($nodes->length > 0) && ($nodes[0]->nodeType === XML_TEXT_NODE)) ? $nodes[0]->wholeText : null;

        // Cast String as double to remove unit
        return ($rawValue !== null) ? (double)$rawValue : null;
    }

    public function getUploadBandwidth()
    {
        $response = $this->requestIndexPage();
        $content = $response->getContent();

        $doc = new DOMDocument();
        @$doc->loadHTML($content);

        $xpath = new DOMXpath($doc);

        $nodes = $xpath->evaluate("//tr/td[starts-with(text(),'Débit Montant maximum')]/following-sibling::td[1]/text()");
        $rawValue = (($nodes->length > 0) && ($nodes[0]->nodeType === XML_TEXT_NODE)) ? $nodes[0]->wholeText : null;

        // Cast String as double to remove unit
        return ($rawValue !== null) ? (double)$rawValue : null;
    }

    public function getNetworkMask()
    {
        $response = $this->requestIndexPage();
        $content = $response->getContent();

        $doc = new DOMDocument();
        @$doc->loadHTML($content);

        $xpath = new DOMXpath($doc);

        $nodes = $xpath->evaluate("//tr/td[starts-with(text(),'Votre masque de sous réseau')]/following-sibling::td[1]/text()");

        return (($nodes->length > 0) && ($nodes[0]->nodeType === XML_TEXT_NODE)) ? $nodes[0]->wholeText : null;
    }

    public function getDns()
    {
        $response = $this->requestIndexPage();
        $content = $response->getContent();

        $doc = new DOMDocument();
        @$doc->loadHTML($content);

        $xpath = new DOMXpath($doc);

        $nodes = $xpath->evaluate("//tr/td[starts-with(text(),'Vos DNS')]/following-sibling::td[1]/text()");
        $rawValue = (($nodes->length > 0) && ($nodes[0]->nodeType === XML_TEXT_NODE)) ? $nodes[0]->wholeText : null;

        // Explode string as array and trim each value
        return ($rawValue !== null) ? array_map('trim', explode('et', $rawValue)) : [];
    }

    public function getGateway()
    {
        $response = $this->requestIndexPage();
        $content = $response->getContent();

        $doc = new DOMDocument();
        @$doc->loadHTML($content);

        $xpath = new DOMXpath($doc);

        $nodes = $xpath->evaluate("//tr/td[starts-with(text(),'Votre passerelle')]/following-sibling::td[1]/text()");

        return (($nodes->length > 0) && ($nodes[0]->nodeType === XML_TEXT_NODE)) ? $nodes[0]->wholeText : null;
    }

    /**
     * Login to the box to access secure pages.
     * @param string $login
     * @param string $password
     */
    public function login($login, $password)
    {
        if ($this->isLoggedIn) {
            return;
        }

        $request = new CurlRequest([
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_AUTOREFERER => true, // to set referer on redirect
            CURLOPT_SSL_VERIFYPEER => 0,// to disable SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_REFERER => 'https://' . $this->ip . '/config.html',
            CURLOPT_POST => true,
            CURLOPT_URL => 'https://' . $this->ip . '/goform/login',
            CURLOPT_POSTFIELDS => http_build_query([
                'loginUsername' => $login,
                'loginPassword' => $password,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = $this->curlSession->sendRequest($request);

        if (($response->getHttpCode() === 200)
            && ($response->getUrl() === 'https://' . $this->ip . '/config.html')
            && (stripos($response->getContent(), 'action="/goform/login"') > 0)) {
            throw new \UnexpectedValueException('Invalid login/password to access private page.');
        }

        if (($response->getHttpCode() === 200)
            && ($response->getUrl() === 'https://' . $this->ip . '/goform/login')
            && (stripos($response->getContent(), 'Accès refusé, un autre utilisateur est déjà connecté') > 0)) {
            throw new \UnexpectedValueException('Another user is logged on the box.');
        }

        if (($response->getHttpCode() !== 200)
            || ($response->getUrl() !== 'https://' . $this->ip . '/config.html')
            || (stripos($response->getContent(), '<a href="logout.html" class="menulogout">SE DECONNECTER</a>') === false)) {
            throw new \UnexpectedValueException('The page after login is not as expected');
        }

        $this->isLoggedIn = true;
    }

    /**  Logout to the box. */
    public function logout()
    {
        $request = new CurlRequest([
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_AUTOREFERER => true, // to set referer on redirect
            CURLOPT_SSL_VERIFYPEER => 0,// to disable SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => 'https://' . $this->ip . '/logout.html',
        ]);

        $response = $this->curlSession->sendRequest($request);

        if (($response->getHttpCode() !== 200)
            || ($response->getUrl() !== 'https://' . $this->ip . '/logout.html')
            || ((stripos($response->getContent(), 'Merci d\'avoir utilisé l\'interface de gestion de votre modem.') === false)
                && (stripos($response->getContent(), 'action="/goform/login"') === false))) {
            throw new \UnexpectedValueException('The page after logout is not as expected');
        }

        $this->isLoggedIn = false;
    }

    /** Restart the box. */
    public function restart()
    {
        if (!$this->isLoggedIn) {
            throw new \LogicException('Be logged is required to restart the box.');
        }

        $request = new CurlRequest([
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_AUTOREFERER => true, // to set referer on redirect
            CURLOPT_SSL_VERIFYPEER => 0,// to disable SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_REFERER => 'https://' . $this->ip . '/config.html',
            CURLOPT_POST => true,
            CURLOPT_URL => 'https://' . $this->ip . '/goform/WebUiOnlyReboot',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = $this->curlSession->sendRequest($request);

        if (($response->getHttpCode() !== 200)
            || ($response->getUrl() !== 'https://' . $this->ip . '/login.html')
            || (stripos($response->getContent(), 'action="/goform/login"') === false)) {
            throw new \UnexpectedValueException('The page after reboot is not as expected');
        }

        $this->isLoggedIn = false;
    }
}
