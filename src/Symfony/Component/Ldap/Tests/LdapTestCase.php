<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Ldap\Tests;

use PHPUnit\Framework\TestCase;

class LdapTestCase extends TestCase
{
    protected function getLdapConfig(bool $ldapSecure = false)
    {
        $host = getenv('LDAP_HOST');
        $port = $ldapSecure ? getenv('LDAP_SECURE_PORT') : getenv('LDAP_PORT');

        $h = @ldap_connect(($ldapSecure ? 'ldaps://' : 'ldap://').$host.':'.$port);
        @ldap_set_option($h, \LDAP_OPT_PROTOCOL_VERSION, 3);

        if (!$h || !@ldap_bind($h)) {
            $this->markTestSkipped(\sprintf('No server is listening on LDAP_HOST:%s', $ldapSecure ? 'LDAP_SECURE_PORT' : 'LDAP_PORT'));
        }

        ldap_unbind($h);

        return [
            'host' => $host,
            'port' => $port,
        ];
    }
}
