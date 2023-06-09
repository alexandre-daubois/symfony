UPGRADE FROM 6.3 to 6.4
=======================

DependencyInjection
-------------------

 * Deprecate `ContainerAwareInterface` and `ContainerAwareTrait`, use dependency injection instead

   Before:
   ```php
   class MyService implements ContainerAwareInterface
   {
       use ContainerAwareTrait;

       // ...
   }
   ```

   After:
   ```php
   class MyService
   {
       private ContainerInterface $container;

       // Inject the container through the constructor...
       public function __construct(ContainerInterface $container)
       {
           $this->container = $container;
       }

       // ... or by using the #[Required] attribute
       #[Required]
       public function setContainer(ContainerInterface $container): void
       {
            $this->container = $container;
       }
   }
   ```

DoctrineBridge
--------------

 * Deprecate `DbalLogger`, use a middleware instead
 * Deprecate not constructing `DoctrineDataCollector` with an instance of `DebugDataHolder`
 * Deprecate `DoctrineDataCollector::addLogger()`, use a `DebugDataHolder` instead
 * Deprecate `ContainerAwareLoader`, use dependency injection in your fixtures instead

HttpFoundation
--------------

 * Make `HeaderBag::getDate()`, `Response::getDate()`, `getExpires()` and `getLastModified()` return a `DateTimeImmutable`
