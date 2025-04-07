<?php

use Illuminate\Support\Facades\Route;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraDashboardController;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraDatatableController;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraExportController;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraController;
use Ginkelsoft\Buildora\Http\Controllers\GlobalSearchController;
use Ginkelsoft\Buildora\Http\Controllers\Install\InstallController;
use Ginkelsoft\Buildora\Http\Controllers\RelationDatatableController;

/*
|--------------------------------------------------------------------------
| Buildora Installation Routes
|--------------------------------------------------------------------------
|
| Routes for first-time installation of the Buildora package. These guide
| the developer through user model setup, trait injection, and resource registration.
|
*/

Route::middleware('web')
    ->prefix('buildora/install')
    ->group(function () {
        Route::get('/', [InstallController::class, 'index'])
            ->name('buildora.install');

        Route::post('/run', [InstallController::class, 'run'])
            ->name('buildora.install.run');

        Route::get('/user', [InstallController::class, 'createUser'])
            ->name('buildora.install.user');

        Route::post('/user', [InstallController::class, 'storeUser'])
            ->name('buildora.install.user.store');

        Route::get('/models', [InstallController::class, 'models'])
            ->name('buildora.install.models');

        Route::post('/models/add', [InstallController::class, 'addBuildoraTrait'])
            ->name('buildora.install.models.add');
    });


/*
|--------------------------------------------------------------------------
| Buildora Core CRUD Routes
|--------------------------------------------------------------------------
|
| These routes handle all dynamic resource actions for models that use the
| HasBuildora trait. They support datatables, forms, exports and actions.
|
| Middleware and prefix are defined via the Buildora config file.
|
*/

Route::prefix(config('buildora.route_prefix', 'buildora'))
    ->middleware(config('buildora.middleware', ['web', 'buildora.auth', 'buildora.ensure-user-resource']))
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard & Global Search
        |--------------------------------------------------------------------------
        |
        | The Buildora dashboard and fuzzy global search functionality.
        |
        */
        Route::get('/', [BuildoraDashboardController::class, 'index'])
            ->name('buildora.dashboard');

        Route::get('/global-search', GlobalSearchController::class)
            ->name('buildora.global.search');


        /*
        |--------------------------------------------------------------------------
        | Dynamic Resource Routes
        |--------------------------------------------------------------------------
        |
        | These routes are dynamically resolved per registered Buildora resource.
        | They support index, create/edit forms, datatable JSON loading, and export.
        |
        */
        Route::prefix('resource')->group(function () {
            Route::get('{resource}', [BuildoraDatatableController::class, 'index'])
                ->name('buildora.index');

            Route::get('{resource}/create', [BuildoraController::class, 'create'])
                ->name('buildora.create');

            Route::get('{resource}/{id}/edit', [BuildoraController::class, 'edit'])
                ->name('buildora.edit');

            Route::post('{resource}', [BuildoraController::class, 'store'])
                ->name('buildora.store');

            Route::put('{resource}/{id}', [BuildoraController::class, 'update'])
                ->name('buildora.update');

            Route::get('{resource}/{id}', [BuildoraController::class, 'show'])
                ->name('buildora.show');

            Route::delete('{resource}/{id}', [BuildoraController::class, 'destroy'])
                ->name('buildora.destroy');

            Route::get('{resource}/{id}/relation/{relation}', RelationDatatableController::class)
                ->name('buildora.relation.index');

            Route::get('{resource}/datatable/json', [BuildoraDatatableController::class, 'json'])
                ->name('buildora.datatable.json');

            Route::get('{resource}/export/{format?}', [BuildoraExportController::class, 'export'])
                ->where('format', 'xlsx|csv')
                ->name('buildora.export');
        });
    });
