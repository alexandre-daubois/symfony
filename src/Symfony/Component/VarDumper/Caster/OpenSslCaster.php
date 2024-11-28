<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Caster;

use Symfony\Component\VarDumper\Cloner\Stub;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
class OpenSslCaster
{
    public static function castOpensslX509($h, array $a, Stub $stub, bool $isNested): array
    {
        $stub->cut = -1;
        $info = openssl_x509_parse($h, false);

        $pin = openssl_pkey_get_public($h);
        $pin = openssl_pkey_get_details($pin)['key'];
        $pin = \array_slice(explode("\n", $pin), 1, -2);
        $pin = base64_decode(implode('', $pin));
        $pin = base64_encode(hash('sha256', $pin, true));

        $a += [
            'subject' => new EnumStub(array_intersect_key($info['subject'], ['organizationName' => true, 'commonName' => true])),
            'issuer' => new EnumStub(array_intersect_key($info['issuer'], ['organizationName' => true, 'commonName' => true])),
            'expiry' => new ConstStub(date(\DateTimeInterface::ISO8601, $info['validTo_time_t']), $info['validTo_time_t']),
            'fingerprint' => new EnumStub([
                'md5' => new ConstStub(wordwrap(strtoupper(openssl_x509_fingerprint($h, 'md5')), 2, ':', true)),
                'sha1' => new ConstStub(wordwrap(strtoupper(openssl_x509_fingerprint($h, 'sha1')), 2, ':', true)),
                'sha256' => new ConstStub(wordwrap(strtoupper(openssl_x509_fingerprint($h, 'sha256')), 2, ':', true)),
                'pin-sha256' => new ConstStub($pin),
            ]),
        ];

        return $a;
    }

    public static function castOpensslAsymmetricKey($h, array $a, Stub $stub, bool $isNested): array
    {
        $info = openssl_pkey_get_details($h);

        $a += [
            'type' => new ConstStub($info['type'], $info['type']),
            'bits' => new ConstStub($info['bits'], $info['bits']),
            'publicKey' => new EnumStub([
                'size' => new ConstStub($info['bits'], $info['bits']),
                'md5' => new ConstStub(wordwrap(strtoupper(md5($info['key'])), 2, ':', true)),
                'sha1' => new ConstStub(wordwrap(strtoupper(sha1($info['key'])), 2, ':', true)),
                'sha256' => new ConstStub(wordwrap(strtoupper(hash('sha256', $info['key'])), 2, ':', true)),
            ]),
        ];

        return $a;
    }

    public static function castOpensslCsr($h, array $a, Stub $stub, bool $isNested): array
    {
        $info = openssl_csr_get_subject($h, false);
        $key = openssl_csr_get_public_key($h);

        $pin = openssl_pkey_get_details($key)['key'];

        $a += [
            'subject' => new EnumStub(array_intersect_key($info, [
                'organizationName' => true,
                'commonName' => true,
                'countryName' => true,
                'stateOrProvinceName' => true,
                'localityName' => true,
                'organizationalUnitName' => true,
                'emailAddress' => true,
            ])),
            'publicKey' => new EnumStub([
                'size' => new ConstStub(openssl_pkey_get_details($key)['bits'], openssl_pkey_get_details($key)['bits']),
                'md5' => new ConstStub(wordwrap(strtoupper(md5($pin)), 2, ':', true)),
                'sha1' => new ConstStub(wordwrap(strtoupper(sha1($pin)), 2, ':', true)),
                'sha256' => new ConstStub(wordwrap(strtoupper(hash('sha256', $pin)), 2, ':', true)),
            ]),
        ];

        return $a;
    }
}
