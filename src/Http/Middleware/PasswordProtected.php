<?php

namespace Rareloop\Lumberjack\Http\Middleware;

use Timber\Timber;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rareloop\Lumberjack\Post;

class PasswordProtected implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $passwordRequestResponse = $this->handlePasswordProtected();

        if ($passwordRequestResponse) {
            return $passwordRequestResponse;
        }

        return $handler->handle($request);
    }

    protected function handlePasswordProtected(): ?ResponseInterface
    {
        if (!post_password_required()) {
            return null;
        }

        $context = Timber::context();
        $context['post'] = new Post();

        $html = Timber::compile(
            apply_filters('lumberjack/password_protect_template', 'single-password.twig'),
            $context
        );

        if (!$html) {
            return null;
        }

        return new HtmlResponse($html);
    }
}
