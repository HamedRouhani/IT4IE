#!/usr/bin/env node
/** Build deployable CSS bundles without PHP dependencies: node tools/build-css.js */
'use strict';
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const zlib = require('node:zlib');
const root = path.resolve(__dirname, '..');
const assets = path.join(root, 'public', 'assets');
const cssDir = path.join(assets, 'css');
const dist = path.join(assets, 'dist');
const configFile = path.join(root, 'app', 'config', 'design-system.php');
const hashes = {};

function load(rel) {
    const file = path.join(assets, rel);
    if (!fs.existsSync(file)) throw new Error(`Missing CSS source: ${rel}`);
    const bytes = fs.readFileSync(file);
    hashes[rel] = crypto.createHash('sha256').update(bytes).digest('hex');
    return bytes.toString('utf8').replace(/^\uFEFF/, '').replace(/@charset\s+[^;]+;/gi, '');
}

function minify(source) {
    const saved = [];
    let result = source.replace(/\/\*[\s\S]*?\*\//g, '');
    result = result.replace(/"(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'|url\([^)]*\)/g, match => {
        saved.push(match);
        return `\u0001${saved.length - 1}\u0002`;
    });
    result = result.replace(/\s+/g, ' ').replace(/\s*([{};,>~])\s*/g, '$1').replace(/;}+/g, '}').trim();
    return result.replace(/\u0001(\d+)\u0002/g, (_, index) => saved[Number(index)]);
}

const style = load('css/style.css');
const app = style.replace(/@import\s+url\(\s*['"]?([^'"\)?]+)(?:\?[^'"\)]*)?['"]?\s*\)\s*;/gi,
    (_, filename) => `\n${load(`css/${filename}`)}\n`);
if (/@import\s/i.test(app)) throw new Error('Unresolved CSS @import in app bundle');

// This PHP config contains only a literal module list. Parse its quoted entries rather than evaluating PHP.
const config = fs.readFileSync(configFile);
const moduleList = config.toString('utf8').match(/['"]modules['"]\s*=>\s*\[([\s\S]*?)\]/);
if (!moduleList) throw new Error('Could not read the design-system module list');
const dsModules = new Set([...moduleList[1].matchAll(/['"]([a-z0-9_-]+)['"]/gi)].map(m => m[1]));
const bundles = { 'app.min.css': app };
for (const filename of fs.readdirSync(path.join(cssDir, 'modules')).filter(f => f.endsWith('.css')).sort()) {
    const module = filename.slice(0, -4);
    const parts = [app, load('css/software.css')];
    if (dsModules.has(module)) parts.push(load('css/ds/it4ie-ds.css'));
    parts.push(load(`css/modules/${filename}`), load('css/software-refinement.css'), load('css/software-themes.css'));
    bundles[`software-${module}.min.css`] = parts.join('\n');
}
fs.mkdirSync(dist, { recursive: true });
for (const [name, source] of Object.entries(bundles)) {
    const output = `@charset "UTF-8";${minify(source)}`;
    fs.writeFileSync(path.join(dist, name), output);
    console.log(`${name}: ${Buffer.byteLength(output)} bytes, ${zlib.gzipSync(output).length} gzip bytes`);
}
const files = Object.fromEntries(Object.entries(hashes).sort(([a], [b]) => a.localeCompare(b)));
fs.writeFileSync(path.join(dist, 'manifest.json'), JSON.stringify({
    built: Math.floor(Date.now() / 1000),
    config: crypto.createHash('sha256').update(config).digest('hex'),
    files
}, null, 4) + '\n');
console.log(`${Object.keys(bundles).length} bundles, ${Object.keys(files).length} source files tracked`);
