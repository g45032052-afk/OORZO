<?php

namespace App\Http\Controllers\Admin\System;

use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Foundation\Application;

class AddonController extends Controller
{
    // Specific reason for the last Builder bundle failure, surfaced to the admin
    // and logged so a failed publish is actionable instead of generic.
    private string $builderError = '';

    public function __construct(){
        if (is_dir('Modules\Gateways\Traits') && trait_exists('Modules\Gateways\Traits\SmsGateway')) {
            $this->extendWithSmsGatewayTrait();
        }
    }

    private function extendWithSmsGatewayTrait()
    {
        $extendedControllerClass = $this->generateExtendedControllerClass();
        eval($extendedControllerClass);
    }

    private function generateExtendedControllerClass()
    {
        $baseControllerClass = get_class($this);
        $traitClassName = 'Modules\Gateways\Traits\SmsGateway';

        $extendedControllerClass = "
            class ExtendedController extends $baseControllerClass {
                use $traitClassName;
            }
        ";

        return $extendedControllerClass;
    }

    public function index(): Factory|View|Application
    {
        $dir = 'Modules';
        $directories = self::getDirectories($dir);
        $addons = [];
        foreach ($directories as $directory) {

            if( !in_array($directory, ['TaxModule','ReelsModule'])) {
                $sub_dirs = self::getDirectories('Modules/' . $directory);
                if (in_array('Addon', $sub_dirs)) {
                    $addons[] = 'Modules/' . $directory;
                }
            }
        }

        return view('admin-views.system.addon.index', compact('addons'));
    }

    /**
     * Stream an addon's preview image straight from the module's own
     * `public/addon.png` — the module's source location — so the file never
     * has to be duplicated into the web docroot. Restricted to `Modules/<Name>`
     * with no path traversal.
     */
    public function image(Request $request)
    {
        $addon = (string) $request->query('addon', '');

        if (!preg_match('#^Modules/[A-Za-z0-9_-]+$#', $addon)) {
            abort(404);
        }

        $path = base_path($addon . '/public/addon.png');
        if (!File::exists($path)) {
            abort(404);
        }

        return response()->file($path, ['Content-Type' => 'image/png']);
    }

    public function publish(Request $request): JsonResponse|int
    {
        if (getEnvMode() == 'demo') {
            return response()->json([
                'errors' => [['message' => translate('messages.update_option_is_disable_for_demo')]],
            ]);
        }

        $full_data = include(base_path($request['path'] . '/Addon/info.php'));
        $going_active = !$full_data['is_published'];

        // Publishing Builder needs a server pre-flight (PHP version, extensions,
        // writable paths, DB, bundle zip present). Run BEFORE flipping info.php
        // so a failure leaves state untouched; issues surface as error toasts.
        if ($going_active && $full_data['name'] == 'Builder') {
            $issues = $this->checkBuilderRequirements();
            if (!empty($issues)) {
                return response()->json(['errors' => $issues]);
            }
        }

        $full_data['is_published'] = $going_active ? 1 : 0;
        unset($full_data['purchase_code'], $full_data['username'], $full_data['software_id']);
        $str = "<?php return " . var_export($full_data, true) . ";";
        file_put_contents(base_path($request['path'] . '/Addon/info.php'), $str);

        // Deploy (or remove) the pre-built storefront bundle for the Builder addon.
        if ($full_data['name'] == 'Builder') {
            $ok = $this->builderPublish($full_data['is_published']);
            if (!$ok) {
                // Roll back the info.php flip so we're never "activated but no bundle".
                $full_data['is_published'] = $going_active ? 0 : 1;
                file_put_contents(
                    base_path($request['path'] . '/Addon/info.php'),
                    "<?php return " . var_export($full_data, true) . ";"
                );
                $message = translate('Builder activation failed during the bundle copy. Check logs and try again.');
                if ($this->builderError !== '') {
                    $message .= ' — ' . $this->builderError;
                }
                return response()->json([
                    'errors' => [['message' => $message]],
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message'=> 'status_updated_successfully'
        ]);
    }

    /**
     * Server-side pre-flight for Builder activation: PHP version, extensions,
     * functions, writable paths, DB connection and the shipped bundle zip.
     * Returns a list of blocking issues (empty when the environment is ready).
     * The project-root `build` entry is NOT pre-flighted here — builderPublish()
     * creates it (symlink, or a real copy when symlink() is unavailable) and
     * fails activation with a specific reason if it can't. Mirrors 6amMart.
     */
    private function checkBuilderRequirements(): array
    {
        $issues = [];

        // A — PHP version
        if (PHP_VERSION_ID < 80100) {
            $issues[] = [
                'key' => 'php_version',
                'message' => "PHP 8.1+ required (found " . PHP_VERSION . ")",
                'fix' => "Upgrade PHP via your hosting control panel.",
            ];
        }

        // B — required PHP extensions
        $extensions = ['zip', 'pdo_mysql', 'mbstring', 'fileinfo', 'openssl',
                       'tokenizer', 'xml', 'ctype', 'bcmath'];
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext)) {
                $issues[] = [
                    'key' => "ext_$ext",
                    'message' => "PHP extension '$ext' is not loaded",
                    'fix' => "Enable the $ext extension in php.ini or via your hosting control panel.",
                ];
            }
        }
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            $issues[] = [
                'key' => 'ext_image',
                'message' => "Neither 'gd' nor 'imagick' PHP extension is loaded",
                'fix' => "Enable at least one image extension (typically GD) via php.ini.",
            ];
        }

        // C — PHP functions sometimes disabled on shared hosts. `symlink` is NOT
        // required: builderPublish() falls back to a real directory copy when it
        // is unavailable, so a symlink-disabled host can still serve /build.
        $functions = ['symlink', 'readlink','copy', 'unlink', 'rmdir',
                      'glob', 'class_implements', 'realpath'];
        foreach ($functions as $fn) {
            if (!function_exists($fn)) {
                $issues[] = [
                    'key' => "fn_$fn",
                    'message' => "PHP function '$fn' is disabled",
                    'fix' => "Remove '$fn' from `disable_functions` in php.ini, or contact your host.",
                ];
            }
        }

        // D — writable paths
        $writable = [
            public_path()                => 'public/',
            base_path('bootstrap/cache') => 'bootstrap/cache/',
            storage_path()               => 'storage/',
            base_path('Modules/Builder') => 'Modules/Builder/',
        ];
        foreach ($writable as $path => $label) {
            if (is_dir($path) && !is_writable($path)) {
                $issues[] = [
                    'key' => 'write_' . md5($path),
                    'message' => "Path is not writable: $label",
                    'fix' => "Run `chmod -R 777 $label` and ensure the web server user owns it.",
                ];
            }
        }

        // E — database connection
        try {
            \DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $issues[] = [
                'key' => 'db_connection',
                'message' => "Database connection failed: " . $e->getMessage(),
                'fix' => "Verify DB_HOST / DB_DATABASE / DB_USERNAME / DB_PASSWORD in .env.",
            ];
        }

        // F — Builder pre-built bundle present (single zip)
        $distZip = base_path('Modules/Builder/resources/dist/build.zip');
        if (!file_exists($distZip)) {
            $issues[] = [
                'key' => 'dist_zip_missing',
                'message' => "Builder pre-built bundle missing at Modules/Builder/resources/dist/build.zip",
                'fix' => "Re-upload the addon zip — the bundle appears incomplete.",
            ];
        }

        return $issues;
    }

    /**
     * Extract the Builder addon's pre-built bundle into public/build (staged +
     * atomically swapped) and expose it at the project root as `build` (symlink,
     * or a real copy when symlink() is unavailable). Shared by activation AND
     * upload (#5) so the storefront renders the moment the bundle is on disk — not
     * only after activation. Sets $this->builderError on failure. Mirrors 6amMart.
     */
    private function extractBuilderBundle(): bool
    {
        $this->builderError = '';

        $reason = $this->extractZipTo(base_path('Modules/Builder/resources/dist/build.zip'), public_path('build'));
        if ($reason !== '') {
            $this->builderError = $reason;
            info('Builder bundle: ' . $reason);
            return false;
        }

        if (!$this->linkPublicBuildToProjectRoot()) {
            $this->builderError = 'could not create the "build" entry at the project root '
                . base_path() . ' (required under a root docroot). Grant the web-server user '
                . 'write permission to the project root, or enable symlink().';
            info('Builder bundle: ' . $this->builderError);
            return false;
        }

        return true;
    }

    /**
     * Deploy (or remove) the Builder storefront bundle on activation toggle.
     * On activate: extractBuilderBundle() (staged extract + project-root `build`),
     * then run migrations. On deactivate: remove the root `build` entry and
     * public/build. Returns false on any failure ($this->builderError carries the
     * reason so the caller can surface it). Mirrors 6amMart.
     */
    private function builderPublish(int|bool $is_published): bool
    {
        $this->builderError = '';

        try {
            $hostBuild = public_path('build');

            if ($is_published) {
                if (!$this->extractBuilderBundle()) {
                    return false;
                }
                Artisan::call('migrate', ['--force' => true]);
            } else {
                // Deactivate: return the disk to its pre-activation state. Remove
                // the project-root `build` first (a symlink OR a real copy from the
                // symlink-less fallback), then the real public/build. PHP-level
                // gating (addon_published_status('Builder')) already blocks the
                // routes and bindings — this just clears the inert files too.
                $rootLink = base_path('build');
                if (is_link($rootLink) || is_file($rootLink)) {
                    @unlink($rootLink);
                } elseif (File::isDirectory($rootLink)) {
                    File::deleteDirectory($rootLink);
                }
                if (!is_link($hostBuild) && File::isDirectory($hostBuild)) {
                    File::deleteDirectory($hostBuild);
                }
            }

            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            return true;
        } catch (\Throwable $e) {
            $this->builderError = $e->getMessage();
            info('Builder publish failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Replace $targetDir with the contents of $zipPath. Returns '' on success or
     * a specific human-readable reason on failure. Extraction goes to a sibling
     * staging dir first, then swaps in — so a previous public/build (possibly
     * owned by a different user) can't leave a half-overwritten tree, and the
     * swap is near-atomic instead of a delete-then-extract that 500s the
     * storefront if extraction is interrupted. Mirrors 6amMart.
     */
    private function extractZipTo(string $zipPath, string $targetDir): string
    {
        if (!file_exists($zipPath)) {
            return 'source bundle not found at ' . $zipPath;
        }

        $stagingDir = $targetDir . '_tmp_' . getmypid();
        if (File::isDirectory($stagingDir)) {
            File::deleteDirectory($stagingDir);
        }
        if (!File::makeDirectory($stagingDir, 0755, true, true)) {
            return 'could not create a staging directory next to ' . $targetDir
                . ' — the parent directory is not writable by the web-server user';
        }

        $zip = new \ZipArchive();
        $opened = $zip->open($zipPath);
        if ($opened !== true) {
            File::deleteDirectory($stagingDir);
            return 'ZipArchive could not open the bundle (error code ' . $opened
                . ') — the zip is likely corrupt or truncated; re-upload the addon';
        }
        if (!$zip->extractTo($stagingDir)) {
            $zip->close();
            File::deleteDirectory($stagingDir);
            return 'extraction failed — likely out of disk space or an open_basedir restriction';
        }
        $zip->close();

        // Swap staging into place. Remove any previous bundle first; if those
        // files are not removable by this user, report it precisely.
        if (is_link($targetDir)) {
            @unlink($targetDir);
        } elseif (File::isDirectory($targetDir) && !File::deleteDirectory($targetDir)) {
            File::deleteDirectory($stagingDir);
            return 'could not remove the existing ' . $targetDir
                . ' — its files are owned by a different user than the web server; delete public/build over SSH and retry';
        }

        if (!@rename($stagingDir, $targetDir)) {
            File::deleteDirectory($stagingDir);
            return 'could not move the extracted bundle into ' . $targetDir;
        }

        return '';
    }

    /**
     * Expose public/build at the project root as `build` so the bundle is present
     * at both public/build and /build. REQUIRED only under a root docroot (public/
     * served as a subfolder), where the storefront's lazy-loaded chunks are
     * fetched from the domain root. Under a public docroot /build already resolves
     * to public/build, so a failure there is logged but not fatal. Mirrors 6amMart.
     */
    private function linkPublicBuildToProjectRoot(): bool
    {
        $target = public_path('build');
        if (!File::isDirectory($target)) {
            return false;
        }

        $link = base_path('build');

        // Already wired up: a symlink that resolves to public/build always tracks
        // the current bundle (nothing to rebuild), so accept it as-is. This makes
        // the "create the symlink over SSH" fix work even when the web-server user
        // cannot write the project root — rebuilding would try to unlink it first
        // and fail on a locked-down root.
        if (is_link($link) && @realpath($link) === @realpath($target)) {
            return true;
        }

        if ($this->rebuildProjectRootBuild($target, $link)) {
            return true;
        }

        \Log::warning('Builder: could not create "build" at the project root ' . base_path()
            . ' — grant the web-server user write permission there (or create the symlink over SSH). public/build was created normally.');

        // This only breaks a root docroot; under a public docroot the storefront
        // still loads from public/build, so don't fail activation for it.
        $rootDocroot = (defined('DOMAIN_POINTED_DIRECTORY') ? DOMAIN_POINTED_DIRECTORY : 'public') !== 'public';
        return !$rootDocroot;
    }

    /**
     * (Re)create the project-root `build` entry pointing at public/build, fresh
     * each time so it tracks the current bundle. A relative symlink when symlink()
     * works (portable, matches `npm run ensure:symlink`), otherwise a real copy
     * for hosts that disable symlink(). All filesystem errors are swallowed into a
     * false return so a locked-down root can never surface as a 500. Mirrors 6amMart.
     */
    private function rebuildProjectRootBuild(string $target, string $link): bool
    {
        try {
            if (is_link($link) || is_file($link)) {
                @unlink($link);
            } elseif (File::isDirectory($link)) {
                File::deleteDirectory($link);
            }

            if (function_exists('symlink') && @symlink('public/build', $link)) {
                return true;
            }

            return File::copyDirectory($target, $link);
        } catch (\Throwable) {
            return false;
        }
    }

//    public function upload(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'file_upload' => 'required|mimes:zip'
//        ]);
//
//        if ($validator->errors()->count() > 0) {
//            $error = Helpers::error_processor($validator);
//            return response()->json(['status' => 'error', 'message' => $error[0]['message']]);
//        }
//
//        $file = $request->file('file_upload');
//        $filename = $file->getClientOriginalName();
//        $tempPath = $file->storeAs('temp', $filename);
//        $zip = new \ZipArchive();
//
//        if ($zip->open(storage_path('app/' . $tempPath)) === TRUE) {
//            // Extract the contents to a directory
//            $extractPath = base_path('Modules/');
//            $zip->extractTo($extractPath);
//            $zip->close();
//            if(File::exists($extractPath.'/'.explode('.', $filename)[0].'/Addon/info.php')){
//                File::chmod($extractPath.'/'.explode('.', $filename)[0].'/Addon', 0777);
//                Toastr::success(translate('file_upload_successfully!'));
//                $status = 'success';
//                $message = translate('file_upload_successfully!');
//            }else{
//                File::deleteDirectory($extractPath.'/'.explode('.', $filename)[0]);
//                $status = 'error';
//                $message = translate('invalid_file!');
//            }
//        }else{
//            $status = 'error';
//            $message = translate('file_upload_fail!');
//        }
//
//        Storage::delete($tempPath);
//
//        return response()->json([
//            'status' => $status,
//            'message'=> $message
//        ]);
//    }
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_upload' => 'required|mimes:zip'
        ]);

        if ($validator->fails()) {
            $error = Helpers::error_processor($validator);
            return response()->json(['status' => 'error', 'message' => $error[0]['message']]);
        }

        $file = $request->file('file_upload');

        try {
            Helpers::validateFile($file, ADDON_MAX_FILE_SIZE);
        } catch (\App\Exceptions\InvalidUploadException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        $filename = $file->getClientOriginalName();
        $tempPath = $file->storeAs('temp', $filename);
        $zip = new \ZipArchive();

        if ($zip->open(storage_path('app/' . $tempPath)) === TRUE) {
            // Extract the contents to a directory
            $extractPath = base_path('Modules');
            $addonPath = $extractPath . '/' . explode('.', $filename)[0] . '/Addon';
            $preInfoFilePath = $addonPath . '/info.php';
            if (!File::isWritable($extractPath)) {
                        $status = 'error';
                        $message = translate('messages.File is not writable. Please check your file permissions.');
                        return response()->json(['status' => $status, 'message' => $message]);
                    }
            $isPublished = null;
            if (File::exists($preInfoFilePath)){
                $preInfoArray = include $preInfoFilePath;
                $isPublished = $preInfoArray['is_published'] ?? null;
            }
            $zip->extractTo($extractPath);
            $zip->close();
            $infoFilePath = $addonPath . '/info.php';
            if (File::exists($infoFilePath)) {
                $infoArray = include $infoFilePath;
                if ($isPublished !== null) {
                    $infoArray['is_published'] = $isPublished;
                }
                unset($infoArray['purchase_code'], $infoArray['username'], $infoArray['software_id']);
                $phpCode = "<?php return " . var_export($infoArray, true) . ";";
                file_put_contents($infoFilePath, $phpCode);

                if (File::exists($infoFilePath)) {
                    File::chmod($addonPath, 0777);

                    // #5 — the Builder storefront reads public/build/manifest.json at
                    // render time, so extract its bundle on UPLOAD too (not only on
                    // publish); otherwise hitting the storefront between upload and
                    // publish throws "Vite manifest not found". Best-effort — the
                    // environment checks still run when publishing.
                    if (($infoArray['name'] ?? null) === 'Builder') {
                        $this->extractBuilderBundle();
                    }

                    Toastr::success(translate('file_upload_successfully!'));
                    $status = 'success';
                    $message = translate('file_upload_successfully!');
                } else {
                    File::deleteDirectory($extractPath.'/'.explode('.', $filename)[0]);
                    $status = 'error';
                    $message = translate('invalid_file!');
                }
            }else{
                $status = 'error';
                $message = translate('invalid_file!');
            }
        } else {
            $status = 'error';
            $message = translate('file_upload_fail!');
        }
        Storage::delete($tempPath);

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function delete_theme(Request $request){
        $path = $request->path;

        $full_path = base_path($path);

        if(File::deleteDirectory($full_path)){
            return response()->json([
                'status' => 'success',
                'message'=> translate('file_delete_successfully')
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message'=> translate('file_delete_fail')
            ]);
        }

    }

    //helper functions
    function getDirectories(string $path): array
    {
        $fullPath = base_path($path);

        if (!is_dir($fullPath)) {
            return [];
        }

        $directories = [];

        foreach (scandir($fullPath) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (is_dir($fullPath . DIRECTORY_SEPARATOR . $item)) {
                $directories[] = $item;
            }
        }

        return $directories;
    }
}
