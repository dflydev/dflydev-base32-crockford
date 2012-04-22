<?php

/*
 * This file is a part of dflydev/base32-crockford.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dflydev\Tests\Base32\Crockford;

use Dflydev\Base32\Crockford\Crockford;

class CrockfordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideEncodedData
     */
    public function testEncode($decodedValue, $encodedValue, $encodedValueWithChecksum)
    {
        if ($decodedValue < 0) {
            $this->markTestSkipped('Fails on 32bit systems');

            return;
        }

        $this->assertEquals($encodedValue, Crockford::encode($decodedValue));
        $this->assertEquals($encodedValueWithChecksum, Crockford::encodeWithChecksum($decodedValue));
    }

    public function testEncodeThrowsException()
    {
        try {
            Crockford::encode("hello world");

            $this->fail('Should not be able to encode a string');
        } catch (\RuntimeException $e) {
            $this->assertContains("hello world", $e->getMessage());
        }
    }

    /**
     * @dataProvider provideEncodedData
     */
    public function testDecode($decodedValue, $encodedValue, $encodedValueWithChecksum)
    {
        if ($decodedValue < 0) {
            $this->markTestSkipped('Fails on 32bit systems');

            return;
        }

        $this->assertEquals($decodedValue, Crockford::decode($encodedValue));
        $this->assertEquals($decodedValue, Crockford::decodeWithChecksum($encodedValueWithChecksum));
    }

    public function testDecodeExtras()
    {
        $this->assertEquals('', Crockford::decode(''));
        $this->assertEquals('', Crockford::decode(null));
    }

    public function testDecodeThrowsException()
    {
        try {
            Crockford::decodeWithChecksum('12');

            $this->fails('Should throw an exception');
        } catch (\RuntimeException $e) {
            $this->assertContains('is not correct value for', $e->getMessage());
            $this->assertContains("'2'", $e->getMessage());
            $this->assertContains("'1'", $e->getMessage());
        }

        try {
            Crockford::decodeWithChecksum('U0');

            $this->fails('Should throw an exception');
        } catch (\RuntimeException $e) {
            $this->assertContains('contains invalid characters', $e->getMessage());
            $this->assertContains("'U'", $e->getMessage());
        }
    }

    public function testNormalize()
    {
        $this->assertEquals("ABC", Crockford::normalize("ABC"));
        $this->assertEquals("ABC", Crockford::normalize("A-B-C"));
        $this->assertEquals("ABC111100", Crockford::normalize("A-B-C-IiLlOo"));

        $this->assertEquals("ABC", Crockford::normalize("ABC", Crockford::NORMALIZE_ERRMODE_EXCEPTION));
    }

    /**
     * @dataProvider provideNormalizeThrowsExceptionData
     */
    public function testNormalizeThrowsException($input)
    {
        try {
            Crockford::normalize($input, Crockford::NORMALIZE_ERRMODE_EXCEPTION);

            $this->fail('Normalize would be required to do something, should have thrown an exception');
        } catch (\RuntimeException $e) {
            $this->assertContains($input, $e->getMessage());
        }
    }

    public function provideEncodedData()
    {
        return array(
            array(0, '0', '00'),
            array(1, '1', '11'),
            array(2, '2', '22'),
            array(194, '62', '629'),
            array(456789, 'DY2N', 'DY2NR'),
            array(398373, 'C515', 'C515Z'),
            array(519571, 'FVCK', 'FVCKH'),
            array(3838385658376483, '3D2ZQ6TVC93', '3D2ZQ6TVC935'),
        );
    }

    public function provideNormalizeThrowsExceptionData()
    {
        return array(
            array('A-B-C'),
            array('abc'),
            array('A-B-C-IiLlOo'),
        );
    }
}