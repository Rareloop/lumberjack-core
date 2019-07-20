<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use DI\NotFoundException;
use Error;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Exceptions\HandlerInterface;
use Rareloop\Router\Responsable;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Zend\Diactoros\ServerRequestFactory;
use function Http\Response\send;

/**
 * Determine whether or not we should be in debug mode or not
 * If not debug:
 *     Register exception handlers against a `handle` function
 *     Get the handler from the container (have an object that implements an interface that the app can extend from)
 *     Call handle on the resolved object
 */

class RegisterExceptionHandler
{
    private $app;

    public function bootstrap(Application $app)
    {
        $this->app = $app;

        if (is_admin()) {
            return;
        }

        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleException($e)
    {
        if ($e instanceof Error) {
            $e = new ErrorException($e->getMessage(), 0, null, $e->getFile(), $e->getLine());
        }

        $handler = $this->getExceptionHandler();
        $handler->report($e);

        try {
            $request = $this->app->get('request');
        } catch (NotFoundException $notFoundException) {
            $request = ServerRequestFactory::fromGlobals(
                $_SERVER,
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            );
        }

        if ($e instanceof Responsable) {
            $this->send($e->toResponse($request));
            return;
        }

        $this->send($handler->render($request, $e));
    }

    public function send(ResponseInterface $response)
    {
        @send($response);
    }

    protected function getExceptionHandler() : HandlerInterface
    {
        return $this->app->get(HandlerInterface::class);
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        $exception = new ErrorException($message, 0, $level, $file, $line);

        if ($level === E_USER_NOTICE || $level === E_USER_DEPRECATED) {
            $this->getExceptionHandler()->report($exception);
            return;
        }

        if (error_reporting() & $level) {
            throw $exception;
        }
    }

    public function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }

    /**
     * Create a new fatal exception instance from an error array.
     *
     * @param  array  $error
     * @param  int|null  $traceOffset
     * @return \Symfony\Component\Debug\Exception\FatalErrorException
     */
    protected function fatalExceptionFromError(array $error, $traceOffset = null)
    {
        return new FatalErrorException(
            $error['message'],
            $error['type'],
            0,
            $error['file'],
            $error['line'],
            $traceOffset
        );
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int  $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}
