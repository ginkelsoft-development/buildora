<?php

namespace Ginkelsoft\Buildora\Http\Controllers\Install;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    /**
     * Show the initial install page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (class_exists('App\\Buildora\\Resources\\UserBuildora')) {
            return redirect()->route('buildora.install.models')
                ->with('info', 'Deze installatiestap is al voltooid.');
        }

        $hasUserModel = class_exists('App\\Models\\User');
        $hasTrait = false;
        $hasUsers = false;

        if ($hasUserModel) {
            $userPath = base_path('app/Models/User.php');
            $userContents = File::get($userPath);
            $hasTrait = Str::contains($userContents, 'HasBuildora');
            $hasUsers = User::query()->exists();
        }

        return view('buildora::install.index', compact('hasUserModel', 'hasTrait', 'hasUsers'));
    }

    /**
     * Run the HasBuildora trait injection on the User model.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function run()
    {
        $path = base_path('app/Models/User.php');
        $class = 'App\\Models\\User';

        if (!class_exists($class)) {
            return redirect()->route('buildora.install')->with('error', 'User model niet gevonden.');
        }

        $updated = $this->injectHasBuildoraTrait($class, $path);

        if ($updated) {
            $modelName = class_basename($class);
            $exitCode = Artisan::call("buildora:resource " . $modelName);

            if ($exitCode !== 0) {
                shell_exec('composer dump-autoload');
                $exitCode = Artisan::call("buildora:resource " . $modelName);

                if ($exitCode !== 0) {
                    return redirect()->route('buildora.install')->with('error', "User resource kon niet aangemaakt worden.");
                }
            }
        }

        return redirect()->route('buildora.install')->with(
            'success',
            $updated
                ? 'HasBuildora trait succesvol toegevoegd aan User model en resource aangemaakt.'
                : 'Trait was al aanwezig in het User model.'
        );
    }

    /**
     * Show the form to create the first user.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function createUser()
    {
        if (class_exists('App\\Buildora\\Resources\\UserBuildora')) {
            return redirect()->route('buildora.install.models')
                ->with('info', 'Deze installatiestap is al voltooid.');
        }

        if (!$this->userModelHasTrait()) {
            return redirect()->route('buildora.install')->with('error', 'Stap 2 is niet beschikbaar totdat het User model Buildora-ready is.');
        }

        return view('buildora::install.create-user');
    }

    /**
     * Handle user creation form submission.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeUser(Request $request)
    {
        if (!$this->userModelHasTrait()) {
            return redirect()->route('buildora.install')->with('error', 'Gebruiker aanmaken is niet mogelijk zonder Buildora trait.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('buildora.install.models')->with('success', 'Gebruiker succesvol aangemaakt. Je kunt nu Buildora verder instellen.');
    }

    /**
     * Show list of all models and indicate Buildora readiness.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function models()
    {
        if (!$this->userModelHasTrait()) {
            return redirect()->route('buildora.install')->with('error', 'Stap 3 is niet beschikbaar totdat het User model Buildora-ready is.');
        }

        $modelPath = app_path('Models');
        $models = collect(File::allFiles($modelPath))
            ->filter(fn($file) => Str::endsWith($file->getFilename(), '.php'))
            ->map(function ($file) {
                $relative = str_replace([base_path('app/Models') . '/', '.php'], ['', ''], $file->getPathname());
                $relative = str_replace('/', '\\', $relative);
                $className = 'App\\Models\\' . $relative;
                $hasTrait = false;

                if (class_exists($className)) {
                    $contents = File::get($file->getPathname());
                    $hasTrait = Str::contains($contents, 'HasBuildora');
                }

                return [
                    'class' => $className,
                    'file' => $file->getPathname(),
                    'hasTrait' => $hasTrait,
                ];
            });

        return view('buildora::install.models', compact('models'));
    }

    /**
     * Add HasBuildora trait and generate Buildora resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addBuildoraTrait(Request $request)
    {
        $class = $request->input('class');
        $path = $request->input('path');

        if (!class_exists($class) || !File::exists($path)) {
            return back()->with('error', 'Modelbestand niet gevonden.');
        }

        $updated = $this->injectHasBuildoraTrait($class, $path);

        if ($updated) {
            $modelName = class_basename($class);
            $exitCode = Artisan::call("buildora:resource " . $modelName);

            if ($exitCode !== 0) {
                shell_exec('composer dump-autoload');
                $exitCode = Artisan::call("buildora:resource " . $modelName);

                if ($exitCode !== 0) {
                    return back()->with('error', "Resource genereren voor {$modelName} is mislukt, ook na dump-autoload.");
                }
            }
        }

        return redirect()->route('buildora.install.models')->with(
            'success',
            class_basename($class) . ' is nu Buildora-ready!'
        );
    }

    /**
     * Inject the HasBuildora trait into the given model file.
     *
     * @param string $className
     * @param string $path
     * @return bool
     */
    protected function injectHasBuildoraTrait(string $className, string $path): bool
    {
        if (!File::exists($path)) {
            return false;
        }

        $contents = File::get($path);
        $updated = false;

        if (!Str::contains($contents, 'use Ginkelsoft\\Buildora\\Traits\\HasBuildora;')) {
            $contents = preg_replace(
                '/^namespace App\\\\Models;(\r?\n)/m',
                "namespace App\\Models;\nuse Ginkelsoft\\Buildora\\Traits\\HasBuildora;$1",
                $contents
            );
            $updated = true;
        }

        if (!preg_match('/class\\s+\\w+\\s+extends\\s+\\w+.*?\\{[\\s\\S]*?use\\s+HasBuildora;/', $contents)) {
            $contents = preg_replace_callback(
                '/class\\s+\\w+\\s+extends\\s+\\w+\\s*\\{/',
                fn($matches) => $matches[0] . "\n    use HasBuildora;",
                $contents
            );
            $updated = true;
        }

        if ($updated) {
            File::put($path, $contents);
        }

        return $updated;
    }

    /**
     * Check if the User model has the HasBuildora trait.
     *
     * @return bool
     */
    protected function userModelHasTrait(): bool
    {
        if (!class_exists(User::class)) {
            return false;
        }

        $path = base_path('app/Models/User.php');
        if (!File::exists($path)) {
            return false;
        }

        $contents = File::get($path);

        return Str::contains($contents, 'HasBuildora');
    }
}
