<?php
/**
 * IT4IE CSS bundler (no dependencies).
 *
 *   php tools/build-css.php          build bundles into public/assets/dist/
 *   php tools/build-css.php --clean  delete bundles (back to plain source files)
 *
 * Output
 *   dist/app.min.css                 everything style.css @imports   (public/auth/profile/admin pages)
 *   dist/software-<module>.min.css   style + software + [ds] + module + refinement + themes
 *   dist/manifest.json               source mtimes; views/partials/asset-helper.php ignores a bundle
 *                                    automatically if any source changed after the build.
 *
 * Run it as part of every deploy (after editing any CSS).
 */

$root   = dirname(__DIR__);
$assets = $root . '/public/assets';
$css    = $assets . '/css';
$dist   = $assets . '/dist';
$cfgFile = $root . '/app/config/design-system.php';

if (in_array('--clean', $argv ?? [], true)) {
    foreach (glob($dist . '/*') ?: [] as $f) {
        if (is_file($f)) {
            unlink($f);
        }
    }
    echo "dist/ cleaned. Pages now use the plain source CSS files.\n";
    exit(0);
}

function read_css(string $file): string
{
    $c = (string) file_get_contents($file);
    $c = preg_replace('/^\xEF\xBB\xBF/', '', $c);          // BOM
    $c = preg_replace('/@charset\s+[^;]+;/i', '', $c);     // re-added once at the top of each bundle
    return $c;
}

function min_css(string $css): string
{
    $css = preg_replace('!/\*.*?\*/!s', '', $css);                          // comments first
    $keep = [];
    $css = preg_replace_callback(
        '/"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'|url\([^)]*\)/',
        function ($m) use (&$keep) {
            $k = "\x01" . count($keep) . "\x02";
            $keep[$k] = $m[0];
            return $k;
        },
        $css
    );
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/\s*([{};,>~])\s*/', '$1', $css);
    $css = str_replace(';}', '}', $css);
    return strtr(trim($css), $keep);
}

$used = [];   // rel path (relative to /assets/) => mtime
function load(string $rel): string
{
    global $assets, $used;
    $file = $assets . '/' . $rel;
    if (!is_file($file)) {
        fwrite(STDERR, "  ! missing $rel (skipped)\n");
        return '';
    }
    $used[$rel] = filemtime($file);
    return read_css($file);
}

// ---- app bundle: style.css with every @import inlined, in order -------------------------------
$style = load('css/style.css');
$app = preg_replace_callback(
    '/@import\s+url\(\s*[\'"]?([^\'")?]+)(?:\?[^\'")]*)?[\'"]?\s*\)\s*;/i',
    function ($m) {
        return "\n" . load('css/' . $m[1]) . "\n";
    },
    $style
);

$dsModules = [];
if (is_file($cfgFile)) {
    $cfg = include $cfgFile;
    $dsModules = is_array($cfg) && isset($cfg['modules']) && is_array($cfg['modules']) ? $cfg['modules'] : [];
}

if (!is_dir($dist) && !mkdir($dist, 0775, true)) {
    fwrite(STDERR, "Cannot create $dist\n");
    exit(1);
}

$bundles = ['app.min.css' => $app];
foreach (glob($css . '/modules/*.css') ?: [] as $modFile) {
    $m = basename($modFile, '.css');
    $parts = [$app, load('css/software.css')];
    if (in_array($m, $dsModules, true)) {
        $parts[] = load('css/ds/it4ie-ds.css');
    }
    $parts[] = load('css/modules/' . $m . '.css');
    $parts[] = load('css/software-refinement.css');
    $parts[] = load('css/software-themes.css');
    $bundles['software-' . $m . '.min.css'] = implode("\n", $parts);
}

echo str_pad('bundle', 34) . str_pad('raw', 10) . str_pad('min', 10) . "gzip\n";
foreach ($bundles as $name => $raw) {
    $min = '@charset "UTF-8";' . min_css($raw);
    file_put_contents($dist . '/' . $name, $min);
    echo str_pad($name, 34)
        . str_pad(number_format(strlen($raw) / 1024, 1) . ' KB', 10)
        . str_pad(number_format(strlen($min) / 1024, 1) . ' KB', 10)
        . number_format(strlen(gzencode($min, 9)) / 1024, 1) . " KB\n";
}

ksort($used);
file_put_contents($dist . '/manifest.json', json_encode([
    'built'  => time(),
    'config' => is_file($cfgFile) ? filemtime($cfgFile) : 0,
    'files'  => $used,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "\nDone: " . count($bundles) . " bundles, " . count($used) . " source files tracked.\n";
