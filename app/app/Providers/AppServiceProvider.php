<?php

namespace App\Providers;

use Accutics\Services\CampaignRepository\CampaignRepository;
use Accutics\Services\CampaignRepository\DbCampaignRepository;
use Accutics\Services\UserRepository\FileUserRepository;
use Accutics\Services\UserRepository\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepository::class, function ($app) {
            return new FileUserRepository();
        });

        $this->app->bind(CampaignRepository::class, function ($app) {
            return new DbCampaignRepository();
        });
    }

    public function boot()
    {
        //
    }
}
