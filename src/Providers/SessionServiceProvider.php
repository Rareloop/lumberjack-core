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

        $cookieOptions = [
            'lifetime' => Config::get('session.lifetime', 120),
            'path' => Config::get('session.path', '/'),
            'domain' => Config::get('session.domain', null),
            'secure' => Config::get('session.secure', false),
            'httpOnly' => Config::get('session.http_only', true),
        ];

        $handler = new FileSessionHandler($this->getSessionPath());

        $store = new Store($name, $handler, $id);
        $store->start();

        $this->app->bind('session', $store);

        // Due to the way we handle WordPressControllers sometimes the `send_headers` action is
        // called twice. Knowing this, we'll put a lock around adding the cookie
        $cookieSet = false;

        add_action('send_headers', function () use ($name, $id, $cookieOptions, &$cookieSet) {
            if (!$cookieSet) {
                setcookie(
                    $name,
                    $id,
                    time() + ($cookieOptions['lifetime'] * 60),
                    $cookieOptions['path'],
                    $cookieOptions['domain'],
                    $cookieOptions['secure'],
                    $cookieOptions['httpOnly']
                );

                $cookieSet = true;
            }
        });

        add_action('shutdown', function () use ($store) {
            $store->save();
        });
    }

    private function getSessionPath()
    {
        $path = session_save_path();

        if (empty($path)) {
            $path = sys_get_temp_dir();
        }

        return $path;
    }
}
