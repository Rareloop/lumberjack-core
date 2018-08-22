# Upgrading

## v3.x to v4.x

### PHP Version
Support for PHP 7.0 has been dropped, ensure you're running at least PHP 7.1.

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
