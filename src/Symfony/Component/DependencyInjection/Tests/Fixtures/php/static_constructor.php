<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class ProjectServiceContainer extends Container
{
    protected $parameters = [];
    protected readonly \WeakReference $ref;

    public function __construct()
    {
        $this->ref = \WeakReference::create($this);
        $this->services = $this->privates = [];
        $this->methodMap = [
            'static_constructor' => 'getStaticConstructorService',
        ];

        $this->aliases = [];
    }

    public function compile(): void
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    public function isCompiled(): bool
    {
        return true;
    }

    /**
     * Gets the public 'static_constructor' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Compiler\A
     */
    protected static function getStaticConstructorService($container)
    {
        return $container->services['static_constructor'] = \Symfony\Component\DependencyInjection\Tests\Compiler\A::create('foo');
    }
}
