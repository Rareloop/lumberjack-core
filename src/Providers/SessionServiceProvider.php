<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\Config;

class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $name = Config::get('session.name', 'lumberjack');
        $id = $_COOKIE[$name] ?? session_create_id();

        $lifetime = Config::get('session.lifetime', 120);
        $path = Config::get('session.path', '/');
        $domain = Config::get('session.domain', null);
        $secure = Config::get('session.secure', false);
        $httpOnly = Config::get('session.http_only', true);

        $handler = new FileSessionHandler(session_save_path());

        $store = new Store($name, $handler, $id);

        $this->app->bind('session', $store);

        add_action('send_headers', function () use ($name, $id, $lifetime, $path, $domain, $secure, $httpOnly) {
            setcookie($name, $value, time() + ($lifetime * 60), $path, $domain, $secure, $httpOnly);
        });
    }
}
