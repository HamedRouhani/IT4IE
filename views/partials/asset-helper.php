<?php
/**
 * Asset helpers
 *  - automatic cache busting (?v=filemtime) instead of hand-edited version numbers
 *  - optional CSS bundles produced by `php tools/build-css.php`
 *    (used only while their source hashes match; otherwise the source files are linked, so a forgotten
 *     rebuild can never serve stale CSS)
 */
if (!function_exists('it4ie_asset_url')) {

    function it4ie_public_path(): string
    {
        return defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public';
    }

    function it4ie_asset_exists(string $rel): bool
    {
        return is_file(it4ie_public_path() . '/assets/' . $rel);
    }

    /** $rel is relative to /assets/, e.g. "css/software.css" */
    function it4ie_asset_url(string $rel): string
    {
        $file = it4ie_public_path() . '/assets/' . $rel;
        $v = is_file($file) ? (int) filemtime($file) : 1;
        return '/assets/' . $rel . '?v=' . $v;
    }

    function it4ie_css_link(string $rel): string
    {
        return '<link rel="stylesheet" href="' . htmlspecialchars(it4ie_asset_url($rel), ENT_QUOTES, 'UTF-8') . '">';
    }

    /** True when dist/manifest.json exists and its source content hashes match. */
    function it4ie_bundles_fresh(): bool
    {
        static $fresh = null;
        if ($fresh !== null) {
            return $fresh;
        }
        $fresh = false;
        $manifest = it4ie_public_path() . '/assets/dist/manifest.json';
        if (!is_file($manifest)) {
            return $fresh;
        }
        $data = json_decode((string) file_get_contents($manifest), true);
        if (!is_array($data) || empty($data['files']) || !is_array($data['files'])) {
            return $fresh;
        }
        // Cache the content check briefly across PHP requests when APCu is available.
        // A source edit is picked up within 60 seconds; a new manifest gets a new key.
        $cacheKey = 'it4ie-css-bundle-v2:' . $manifest . ':' . filemtime($manifest) . ':' . filesize($manifest);
        if (function_exists('apcu_fetch') && function_exists('apcu_store')) {
            $cached = apcu_fetch($cacheKey, $found);
            if ($found) {
                return $fresh = (bool) $cached;
            }
        }
        foreach ($data['files'] as $rel => $hash) {
            $file = it4ie_public_path() . '/assets/' . $rel;
            if (!is_file($file) || hash_file('sha256', $file) !== (string) $hash) {
                return $fresh;
            }
        }
        // the list of design-system modules decides what goes into each software bundle
        $cfgFile = (defined('CONFIG_PATH') ? CONFIG_PATH : dirname(__DIR__, 2) . '/app/config') . '/design-system.php';
        if (isset($data['config']) && (!is_file($cfgFile) || hash_file('sha256', $cfgFile) !== (string) $data['config'])) {
            return $fresh;
        }
        $fresh = true;
        if (function_exists('apcu_store')) {
            apcu_store($cacheKey, true, 60);
        }
        return $fresh;
    }

    /** Modules that use the shared design system (app/config/design-system.php). */
    function it4ie_ds_modules(): array
    {
        static $modules = null;
        if ($modules === null) {
            $dir = defined('CONFIG_PATH') ? CONFIG_PATH : dirname(__DIR__, 2) . '/app/config';
            $file = $dir . '/design-system.php';
            $cfg = is_file($file) ? include $file : [];
            $list = is_array($cfg) && isset($cfg['modules']) && is_array($cfg['modules']) ? $cfg['modules'] : [];
            $modules = array_values(array_filter($list, 'is_string'));
        }
        return $modules;
    }

    function it4ie_is_ds(?string $module): bool
    {
        return $module !== null && in_array($module, it4ie_ds_modules(), true);
    }

    /** Public pages (main, auth, profile, admin). */
    function it4ie_css_app(): string
    {
        if (it4ie_bundles_fresh() && it4ie_asset_exists('dist/app.min.css')) {
            return it4ie_css_link('dist/app.min.css');
        }
        return it4ie_css_link('css/style.css');
    }

    /** Software shell for one module, in the cascade order the modules were designed for. */
    function it4ie_css_software(string $module): string
    {
        $safe = preg_replace('/[^a-z0-9_-]/i', '', $module);
        $bundle = 'dist/software-' . $safe . '.min.css';
        if (it4ie_bundles_fresh() && it4ie_asset_exists($bundle)) {
            return it4ie_css_link($bundle);
        }
        $files = [(it4ie_bundles_fresh() && it4ie_asset_exists('dist/app.min.css')) ? 'dist/app.min.css' : 'css/style.css', 'css/software.css'];
        if (it4ie_is_ds($module)) {
            $files[] = 'css/ds/it4ie-ds.css';
        }
        $files[] = 'css/modules/' . $safe . '.css';
        $files[] = 'css/software-refinement.css';
        $files[] = 'css/software-themes.css';
        $links = [];
        foreach ($files as $f) {
            if (it4ie_asset_exists($f)) {
                $links[] = it4ie_css_link($f);
            }
        }
        return implode("\n    ", $links);
    }
}
