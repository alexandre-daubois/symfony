<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Tests\Caster;

use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;

/**
 * @requires extension openssl
 */
class OpenSslCasterTest extends TestCase
{
    use VarDumperTestTrait;

    public function testAsymmetricKey()
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 1024,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $this->assertDumpMatchesFormat(
            <<<'EODUMP'
OpenSSLAsymmetricKey {
  +type: 0
  +bits: 1024
  +publicKey: {
    size: 1024
    md5: %A
    sha1: %A
    sha256: %A
  }
}
EODUMP, $key);
    }

    public function testOpensslCsr()
    {
        $dn = [
            'countryName' => 'FR',
            'stateOrProvinceName' => 'Ile-de-France',
            'localityName' => 'Paris',
            'organizationName' => 'Symfony',
            'organizationalUnitName' => 'Security',
            'commonName' => 'symfony.com',
            'emailAddress' => 'test@symfony.com',
        ];
        $privkey = openssl_pkey_new();
        $csr = openssl_csr_new($dn, $privkey);

        $this->assertDumpMatchesFormat(
            <<<'EODUMP'
OpenSSLCertificateSigningRequest {
  +subject: {
    countryName: "FR"
    stateOrProvinceName: "Ile-de-France"
    localityName: "Paris"
    organizationName: "Symfony"
    organizationalUnitName: "Security"
    commonName: "symfony.com"
    emailAddress: "test@symfony.com"
  }
  +publicKey: {
    size: 2048
    md5: %A
    sha1: %A
    sha256: %A
  }
}
EODUMP, $csr);
    }
}
