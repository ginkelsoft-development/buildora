<?php

use Illuminate\Support\Facades\Route;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraDashboardController;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraDataTableController;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraExportController;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraController;
use Ginkelsoft\Buildora\Http\Controllers\GlobalSearchController;
use Ginkelsoft\Buildora\Http\Controllers\RelationDatatableController;

Route::prefix(config('buildora.route_prefix', 'buildora'))
    ->middleware(config('buildora.middleware', ['web', 'buildora.auth', 'buildora.ensure-user-resource']))
    ->group(function () {

        Route::post('/switch-locale', function (\Illuminate\Http\Request $request) {
            $locale = $request->input('locale');

            if (in_array($locale, ['en', 'nl', 'es', 'de', 'fy'])) {
                session(['locale' => $locale]);
            }

            return back();
        })->name('locale.switch');

        /*
        |--------------------------------------------------------------------------
        | Dashboard & Global Search
        |--------------------------------------------------------------------------
        */
        Route::get('/', [BuildoraDashboardController::class, 'index'])
            ->name('buildora.dashboard');

        Route::get('/global-search', GlobalSearchController::class)
            ->name('buildora.global.search');

        /*
        |--------------------------------------------------------------------------
        | Dynamic Resource Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('resource')->group(function () {

            Route::get('{resource}', [BuildoraDataTableController::class, 'index'])
                ->middleware('buildora.can:view')
                ->name('buildora.index');

            Route::get('{resource}/create', [BuildoraController::class, 'create'])
                ->middleware('buildora.can:create')
                ->name('buildora.create');

            Route::post('{resource}', [BuildoraController::class, 'store'])
                ->middleware('buildora.can:create')
                ->name('buildora.store');

            Route::get('{resource}/{id}/edit', [BuildoraController::class, 'edit'])
                ->middleware('buildora.can:edit')
                ->name('buildora.edit');

            Route::put('{resource}/{id}', [BuildoraController::class, 'update'])
                ->middleware('buildora.can:edit')
                ->name('buildora.update');

            Route::get('{resource}/{id}', [BuildoraController::class, 'show'])
                ->middleware('buildora.can:view')
                ->name('buildora.show');

            Route::delete('{resource}/{id}', [BuildoraController::class, 'destroy'])
                ->middleware('buildora.can:delete')
                ->name('buildora.destroy');

            Route::get('{resource}/{id}/relation/{relation}', RelationDatatableController::class)
                ->middleware('buildora.can:view')
                ->name('buildora.relation.index');

            Route::get('{resource}/datatable/json', [BuildoraDataTableController::class, 'json'])
                ->middleware('buildora.can:view')
                ->name('buildora.datatable.json');

            Route::get('{resource}/export/{format?}', [BuildoraExportController::class, 'export'])
                ->middleware('buildora.can:view')
                ->where('format', 'xlsx|csv')
                ->name('buildora.export');
        });
    });
