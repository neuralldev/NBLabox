<?php
/**
 * This file is part of Jeedom's NBLabox plugin.
 * @copyright 2018, Lionel SAURON
 * @licence https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace NBLabox\Curl;

/** Test class for {@link CurlRequest} */
class CurlRequestTest extends \PHPUnit_Framework_TestCase
{
    /** {@link CurlRequest::getOption()} return the value if the option exists, null otherwise */
    public function testGetOption()
    {
        $request = new CurlRequest([25 => 'test value']);

        $this->assertEquals('test value', $request->getOption(25));
        $this->assertNull($request->getOption(30));
    }

    /** {@link CurlRequest::getOptions()} returns all options, but omit options with null value */
    public function testGetOptions()
    {
        $request = new CurlRequest([
            // Null is not a valid value, the key must be remove
            10 => null,
            // The following values should not be confused with null and must be kept
            30 => 0,
            31 => '',
            32 => false
        ]);

        $this->assertEquals([
            30 => 0,
            31 => '',
            32 => false
        ], $request->getOptions());
    }
}
