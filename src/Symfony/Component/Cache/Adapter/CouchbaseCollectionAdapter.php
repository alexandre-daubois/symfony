<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Adapter;

use Couchbase\Bucket;
use Couchbase\Cluster;
use Couchbase\ClusterOptions;
use Couchbase\Collection;
use Couchbase\DocumentNotFoundException;
use Couchbase\UpsertOptions;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

/**
 * @author Antonio Jose Cerezo Aranda <aj.cerezo@gmail.com>
 */
class CouchbaseCollectionAdapter extends AbstractAdapter
{
    private const MAX_KEY_LENGTH = 250;

    /** @var Collection */
    private $connection;
    private $marshaller;

    public function __construct(Collection $connection, string $namespace = '', int $defaultLifetime = 0, MarshallerInterface $marshaller = null)
    {
        if (!static::isSupported()) {
            throw new CacheException('Couchbase >= 3.0.0 < 4.0.0 is required.');
        }

        $this->maxIdLength = static::MAX_KEY_LENGTH;

        $this->connection = $connection;

        parent::__construct($namespace, $defaultLifetime);
        $this->enableVersioning();
        $this->marshaller = $marshaller ?? new DefaultMarshaller();
    }

    /**
     * @param array|string $dsn
     *
     * @return Bucket|Collection
     */
    public static function createConnection($dsn, array $options = [])
    {
        if (\is_string($dsn)) {
            $dsn = [$dsn];
        } elseif (!\is_array($dsn)) {
            throw new \TypeError(sprintf('Argument 1 passed to "%s()" must be array or string, "%s" given.', __METHOD__, get_debug_type($dsn)));
        }

        if (!static::isSupported()) {
            throw new CacheException('Couchbase >= 3.0.0 < 4.0.0 is required.');
        }

        set_error_handler(function ($type, $msg, $file, $line): bool { throw new \ErrorException($msg, 0, $type, $file, $line); });

        $pathPattern = '/^(?:\/(?<bucketName>[^\/\?]+))(?:(?:\/(?<scopeName>[^\/]+))(?:\/(?<collectionName>[^\/\?]+)))?(?:\/)?$/';
        $newServers = [];
        $protocol = 'couchbase';
        try {
            $username = $options['username'] ?? '';
            $password = $options['password'] ?? '';

            foreach ($dsn as $server) {
                if (0 !== strpos($server, 'couchbase:')) {
                    throw new InvalidArgumentException('Invalid Couchbase DSN: it does not start with "couchbase:".');
                }

                $parsed = parse_url($server);

                $username = $parsed['user'] ?? $username;
                $password = rawurldecode($parsed['pass'] ?? $password);
                $protocol = $parsed['scheme'] ?? $protocol;

                if (isset($parsed['query'])) {
                    $optionsInDsn = self::getOptions($parsed['query']);

                    foreach ($optionsInDsn as $parameter => $value) {
                        $options[$parameter] = $value;
                    }
                }

                $newServers[] = $parsed['host'];
            }

            $option = isset($parsed['query']) ? '?'.$parsed['query'] : '';
            $connectionString = $protocol.'://'.implode(',', $newServers).$option;

            $clusterOptions = new ClusterOptions();
            $clusterOptions->credentials($username, $password);

            $client = new Cluster($connectionString, $clusterOptions);

            preg_match($pathPattern, $parsed['path'], $matches);
            $bucket = $client->bucket($matches['bucketName']);
            $collection = $bucket->defaultCollection();
            if (!empty($matches['scopeName'])) {
                $scope = $bucket->scope($matches['scopeName']);
                $collection = $scope->collection($matches['collectionName']);
            }

            return $collection;
        } finally {
            restore_error_handler();
        }
    }

    public static function isSupported(): bool
    {
        return \extension_loaded('couchbase') && version_compare(phpversion('couchbase'), '3.0.4', '>=') && version_compare(phpversion('couchbase'), '5.0', '<');
    }

    private static function getOptions(string $options): array
    {
        $results = [];
        $optionsInArray = explode('&', $options);

        foreach ($optionsInArray as $option) {
            [$key, $value] = explode('=', $option);

            $results[$key] = $value;
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            try {
                $resultCouchbase = $this->connection->get($id);
            } catch (DocumentNotFoundException $exception) {
                continue;
            }

            $content = $resultCouchbase->value ?? $resultCouchbase->content();

            $results[$id] = $this->marshaller->unmarshall($content);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    protected function doHave($id): bool
    {
        return $this->connection->exists($id)->exists();
    }

    /**
     * {@inheritdoc}
     */
    protected function doClear($namespace): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete(array $ids): bool
    {
        $idsErrors = [];
        foreach ($ids as $id) {
            try {
                $result = $this->connection->remove($id);

                if (null === $result->mutationToken()) {
                    $idsErrors[] = $id;
                }
            } catch (DocumentNotFoundException $exception) {
            }
        }

        return 0 === \count($idsErrors);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave(array $values, $lifetime)
    {
        if (!$values = $this->marshaller->marshall($values, $failed)) {
            return $failed;
        }

        $upsertOptions = new UpsertOptions();
        $upsertOptions->expiry($lifetime);

        $ko = [];
        foreach ($values as $key => $value) {
            try {
                $this->connection->upsert($key, $value, $upsertOptions);
            } catch (\Exception $exception) {
                $ko[$key] = '';
            }
        }

        return [] === $ko ? true : $ko;
    }
}
