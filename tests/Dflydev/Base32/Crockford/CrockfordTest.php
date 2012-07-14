<?php

/*
 * This file is a part of dflydev/base32-crockford.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dflydev\Base32\Crockford;

class CrockfordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideEncodedData
     */
    public function testEncode($decodedValue, $encodedValue, $encodedValueWithChecksum, $requires64bit)
    {
        if ($requires64bit && 4 === PHP_INT_SIZE) {
            $this->markTestSkipped('Requires 64bit system (int size is '.PHP_INT_SIZE.')');

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
    public function testDecode($decodedValue, $encodedValue, $encodedValueWithChecksum, $requires64bit)
    {
        if ($requires64bit && 4 === PHP_INT_SIZE) {
            $this->markTestSkipped('Requires 64bit system (int size is '.PHP_INT_SIZE.')');

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
            array(0, '0', '00', false),
            array(1, '1', '11', false),
            array(2, '2', '22', false),
            array(194, '62', '629', false),
            array(456789, 'DY2N', 'DY2NR', false),
            array(398373, 'C515', 'C515Z', false),
            array(519571, 'FVCK', 'FVCKH', false),
            array(3838385658376483, '3D2ZQ6TVC93', '3D2ZQ6TVC935', true),
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
