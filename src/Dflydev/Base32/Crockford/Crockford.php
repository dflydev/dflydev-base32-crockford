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

/**
 * Base32 Crockford implementation
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Crockford
{
    const NORMALIZE_ERRMODE_SILENT = 0;
    const NORMALIZE_ERRMODE_EXCEPTION = 1;

    public static $symbols = array(
        '0', '1', '2', '3', '4',
        '5', '6', '7', '8', '9',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
        'J', 'K', 'M', 'N', 'P', 'Q', 'R', 'S',
        'T', 'V', 'W', 'X', 'Y', 'Z',
        '*', '~', '$', '=', 'U',
    );

    public static $flippedSymbols = array(
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4,
        '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13,
        'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17,
        'J' => 18, 'K' => 19, 'M' => 20, 'N' => 21,
        'P' => 22, 'Q' => 23, 'R' => 24, 'S' => 25,
        'T' => 26, 'V' => 27, 'W' => 28, 'X' => 29,
        'Y' => 30, 'Z' => 31,
        '*' => 32, '~' => 33, '$' => 34, '=' => 35, 'U' => 36,
    );

    /**
     * Encode a number
     *
     * @param int $number
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function encode($number)
    {
        if (!is_numeric($number)) {
            throw new \RuntimeException("Specified number '{$number}' is not numeric");
        }

        if (!$number) {
            return 0;
        }

        $response = array();
        while ($number) {
            $remainder = $number % 32;
            $number = (int) ($number/32);
            $response[] = static::$symbols[$remainder];
        }

        return implode('', array_reverse($response));
    }

    /**
     * Encode a number with checksum
     *
     * @param int $number
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function encodeWithChecksum($number)
    {
        $encoded = static::encode($number);

        return $encoded . static::$symbols[$number % 37];
    }

    /**
     * Decode a string
     *
     * @param string $string  Encoded string
     * @param int    $errmode Error mode
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    public static function decode($string, $errmode = self::NORMALIZE_ERRMODE_SILENT)
    {
        return static::internalDecode($string, $errmode);
    }

    /**
     * Decode a string with checksum
     *
     * @param string $string  Encoded string
     * @param int    $errmode Error mode
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    public static function decodeWithChecksum($string, $errmode = self::NORMALIZE_ERRMODE_SILENT)
    {
        $checksum = substr($string, (strlen($string) -1), 1);
        $string = substr($string, 0, strlen($string) - 1);

        $value = static::internalDecode($string, $errmode);
        $checksumValue = static::internalDecode($checksum, self::NORMALIZE_ERRMODE_EXCEPTION, true);

        if ($checksumValue !== ($value % 37)) {
            throw new \RuntimeException("Checksum symbol '$checksum' is not correct value for '$string'");
        }

        return $value;
    }

    /**
     * Normalize a string
     *
     * @param string $string  Encoded string
     * @param int    $errmode Error mode
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function normalize($string, $errmode = self::NORMALIZE_ERRMODE_SILENT)
    {
        $origString = $string;

        $string = strtoupper($string);
        if ($string !== $origString && $errmode) {
            throw new \RuntimeException("String '$origString' requires normalization");
        }

        $string = str_replace('-', '', strtr($string, 'IiLlOo', '111100'));
        if ($string !== $origString && $errmode) {
            throw new \RuntimeException("String '$origString' requires normalization");
        }

        return $string;
    }

    /**
     * Decode a string
     *
     * @param string $string     Encoded string
     * @param int    $errmode    Error mode
     * @param bool   $isChecksum Is encoded with a checksum?
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    protected static function internalDecode($string, $errmode = self::NORMALIZE_ERRMODE_SILENT, $isChecksum = false)
    {
        if ('' === $string) {
            return '';
        }

        if (null === $string) {
            return '';
        }

        $string = static::normalize($string, $errmode);

        if ($isChecksum) {
            $valid = '/^[A-Z0-9\*\~\$=U]$/';
        } else {
            $valid = '/^[A-TV-Z0-9]+$/';
        }

        if (!preg_match($valid, $string)) {
            throw new \RuntimeException("String '$string' contains invalid characters");
        }

        $total = 0;
        foreach (str_split($string) as $symbol) {
            $total = $total * 32 + static::$flippedSymbols[$symbol];
        }

        return $total;
    }
}
