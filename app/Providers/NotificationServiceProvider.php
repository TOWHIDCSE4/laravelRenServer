<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider {
   public function register() {
        $this->app->bind('App\Services\Notification\NotificationServiceInterface', 'App\Services\Notification\FcmService');
    }

}