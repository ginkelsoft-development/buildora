<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraDashboardController;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraDataTableController;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraExportController;
use Ginkelsoft\Buildora\Http\Controllers\BuildoraController;
use Ginkelsoft\Buildora\Http\Controllers\GlobalSearchController;
use Ginkelsoft\Buildora\Http\Controllers\RelationDatatableController;
use Ginkelsoft\Buildora\Http\Controllers\PermissionSyncController;

Route::prefix(config('buildora.route_prefix', 'buildora'))
    ->middleware(config('buildora.middleware', ['web', 'buildora.auth', 'buildora.ensure-user-resource']))
    ->group(function () {

        Route::post('/switch-locale', function (Request $request) {

            $locale = $request->input('locale');

            if (in_array($locale, ['en', 'nl', 'es', 'de', 'fy'])) {
                buildora_session_put('locale', $locale);
            }

            return back();
        })->name('buildora.locale.switch');

        /*
        |--------------------------------------------------------------------------
        | Dashboard & Global Search
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard/{name?}', BuildoraDashboardController::class)
            ->name('buildora.dashboard')
            ->middleware(['web', 'auth']);

        Route::get('/global-search', GlobalSearchController::class)
            ->name('buildora.global.search');

        /*
        |--------------------------------------------------------------------------
        | Permission Management
        |--------------------------------------------------------------------------
        */
        Route::post('/permissions/sync', [PermissionSyncController::class, 'sync'])
            ->name('buildora.permissions.sync')
            ->middleware(['web', 'auth']);

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

            Route::get('{resource}/datatable/json', [BuildoraDataTableController::class, 'json'])
                ->middleware('buildora.can:view')
                ->name('buildora.datatable.json');

            Route::get('{resource}/export/{format?}', [BuildoraExportController::class, 'export'])
                ->middleware('buildora.can:view')
                ->where('format', 'xlsx|csv')
                ->name('buildora.export');

            Route::post('{resource}', [BuildoraController::class, 'store'])
                ->middleware('buildora.can:create')
                ->name('buildora.store');

            Route::get('{resource}/{id}/edit', [BuildoraController::class, 'edit'])
                ->where('id', '[0-9]+')
                ->middleware('buildora.can:edit')
                ->name('buildora.edit');

            Route::get('{resource}/{id}/relation/{relation}', RelationDatatableController::class)
                ->where('id', '[0-9]+')
                ->middleware('buildora.can:view')
                ->name('buildora.relation.index');

            Route::put('{resource}/{id}', [BuildoraController::class, 'update'])
                ->where('id', '[0-9]+')
                ->middleware('buildora.can:edit')
                ->name('buildora.update');

            Route::get('{resource}/{id}', [BuildoraController::class, 'show'])
                ->where('id', '[0-9]+')
                ->middleware('buildora.can:view')
                ->name('buildora.show');

            Route::delete('{resource}/{id}', [BuildoraController::class, 'destroy'])
                ->where('id', '[0-9]+')
                ->middleware('buildora.can:delete')
                ->name('buildora.destroy');

            Route::get('/buildora/async/{model}/search', function ($model) {
                $modelClass = Str::start("App\\Models\\" . Str::studly($model), '\\');

                if (!class_exists($modelClass)) {
                    abort(404, "Model {$modelClass} not found");
                }

                $query = request('q');
                $displayColumn = request('label', 'name'); // wat je toont (mag accessor zijn)
                $searchColumns = request()->input('search', [$displayColumn]); // waar je op zoekt (echte DB kolommen)

                $instance = new $modelClass;

                // validatie: display column mag accessor zijn
                if (!array_key_exists($displayColumn, $instance->toArray())) {
                    abort(400, "Invalid display column '{$displayColumn}' for {$modelClass}");
                }

                // validatie: alle zoek kolommen moeten kolommen zijn in de DB
                $invalid = collect($searchColumns)->reject(fn ($col) => \Schema::hasColumn($instance->getTable(), $col));
                if ($invalid->isNotEmpty()) {
                    abort(400, "Invalid search column(s): " . $invalid->implode(', '));
                }

                return $modelClass::query()
                    ->when($query, function ($q) use ($searchColumns, $query) {
                        $q->where(function ($q) use ($searchColumns, $query) {
                            foreach ($searchColumns as $col) {
                                $q->orWhere($col, 'like', "%{$query}%");
                            }
                        });
                    })
                    ->limit(25)
                    ->get()
                    ->map(fn ($record) => [
                        'id' => $record->getKey(),
                        'text' => $record->{$displayColumn},
                    ]);
            })->name('buildora.async.search');
        });
    });
