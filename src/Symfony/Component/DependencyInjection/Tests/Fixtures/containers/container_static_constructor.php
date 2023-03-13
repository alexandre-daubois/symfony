<?php

require_once __DIR__.'/../includes/classes.php';
require_once __DIR__.'/../includes/foo.php';

use Bar\FooClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

$container = new ContainerBuilder();
$container
    ->register('foo', FooClass::class)
    ->setConstructor('getInstance')
;

return $container;
