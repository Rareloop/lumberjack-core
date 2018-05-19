<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\Config;
use Rareloop\Lumberjack\Session\FileSessionHandler;
use Rareloop\Lumberjack\Session\Store;

class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $name = Config::get('session.name', 'lumberjack');
        $id = $_COOKIE[$name] ?? uniqid();

        $lifetime = Config::get('session.lifetime', 120);
        $path = Config::get('session.path', '/');
        $domain = Config::get('session.domain', null);
        $secure = Config::get('session.secure', false);
        $httpOnly = Config::get('session.http_only', true);

        $handler = new FileSessionHandler(session_save_path());

        $store = new Store($name, $handler, $id);

        $this->app->bind('session', $store);

        // Due to the way we handle WordPressControllers sometimes the `send_headers` action is
        // called twice. Knowing this, we'll put a lock around adding the cookie
        $cookieSet = false;

        add_action('send_headers', function () use ($name, $id, $lifetime, $path, $domain, $secure, $httpOnly, &$cookieSet) {
            if (!$cookieSet) {
                setcookie($name, $id, time() + ($lifetime * 60), $path, $domain, $secure, $httpOnly);
                $cookieSet = true;
            }
        });
    }
}
