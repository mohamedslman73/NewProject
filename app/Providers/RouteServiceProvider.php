<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;


class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace        = 'App\Modules\{route}',
              $exceptRoutesFile = [];

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map(){

        $getAllRoutes = glob(base_path('routes/*.routes.php'),GLOB_NOSORT );

        foreach ($getAllRoutes as $route){
            $route = explode('.',basename($route))[0];
            if(in_array($route,$this->exceptRoutesFile)) continue;

            if($route == 'Api'){
                $route = ucfirst($route);
                Route::prefix('api')
                    ->middleware('api')
                    ->namespace( str_replace('{route}',$route,$this->namespace) )
                    ->group(base_path('routes/'.$route.'.routes.php'));
            }elseif($route == 'ApiMerchant'){
                $route = ucfirst($route);
                Route::prefix('api')
                    ->middleware('apiMerchant')
                    ->namespace( str_replace('{route}','Api',$this->namespace) )
                    ->group(base_path('routes/'.$route.'.routes.php'));
            }  elseif ($route == 'ApiPartner') {
                $route = ucfirst($route);
                Route::prefix('api')
                    ->middleware('ApiPartner')
                    ->namespace(str_replace('{route}', 'Api', $this->namespace))
                    ->group(base_path('routes/' . $route . '.routes.php'));
            } else{
                $route = ucfirst($route);
                Route::middleware('web')
                    ->namespace( str_replace('{route}',$route,$this->namespace) )
                    ->group(base_path('routes/'.$route.'.routes.php'));
            }
        }

    }


}