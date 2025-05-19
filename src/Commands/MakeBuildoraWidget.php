<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Class MakeBuildoraWidget
 *
 * This command generates a new Buildora widget class along with a corresponding Blade view file.
 */
class MakeBuildoraWidget extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buildora:widget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new Buildora Widget class and corresponding Blade view';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $name = $this->ask('Wat is de naam van de widget class? (zonder "Widget" achtervoegsel)');
        $className = Str::studly($name) . 'Widget';

        $viewPath = $this->ask('In welke submap moet de view komen? (bijv. "dashboard", leeg laten voor root)', '');
        $viewName = $this->ask('Wat is de naam van de view file? (bijv. "overview")');

        $fullViewPath = trim("{$viewPath}/{$viewName}", '/');
        $viewNamespace = 'buildora::widgets.' . str_replace('/', '.', $fullViewPath);

        // Define widget class output path
        $widgetDirectory = app_path('Buildora/Widgets');
        $widgetPath = "{$widgetDirectory}/{$className}.php";

        // Create widget directory if it doesn't exist
        if (!File::exists($widgetDirectory)) {
            File::makeDirectory($widgetDirectory, 0755, true);
            $this->info("📂 Directory aangemaakt: {$widgetDirectory}");
        }

        // Prevent overwriting existing widget
        if (File::exists($widgetPath)) {
            $this->error("❌ Widget {$className} bestaat al.");
            return;
        }

        // Generate widget class content
        $classStub = <<<PHP
<?php

namespace App\Buildora\Widgets;

use Ginkelsoft\Buildora\Widgets\BuildoraWidget;
use Illuminate\View\View;

class {$className} extends BuildoraWidget
{
    /**
     * Render the widget view.
     *
     * @return View
     */
    public function render(): View
    {
        return view('{$viewNamespace}');
    }
}
PHP;

        File::put($widgetPath, trim($classStub));
        $this->info("✅ Widget class aangemaakt: App\\Buildora\\Widgets\\{$className}");

        // Determine view location and create if necessary
        $viewBase = resource_path("views/vendor/buildora/widgets/{$viewPath}");
        $viewFile = "{$viewBase}/{$viewName}.blade.php";

        if (!File::exists($viewBase)) {
            File::makeDirectory($viewBase, 0755, true);
            $this->info("📁 View map aangemaakt: {$viewBase}");
        }

        if (!File::exists($viewFile)) {
            File::put($viewFile, "<div class=\"p-4 rounded bg-white shadow\">[{$className}] widget content hier</div>");
            $this->info("📄 View aangemaakt: resources/views/vendor/buildora/widgets/{$fullViewPath}.blade.php");
        } else {
            $this->warn("⚠️ View bestaat al: {$viewFile}");
        }

        $this->line('🎉 Widget succesvol aangemaakt!');
    }
}
