<?php
/**
 * This file is part of Jeedom's NBLabox plugin.
 * @copyright 2018, Lionel SAURON
 * @licence https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace NBLabox\Curl;

/** Test class for {@link CurlSession} */
class CurlSessionTest extends \PHPUnit_Framework_TestCase
{
    /** Authentication for the box.
     * Security issue : Set this login/password for your box only for unit test.
     */
    const BOX_IP = '192.168.0.1';
    const ADMIN_LOGIN = 'admin';
    const ADMIN_PASSWORD = 'password';

    /** Test to retrieve index page. */
    public function testRequestIndexPage()
    {
        $curl = new CurlSession();

        $request = new CurlRequest([
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => 'https://' . self::BOX_IP,
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_AUTOREFERER => true, // to set referer on redirect
            CURLOPT_SSL_VERIFYPEER => 0,// to disable SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $response = $curl->sendRequest($request);

        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals('https://' . self::BOX_IP . '/', $response->getUrl());
        $this->assertEquals('text/html', $response->getContentType());

        $this->assertContains('<title>Mon Modem</title>', $response->getContent());
        $this->assertContains('Votre adresse IP', $response->getContent());
    }

    /** Test to retrieve the login page. */
    public function testRequestLoginPage()
    {
        $curl = new CurlSession();

        $request = new CurlRequest([
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => 'https://' . self::BOX_IP . '/config.html',
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_AUTOREFERER => true, // to set referer on redirect
            CURLOPT_SSL_VERIFYPEER => 0,// to disable SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $response = $curl->sendRequest($request);

        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals('https://' . self::BOX_IP . '/config.html', $response->getUrl());
        $this->assertEquals('text/html', $response->getContentType());

        $this->assertContains('<title>Mon Modem</title>', $response->getContent());
        $this->assertContains('action="/goform/login"', $response->getContent());
        $this->assertContains('<input name="loginPassword" type="password"', $response->getContent());
    }

    /** Test to login and to logout. */
    public function testDoLoginAndLogout()
    {
        $curl = new CurlSession();

        $request = new CurlRequest([
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_AUTOREFERER => true, // to set referer on redirect
            CURLOPT_SSL_VERIFYPEER => 0,// to disable SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_REFERER => 'https://' . self::BOX_IP . '/config.html',
            CURLOPT_POST => true,
            CURLOPT_URL => 'https://' . self::BOX_IP . '/goform/login',
            CURLOPT_POSTFIELDS => http_build_query([
                'loginUsername' => self::ADMIN_LOGIN,
                'loginPassword' => self::ADMIN_PASSWORD,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $loginResponse = $curl->sendRequest($request);

        // Always do the logout even if there is error on login.
        // Because only one user can be login in (result page is different is an user is already connected)
        // So assert must be done after the two requests.

        $request = new CurlRequest([
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_AUTOREFERER => true, // to set referer on redirect
            CURLOPT_SSL_VERIFYPEER => 0,// to disable SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => 'https://' . self::BOX_IP . '/logout.html',
        ]);

        $logoutResponse = $curl->sendRequest($request);

        // ===== Login =====

        $this->assertEquals(200, $loginResponse->getHttpCode());
        $this->assertEquals('https://' . self::BOX_IP . '/config.html', $loginResponse->getUrl());
        $this->assertEquals('text/html', $loginResponse->getContentType());

        $this->assertContains('<title>Mon Modem</title>', $loginResponse->getContent());
        $this->assertContains('<a href="logout.html" class="menulogout">SE DECONNECTER</a>', $loginResponse->getContent());
        $this->assertContains('<a href="wifi.html">WIFI</a>', $loginResponse->getContent());

        // ===== Logout =====

        $this->assertEquals(200, $logoutResponse->getHttpCode());
        $this->assertEquals('https://' . self::BOX_IP . '/logout.html', $logoutResponse->getUrl());
        $this->assertEquals('text/html', $logoutResponse->getContentType());

        $this->assertContains('Merci d\'avoir utilisé l\'interface de gestion de votre modem.', $logoutResponse->getContent());
        $this->assertContains('Au revoir.', $logoutResponse->getContent());
    }
}
