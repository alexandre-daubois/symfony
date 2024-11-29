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

class ResourceCasterTest extends TestCase
{
    use VarDumperTestTrait;

    public function testCastCurl()
    {
        $ch = curl_init('http://example.com');
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);

        $this->assertDumpMatchesFormat(
            <<<'EODUMP'
CurlHandle {
  url: "http://example.com/"
  content_type: "text/html; charset=UTF-8"
  http_code: 200%A
}
EODUMP, $ch);
    }

    public function testAsymmetricKey()
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 1024,
            'private_key_type' => \OPENSSL_KEYTYPE_RSA,
        ]);

        $this->assertDumpMatchesFormat(
            <<<'EODUMP'
OpenSSLAsymmetricKey {
  bits: 1024
  key: """
    -----BEGIN PUBLIC KEY-----\n
    %A
    %A
    %A
    %A
    -----END PUBLIC KEY-----\n
    """
  type: 0
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
  countryName: "FR"
  stateOrProvinceName: "Ile-de-France"
  localityName: "Paris"
  organizationName: "Symfony"
  organizationalUnitName: "Security"
  commonName: "symfony.com"
  emailAddress: "test@symfony.com"
}
EODUMP, $csr);
    }

    /**
     * @requires PHP < 8.4
     */
    public function testCastDbaPriorToPhp84()
    {
        $dba = dba_open(sys_get_temp_dir().'/test.db', 'c');

        $this->assertDumpMatchesFormat(
            <<<'EODUMP'
dba resource {
  file: %s
}
EODUMP, $dba);
    }
}
