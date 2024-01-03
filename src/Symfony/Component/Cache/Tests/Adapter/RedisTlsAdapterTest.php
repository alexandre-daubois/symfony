<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Tests\Adapter;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\Cache\Traits\RedisProxy;

/**
 * @group integration
 */
class RedisTlsAdapterTest extends AbstractRedisAdapterTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $dsn = 'rediss://'.getenv('REDIS_TLS_HOST').'?ssl[verify_peer]=1&ssl[verify_peer_name]=0&ssl[cafile]='.getenv('REDIS_TLS_CA');

        dump(getenv('REDIS_TLS_HOST'), getenv('REDIS_TLS_CA'), getenv('REDIS_TLS_CERT'), getenv('REDIS_TLS_KEY'));
        self::$redis = AbstractAdapter::createConnection($dsn, ['lazy' => true]);
    }
}
