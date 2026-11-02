<?php

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * Package the current public/build (manifest.json + assets/) into the Builder
 * addon's shipped bundle, Modules/Builder/resources/dist/build.zip — the exact
 * zip AddonController::builderPublish() extracts back over public/build on
 * activation. Pure PHP (no `zip`/`bash` binary needed), so it runs on Windows and
 * minimal CI containers alike. Mirrors 6amMart's prepare:builder-bundle.
 */
class PrepareBuilderBundle extends Command
{
    protected $signature = 'prepare:builder-bundle {output=build.zip}';

    protected $description = 'Package public/build (manifest.json + assets/) into Modules/Builder/resources/dist/build.zip (/build/* base) for the Builder addon.';

    public function handle(): int
    {
        // No-op on the base product (Builder addon not installed) so `npm run
        // build` stays safe on the without-addon variant — never create an orphan
        // Modules/Builder tree.
        if (!File::isDirectory(base_path('Modules/Builder'))) {
            $this->warn('Modules/Builder not present — skipping Builder bundle packaging.');
            return self::SUCCESS;
        }

        $buildDir = public_path('build');
        if (!File::exists($buildDir . '/manifest.json')) {
            $this->error('public/build/manifest.json not found. Run `vite build` before packaging.');
            return self::FAILURE;
        }

        $output = basename((string) $this->argument('output'));
        if (!str_ends_with($output, '.zip')) {
            $output .= '.zip';
        }

        $distDir = base_path('Modules/Builder/resources/dist');
        if (!File::isDirectory($distDir)) {
            File::makeDirectory($distDir, 0755, true);
        }

        $zipPath = $distDir . '/' . $output;
        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Cannot create {$zipPath}");
            return self::FAILURE;
        }

        // Add public/build's contents at the archive ROOT (manifest.json + assets/)
        // so it extracts straight back over public/build on activation.
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($buildDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $relativePath = substr($item->getPathname(), strlen($buildDir) + 1);
            if ($item->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($item->getPathname(), $relativePath);
            }
        }
        $zip->close();

        $sizeMb = round(filesize($zipPath) / 1048576, 2);
        $this->info("Builder bundle packaged: {$zipPath} ({$sizeMb} MB)");
        return self::SUCCESS;
    }
}
