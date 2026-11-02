<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;

class GenerateAdminRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:admin-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate admin formatted routes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes = Route::getRoutes();
        $adminRoutes = collect($routes->getRoutesByMethod()['GET'])->filter(function ($route) {
            return Str::startsWith($route->uri(), 'admin');
        });

        $excludeTermsRoute = [
            'print', 'download', 'export', 'edit', 'update', 'invoice', 'child', 'update-default-status', 'update-status',
            'system-currency', 'status', 'paidStatus', 'priority', 'remove-proof-image', 'select-customer', 'orders', 'logs',
            'refund_mode', 'account-transaction/create', 'provide-deliveryman-earnings/create', 'system-addons', 'social-media/create'
        ];

        $excludeTermsAjax = $this->getAjaxRoutes($adminRoutes);
        $jsonFilePath = public_path('admin_formatted_routes.json');
        $excludeTerms = array_merge($excludeTermsAjax, $excludeTermsRoute);
        $formattedRoutes = [];

        foreach ($adminRoutes as $route) {
            $uri = $route->uri();
            $exclude = collect($excludeTerms)->contains(function ($term) use ($uri) {
                return Str::contains($uri, $term);
            });

            if (!$exclude) {
                $hasParameters = preg_match('/\{(.*?)\}/', $uri);
                if (!$hasParameters) {
                    $actualRouteName = $route->getName();
                    $routeName = ucwords(str_replace(['.', '_','-'], ' ', Str::afterLast($actualRouteName, '.')));
                    $bladePath = $this->getBladePathFromController($route);
                    $keywords = $this->getTextDataFromBladeFile($bladePath);
                    $keywords = ucwords(str_replace(['.', '_' ,'-'] ,' ', $keywords));
                    if($bladePath){
                        $formattedRoutes[] = [
                            'routeName' => $routeName,
                            'URI' => $uri,
                            'keywords' => $keywords,
                            'bladePath' =>  $bladePath,
                            'isModified' => false
                        ];
                    }
                }
            }
        }

        $formattedRoutes = $this->manualyAddedBladePath($formattedRoutes);

        if (file_exists($jsonFilePath)) {
            $fileContents = file_get_contents($jsonFilePath);
            $existingRoutes = json_decode($fileContents, true) ?? [];

            $newRoutes = array_filter($formattedRoutes, function ($newRoute) use ($existingRoutes) {
                foreach ($existingRoutes as $existingRoute) {
                    if ($existingRoute['URI'] === $newRoute['URI']) {
                        return false;
                    }
                }
                return true;
            });

            if (!empty($newRoutes)) {
                $updatedRoutes = array_merge($existingRoutes, $newRoutes);
                file_put_contents($jsonFilePath, json_encode($updatedRoutes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        } else {
            file_put_contents($jsonFilePath, json_encode($formattedRoutes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return 0;
    }

    function getAjaxRoutes ($adminRoutes): array
    {
        $jsonRoutes = [];
        $route_names = [];

        foreach ($adminRoutes as $route) {
            $uri = $route->uri();
            $action = $route->getAction();

            $controller = $action['controller'] ?? null;
            if ($controller && str_contains($controller, '@')) {
                list($controllerClass, $method) = explode('@', $controller);

                if (class_exists($controllerClass) && method_exists($controllerClass, $method)) {
                    $reflectionMethod = new \ReflectionMethod($controllerClass, $method);
                    $filename = $reflectionMethod->getFileName();
                    $startLine = $reflectionMethod->getStartLine();
                    $endLine = $reflectionMethod->getEndLine();

                    $file = file($filename);
                    $methodBody = implode('', array_slice($file, $startLine - 1, $endLine - $startLine + 1));

                    if (strpos($methodBody, 'return response()->json') !== false) {
                        $jsonRoutes[] = [
                            'method' => implode('|', $route->methods()),
                            'uri' => $uri,
                            'controller' => $controller
                        ];
                    }
                }
            }
        }

        foreach ($jsonRoutes as $route) {
            $route_names[] =$route['uri'];
        }

        return $route_names;
    }

    function getBladePathFromController($route): ?string
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;

        if ($controller && str_contains($controller, '@')) {
            list($controllerClass, $method) = explode('@', $controller);

            if (class_exists($controllerClass) && method_exists($controllerClass, $method)) {
                $reflectionMethod = new \ReflectionMethod($controllerClass, $method);
                $filename = $reflectionMethod->getFileName();
                $startLine = $reflectionMethod->getStartLine();
                $endLine = $reflectionMethod->getEndLine();

                $file = file($filename);
                $methodBody = implode('', array_slice($file, $startLine - 1, $endLine - $startLine + 1));

                if (preg_match("/view\\(['\"](.*?)['\"]/", $methodBody, $matches)) {
                    return str_replace('.', '/', $matches[1]);
                }
            }
        }

        return null;
    }

    function getTextDataFromBladeFile($viewPath): ? string
    {
        try {
            if (!$viewPath) {
                return null;
            }
            if (!View::exists($viewPath)) {
                return null;
            }
            $viewFilePath = View::getFinder()->find($viewPath);
            if (!File::exists($viewFilePath)) {
                return null;
            }

            $pattern = "/translate\('([^']+)'\)/";
            $textData = [];

            $content = File::get($viewFilePath);
            preg_match_all($pattern, $content, $matches);

            if (!empty($matches[1])) {
                foreach ($matches[1] as $text) {
                    $cleanedText = preg_replace("/^messages\./", "", $text);
                    $cleanedText = preg_replace("/[_:\?\.,-]+/", " ", $cleanedText);
                    $cleanedText = preg_replace("/\d+/", "", $cleanedText);
                    $cleanedText = preg_replace("/\s+/", " ", trim($cleanedText));

                    $textData[] = $cleanedText;
                }
            }

            $textData = array_unique($textData);
            $finalText = implode(" ", $textData);

            return trim($finalText);
        }
        catch (\Exception $exception) {
            info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
            return null;
        }
    }

    private function manualyAddedBladePath($formattedRoutes): array
    {
        $array = [
            'admin-views.business-settings.invoice-setup.index' => ['admin/business-settings/invoice-setup'],
        ];

        foreach ($array as $bladePath => $value) {
            foreach ($value as $uri) {
                $formattedRoutes=  $this->genetateRouteJsonFileFormate($formattedRoutes,$bladePath,$this->getRouteName($bladePath), $uri);
            }
        }
        return $formattedRoutes;
    }

    private function genetateRouteJsonFileFormate($formattedRoutes,$bladePath, $routeName, $uri) : array  {
        $bladePaths = is_array($bladePath) ? $bladePath : [null => $bladePath];

        foreach ($bladePaths as $path) {
            if (!$path) continue;

            $keywords = $this->getTextDataFromBladeFile($path);
            $keywords = ucwords(str_replace(['.', '_', '-'], ' ', $keywords));

            if (strlen($keywords) > 3) {
                $formattedRoutes[] = [
                    'routeName'   => $routeName,
                    'URI'         => $uri,
                    'keywords'    => $keywords,
                    'bladePath'   => $path,
                    'isModified'  => false,
                ];
            }
        }
        return $formattedRoutes;
    }

    private function getRouteName($actualRouteName){
        $routeNameParts = explode('.', $actualRouteName);
        if (count($routeNameParts) >= 2) {
            $lastPart = $routeNameParts[count($routeNameParts) - 1];
            $secondLastPart = $routeNameParts[count($routeNameParts) - 2];

            if (strtolower($lastPart) === 'index') {
                $lastPart = 'List';
            }

            $lastPartWords = explode(' ', str_replace(['_', '-'], ' ', $lastPart));
            $secondLastPartWords = explode(' ', str_replace(['_', '-'], ' ', $secondLastPart));
            $allWords = array_merge($secondLastPartWords, $lastPartWords);
            $uniqueWords = [];

            foreach ($allWords as $word) {
                $lowerWord = strtolower($word);
                if (empty($uniqueWords) || strtolower(end($uniqueWords)) !== $lowerWord) {
                    $uniqueWords[] = $word;
                }
            }

            if (count($uniqueWords) > 1 && strtolower($uniqueWords[0]) === strtolower(end($uniqueWords))) {
                array_shift($uniqueWords);
            }

            $uniqueWords = array_filter($uniqueWords, function ($word) {
                return strtolower($word) !== 'rental';
            });

            $routeName = ucwords(implode(' ', $uniqueWords));
        } else {
            $routeName = ucwords(str_replace(['.', '_', '-'], ' ', Str::afterLast($actualRouteName, '.')));
        }
        return $routeName;
    }
}
