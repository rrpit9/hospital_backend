<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /** Registering Moroph Relationship */
        $this->registerMorphMaps();

        /** Auth User Share to View */
        /*\View::composer('*', function ($view) {
            if ($authUser = auth()->user()) {
                $view->with('authUser', $authUser);
            }
        });*/

        /*Query Logs in to File*/
        $this->logQueryIntoLogFile();
    }

    public function registerMorphMaps()
    {
        Relation::morphMap([
            'ADMIN' => \App\Models\User::class,
            'CLIENT' => \App\Models\Client::class,
            'EMPLOYEE' => \App\Models\Employee::class,
            'CUSTOMER' => \App\Models\Customer::class,
            'PRODUCTS' => \App\Models\Product::class
        ]);
    }

    public function logQueryIntoLogFile()
    {
        \DB::listen(function ($query) {
            \Log::info(
                $query->sql, $query->bindings, $query->time
            );
        });
    }
}
