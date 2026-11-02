<?php

namespace App\Builder;

use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use Modules\Builder\Contracts\MediaUrlResolver as MediaUrlResolverContract;

/**
 * StackFood media URLs.
 *
 * StackFood's Helpers::get_full_url($path, $data, $disk) differs from 6amMart's
 * signature — the placeholder category is the FIRST arg ($path/$folder) and the
 * 3rd arg is the storage disk. The contract's $type hint is unused here because
 * StackFood derives the placeholder from $folder.
 */
class MediaUrlResolver implements MediaUrlResolverContract
{
    public function defaultLogoUrl(): ?string
    {
        try {
            $logo = BusinessSetting::where('key', 'logo')->first();
            $disk = $logo?->storage[0]?->value ?? 'public';

            return Helpers::get_full_url('business', $logo?->value, $disk);
        } catch (\Throwable) {
            return null;
        }
    }

    public function url(string $folder, string $filename, string $disk = 'public', ?string $type = null): string
    {
        return Helpers::get_full_url($folder, $filename, $disk);
    }

    public function assetUrl(string $path): string
    {
        // dynamicAsset() emits the mode-correct path: /assets/... in
        // public-mode (Herd, web root = public/) and /public/assets/... in
        // root-mode — so the module never has to know StackFood's serving mode.
        return dynamicAsset($path);
    }

    public function storageBaseUrl(): string
    {
        // dynamicStorage() maps the public storage disk to the right base:
        // /storage in public-mode, /storage/app/public in root-mode.
        return dynamicStorage('storage/app/public');
    }
}
