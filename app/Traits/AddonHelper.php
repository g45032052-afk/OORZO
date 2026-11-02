<?php

namespace App\Traits;

trait AddonHelper
{
    public function get_addons(): array
    {
        $dir = 'Modules';
        $directories = self::getDirectories($dir);
        $addons = [];
        foreach ($directories as $directory) {
            $sub_dirs = self::getDirectories('Modules/' . $directory);
            if (in_array('Addon', $sub_dirs)) {
                $addons[] = 'Modules/' . $directory;
            }
        }

        $array = [];
        foreach ($addons as $item) {
            $full_data = include(base_path($item . '/Addon/info.php'));
            $array[] = [
                'addon_name' => $full_data['name'],
                'is_published' => $full_data['is_published'],
            ];
        }

        return $array;
    }

    public function get_addon_admin_routes(): array
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

        $full_data = [];
        foreach ($addons as $item) {
            $info = include(base_path($item . '/Addon/info.php'));
            if ($info['is_published']){
                // Not every published addon ships an Addon/admin_routes.php — modern
                // module addons (e.g. Builder) register their admin routes via their
                // own RouteServiceProvider instead. Skip when the legacy file is absent
                // rather than fataling the whole request.
                $adminRoutesFile = base_path($item . '/Addon/admin_routes.php');
                if (file_exists($adminRoutesFile)) {
                    $full_data[] = include($adminRoutesFile);
                }
            }
        }

        return $full_data;
    }

    public function get_payment_publish_status(): array
    {
        $dir = 'Modules'; // Update the directory path to Modules/Gateways
        $directories = self::getDirectories($dir);
        // dd($directories);
        $addons = [];
        foreach ($directories as $directory) {
            $sub_dirs = self::getDirectories($dir . '/' . $directory); // Use $dir instead of 'Modules/'
            if($directory == 'Gateways'){
                if (in_array('Addon', $sub_dirs)) {
                    $addons[] = $dir . '/' . $directory; // Use $dir instead of 'Modules/'
                }
            }
        }

        $array = [];
        foreach ($addons as $item) {
            $full_data = include(base_path($item . '/Addon/info.php'));
            $array[] = [
                'is_published' => $full_data['is_published'],
            ];
        }

        return $array;
    }


    function getDirectories(string $path): array
    {
        $directories = [];
        // if (DOMAIN_POINTED_DIRECTORY == 'public') {
        //     $path = '../'.$path;
        // }
        $path= base_path($path);
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '..' || $item == '.')
                continue;
            if (is_dir($path . '/' . $item))
                $directories[] = $item;
        }
        return $directories;
    }
}
