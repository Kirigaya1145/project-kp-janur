<?php

namespace App\Providers;

use App\Models\CompanyProfile;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('partials.footer', function ($view) {
            $view->with('companyProfile', CompanyProfile::first());
        });
    }
}
