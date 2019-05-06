<?php namespace Samuell\Cdn\Classes;

use Cms\Classes\Controller;
use Cms\Classes\Theme;
use Cache;
use SystemException;

class TwigExtension
{

    /**
     * Get asset path for cdn.
     *
     * @param  string  $path
     * @param  boolean $useManifest
     * @return string
     */
    public function assetCdn($path, $useManifest = true): string
    {
        // Use manifest to determine path
        if (config('cdn.useManifest') && $useManifest) {
            $outputPath = $this->readManifest(basename($path));
        } else {
            // If cdn is disabled return url from local active theme
            if (!config('cdn.active')) {
                return (new Controller)->themeUrl($path);
            }

            $cdnUrl = rtrim(config('cdn.url'), '/');
            $outputPath = $cdnUrl . '/' . trim($path, '/');
        }

        return $outputPath;
    }

    /**
     * Get path for cdn.
     *
     * @param  string  $path
     * @return string
     */
    public function cdn($path): string
    {
        // If cdn is disabled return url from local active theme
        if (!config('cdn.active')) {
            return (new Controller)->themeUrl($path);
        }

        // Remove slashes from ending of the path
        $cdnUrl = rtrim(config('cdn.url'), '/');

        return $cdnUrl . '/' . trim($path, '/');
    }

    private function readManifest($path)
    {
        $manifest = Cache::rememberForever('cdn:manifest', function () {
            $themePath = Theme::getActiveTheme()->getPath();
            $manifestPath = $themePath . config('cdn.manifestPath');
            if (file_exists($manifestPath)) {
                return json_decode(file_get_contents($manifestPath));
            } else {
                throw new SystemException('Missing manifest.json file');
            }
        });

        return $manifest->$path;
    }
}
