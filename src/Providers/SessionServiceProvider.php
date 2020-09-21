<?php

namespace Rareloop\Lumberjack\Providers;

use Rareloop\Lumberjack\Helpers;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Facades\Config;
use Rareloop\Lumberjack\Session\SessionManager;

class SessionServiceProvider extends ServiceProvider
{
    protected $session;

    public function register()
    {
        $this->session = new SessionManager($this->app);
        $this->app->bind('session', $this->session);
    }

    public function boot()
    {
        add_action('init', function () {
            $this->session->start();

            if ($this->shouldGarbageCollect()) {
                $this->session->collectGarbage(Config::get('session.lifetime', 120));
            }
        });

        // Due to the way we handle WordPressControllers sometimes the `send_headers` action is
        // called twice. Knowing this, we'll put a lock around adding the cookie
        $cookieSet = false;

        add_action('send_headers', function () use (&$cookieSet) {
            if (!$cookieSet) {
                $cookieOptions = [
                    'lifetime' => Config::get('session.lifetime', 120),
                    'path' => Config::get('session.path', '/'),
                    'domain' => Config::get('session.domain', null),
                    'secure' => Config::get('session.secure', false),
                    'httpOnly' => Config::get('session.http_only', true),
                ];

                setcookie(
                    $this->session->getName(),
                    $this->session->getId(),
                    time() + ($cookieOptions['lifetime'] * 60),
                    $cookieOptions['path'],
                    $cookieOptions['domain'],
                    $cookieOptions['secure'],
                    $cookieOptions['httpOnly']
                );

                $cookieSet = true;
            }
        });

        add_action('shutdown', function () {
            $this->storePreviousUrlToSession();
        });
    }

    private function storePreviousUrlToSession()
    {
        if (!Helpers::app()->has('request')) {
            return;
        }

        $request = Helpers::request();

        if ($request->isMethod('GET') && !$request->ajax()) {
            $this->session->setPreviousUrl($request->fullUrl());
        }

        $this->session->save();
    }

    private function shouldGarbageCollect()
    {
        $lottery = Config::get('session.lottery');

        if (!is_array($lottery) || count($lottery) < 2 || !is_numeric($lottery[0]) || !is_numeric($lottery[1])) {
            $lottery = [2, 100];
        }

        return random_int(1, $lottery[1]) <= $lottery[0];
    }
}
