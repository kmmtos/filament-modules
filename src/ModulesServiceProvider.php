<?php

namespace Coolsam\FilamentModules;

use Coolsam\FilamentModules\Commands\ModuleMakePanelCommand;
use Coolsam\FilamentModules\Extensions\LaravelModulesServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Support\HtmlString;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModulesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('modules')
            ->hasConfigFile('modules')
            ->hasViews()
            ->hasCommands([
                ModuleMakePanelCommand::class,
            ]);
    }

    public function register()
    {
        $this->app->register(LaravelModulesServiceProvider::class);
        $this->app->singleton('coolsam-modules', Modules::class);
        $this->app->afterResolving('filament', function () {
            foreach (Filament::getPanels() as $panel) {
                $id = \Str::of($panel->getId());
                if ($id->contains('::')) {
                    $panelId = explode('::', $id);

                    $moduleConfig = require_once(module_path($panelId[0]) . '/Config/config.php');

                    $title = $id->replace(['::', '-'], [' ', ' '])->title()->toString();

                    if (isset($moduleConfig['panel_title'])) {
                        if (isset($moduleConfig['panel_title'][$panelId[1]])) {
                            $title = $moduleConfig['panel_title'][$panelId[1]];
                        }
                    }

                    $panel
                        ->renderHook(
                            'panels::sidebar.nav.start',
                            fn() => new HtmlString("<h2 class='m-2 p-2 font-black text-xl'>$title</h2>"),
                        )
                        ->renderHook(
                            'panels::sidebar.nav.end',
                            fn() => new HtmlString(
                                '<a href="' . url('/') . '" class="m-2 p-2 mt-4 inline-flex gap-2 block rounded-lg font-bold bg-gray-500/10">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                        </svg>
                                        Main Panel
                                      </a>'
                            ),
                        );
                }
            }
        });

        return parent::register();
    }
}
