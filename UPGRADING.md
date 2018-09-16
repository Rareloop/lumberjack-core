# Upgrading

## v3.x to v4.x

### PHP Version
Support for PHP 7.0 has been dropped, ensure you're running at least PHP 7.1.

### Container
The `bind()` method on the `Application` container is no longer a singleton by default when the value (2nd param) is not a primitive or object instance.

When binding a concrete implementation to an interface, being a singleton created unexpected side affects. 

A new `singleton()` method has been provided to enable the previous behaviour. This enables the app developer to be more intentional about the behaviour they desire.

e.g.

```
$app->singleton(App\AppInterface::class, App\AppImplementation::class);
$object1 = $app->get(App\AppInterface::class);
$object2 = $app->get(App\AppInterface::class);

$object1 === $object2; true;

// ---

$app->bind(App\AppInterface::class, App\AppImplementation::class);
$object1 = $app->get(App\AppInterface::class);
$object2 = $app->get(App\AppInterface::class);

$object1 === $object2; false;
```

### Service Providers
Add the following providers to `config/app.php`:

```
'providers' => [
    ...
    Rareloop\Lumberjack\Providers\QueryBuilderServiceProvider::class
    Rareloop\Lumberjack\Providers\SessionServiceProvider::class
    Rareloop\Lumberjack\Providers\EncryptionServiceProvider::class
    ...
],
```

### PSR-15 Middleware
The `http-interop/http-server-middleware` package has been deprecated in favour of the now official PSR-15 interfaces found in `psr/http-server-middleware`.

Make sure any middleware used now complies with the `Psr\Http\Server\MiddlewareInterface` interface.

### Exception Handler
The type hint on the `render()` function has changed to the PSR interface from the concrete Zend implementation.

Make the following change in `app/Exceptions/Handler.php`, from:

```

use Zend\Diactoros\ServerRequest;

public function render(ServerRequest $request, Exception $e) : ResponseInterface
{

}
```

to:

```

use Psr\Http\Message\ServerRequestInterface;

public function render(ServerRequestInterface $request, Exception $e) : ResponseInterface
{

}
```

No changes should be required to your application logic as Zend subclasses will already comply with the new interface.

### `Helpers::app()` helper
`Helpers::app()` (and the `app()` global counterpart) no longer use the `make()` method of the Application instance and now rely on `get()`. This provides much more consistent behaviour with other uses of the Container. If you still want to use the helpers to get `make()` behaviour you can change your code from:

```
Helpers::app(MyClassName::class);
```

to:

```
Helpers::app()->make(MyClassName::class);
```


### `Router` class namespace
If you resolve an instance of the `Router` class from the container, you'll need to change the class reference from:

```
use Rareloop\Router\Router
```

to:

```
Rareloop\Lumberjack\Http\Router
```

If you're just using the Router Facade, you do not need to change anything.

### `ServerRequest` class (optional)
If you're injecting an instance of the Diactoros `ServerRequest` class into a Controller, you can now switch this out for the following class if you want to benefit from some of the new helper functions:

```
Rareloop\Lumberjack\Http\ServerRequest
```
