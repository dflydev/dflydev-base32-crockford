Base32 Crockford Encoder and Decoder
====================================

A [Base32 Crockford](http://www.crockford.com/wrmg/base32.html) implementation
for PHP.


Example
-------

    use Dflydev\Base32\Crockford\Crockford;
    
    $encodedValue = Crockford::encode('519571'); // FVCK
    $decodedValue = Crockford::decode('FVCK'); // 519571
    
    $encodedValue = Crockford::encodeWithChecksum('519571'); // FVCKH
    $decodedValue = Crockford::decodeWithChecksum('FVCKH'); // 519571

By default, decoding will be lenient on the input values. This will
allow for passing in the following:

    $decodedValue = Crockford::decode('F-VCk'); // treated as: FVCK
    $decodedValue = Crockford::decode('hEl1O'); // treated as: HE110

See [the spec](http://www.crockford.com/wrmg/base32.html) for the
translation rules.

Decoding can be made strict by passing an optional second argument
to the decode methods.

    Crockford::decode('F-VCk', Crockford::NORMALIZE_ERRMODE_EXCEPTION);
    Crockford::decode('hEl1O', Crockford::NORMALIZE_ERRMODE_EXCEPTION);


Requirements
------------

 * PHP 5.3+


License
-------

MIT, see LICENSE.


Community
---------

If you have questions or want to help out, join us in the
[#dflydev](irc://irc.freenode.net/#dflydev) channel on irc.freenode.net.


Not Invented Here
-----------------

This is a port of [Encode::Base32::Crockford](https://github.com/gbarr/Encode-Base32-Crockford).
