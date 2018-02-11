<?php
/**
 * This file is part of Jeedom's NBLabox plugin.
 * @copyright 2018, Lionel SAURON
 * @licence https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace NBLabox\Box;

/** Test class for {@link NumericableBox} */
class NumericableBoxTest extends \PHPUnit_Framework_TestCase
{
    /** Test access data which don't need login. */
    public function testGetDataFromIndexPage()
    {
        $curlSession = new CurlSessionStub([
            'https://dummy_ip' => [
                'url' => 'https://dummy_ip/',
                'httpCode' => 200,
                'content' => file_get_contents(__DIR__ . "/../../resources/index.html"),
            ]
        ]);

        $box = new NumericableBox('dummy_ip', $curlSession);

        $this->assertEquals('81.255.255.25', $box->getPublicIp());
        $this->assertEquals('NCC_1.4.0', $box->getSoftwareVersion());
        $this->assertEquals('2.0', $box->getHardwareVersion());
        $this->assertEquals('2.0', $box->getHardwareVersion());
        $this->assertEquals(100, $box->getDownloadBandwidth());
        $this->assertEquals(5, $box->getUploadBandwidth());
        $this->assertEquals('255.255.252.0', $box->getNetworkMask());
        $this->assertEquals('81.65.255.1', $box->getGateway());
        $this->assertEquals(['89.2.0.1', '89.2.0.2'], $box->getDns());
    }

    /** {@link NumericableBox::login()} with invalid login/password. */
    public function testLoginWithInvalidAuthentication()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid login/password to access private page.');

        $curlSession = new CurlSessionStub([
            'https://dummy_ip/goform/login' => [
                'url' => 'https://dummy_ip/config.html',
                'httpCode' => 200,
                'content' => file_get_contents(__DIR__ . "/../../resources/login.html"),
            ]
        ]);

        $box = new NumericableBox('dummy_ip', $curlSession);
        $box->login('dummy login', 'dummy password');
    }

    /** {@link NumericableBox::login()} with another user logged. */
    public function testLoginWithAnotherUserLogged()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Another user is logged on the box.');

        $curlSession = new CurlSessionStub([
            'https://dummy_ip/goform/login' => [
                'url' => 'https://dummy_ip/goform/login',
                'httpCode' => 200,
                'content' => file_get_contents(__DIR__ . "/../../resources/auth_another_user.html"),
            ]
        ]);

        $box = new NumericableBox('dummy_ip', $curlSession);
        $box->login('dummy login', 'dummy password');
    }

    /** {@link NumericableBox::login()} with success. */
    public function testLoginSuccessful()
    {
        $curlSession = new CurlSessionStub([
            'https://dummy_ip/goform/login' => [
                'url' => 'https://dummy_ip/config.html',
                'httpCode' => 200,
                'content' => file_get_contents(__DIR__ . "/../../resources/auth_ok.html"),
            ]
        ]);

        $box = new NumericableBox('dummy_ip', $curlSession);
        $box->login('dummy login', 'dummy password');
    }

    /** {@link NumericableBox::logout()} when user is logged. */
    public function testLogoutWhenLogged()
    {
        $curlSession = new CurlSessionStub([
            'https://dummy_ip/logout.html' => [
                'url' => 'https://dummy_ip/logout.html',
                'httpCode' => 200,
                'content' => file_get_contents(__DIR__ . "/../../resources/logout.html"),
            ]
        ]);

        $box = new NumericableBox('dummy_ip', $curlSession);
        $box->logout();
    }

    /** {@link NumericableBox::logout()} When user is already logged out. */
    public function testLogoutAlreadyLoggedOut()
    {
        $curlSession = new CurlSessionStub([
            'https://dummy_ip/logout.html' => [
                'url' => 'https://dummy_ip/logout.html',
                'httpCode' => 200,
                'content' => file_get_contents(__DIR__ . "/../../resources/login.html"),
            ]
        ]);

        $box = new NumericableBox('dummy_ip', $curlSession);
        $box->logout();
    }

    /** {@link NumericableBox::restart()} without being logged */
    public function testRestartTheBoxWithoutBeingLogged()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Be logged is required to restart the box.');

        $curlSession = new CurlSessionStub([
            'https://dummy_ip/goform/login' => [
                'url' => 'https://dummy_ip/config.html',
                'httpCode' => 200,
                'content' => file_get_contents(__DIR__ . "/../../resources/auth_ok.html"),
            ],
            'https://dummy_ip//goform/WebUiOnlyReboot' => [
                'url' => 'https://dummy_ip/',
                'httpCode' => 200,
                'content' => file_get_contents(__DIR__ . "/../../resources/reboot.html"),
            ]
        ]);

        $box = new NumericableBox('dummy_ip', $curlSession);
        $box->restart();
    }

    /** {@link NumericableBox::restart()} reboot the box */
    public function testRestartTheBox()
    {
        $curlSession = new CurlSessionStub([
            'https://dummy_ip/goform/login' => [
                'url' => 'https://dummy_ip/config.html',
                'httpCode' => 200,
                'content' => file_get_contents(__DIR__ . "/../../resources/auth_ok.html"),
            ],
            'https://dummy_ip/goform/WebUiOnlyReboot' => [
                'url' => 'https://dummy_ip/login.html',
                'httpCode' => 200,
                'content' => file_get_contents(__DIR__ . "/../../resources/login.html"),
            ]
        ]);

        $box = new NumericableBox('dummy_ip', $curlSession);
        $box->login('dummy login', 'dummy password');
        $box->restart();
    }
}
