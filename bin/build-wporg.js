#!/usr/bin/env node
/**
 * Zip for WordPress.org: same as the release zip, but without the bundled translations
 * (on WordPress.org they come from translate.wordpress.org and WordPress loads them by itself).
 *
 *   node bin/build-wporg.js
 *
 * Creates dist/kaos-header-footer-wporg-<version>.zip with the kaos-header-footer/ folder inside.
 */

'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');
const { execFileSync } = require('child_process');

const ROOT = path.resolve(__dirname, '..');
// On Windows use the system tar (bsdtar): the one from Git cannot handle C:\ paths or create zips.
// On Linux (CI) tar and zip are enough.
const WIN = process.platform === 'win32';
const TAR = WIN ? path.join(process.env.SystemRoot || 'C:\Windows', 'System32', 'tar.exe') : 'tar';
const run = (cmd, args, cwd = ROOT) => execFileSync(cmd, args, { cwd, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] });

const main = fs.readFileSync(path.join(ROOT, 'kaos-header-footer.php'), 'utf8');
const version = (main.match(/^\s*\*\s*Version:\s*(\S+)/m) || [])[1];
if (!version) {
	throw new Error('Version not found in kaos-header-footer.php');
}

// The zip comes from the last commit: with unsaved changes it would not match the files.
if (run('git', ['status', '--porcelain']).trim()) {
	throw new Error('There are uncommitted changes: commit first.');
}

const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'kaos-hf-wporg-'));
const tar = path.join(tmp, 'src.tar');
run('git', ['archive', '--format=tar', '--prefix=kaos-header-footer/', '-o', tar, 'HEAD']);
run(TAR, ['-xf', tar, '-C', tmp]);
fs.unlinkSync(tar);
const dir = path.join(tmp, 'kaos-header-footer');

// No bundled translations: they arrive from translate.wordpress.org.
fs.rmSync(path.join(dir, 'languages'), { recursive: true, force: true });
const php = path.join(dir, 'kaos-header-footer.php');
fs.writeFileSync(php, fs.readFileSync(php, 'utf8').replace(/^add_action\(\s*'init',\s*function \(\) \{\s*load_plugin_textdomain\([^;]*\);\s*\},\s*0\s*\);\r?\n/m, ''));
const admin = path.join(dir, 'includes', 'class-khf-admin.php');
fs.writeFileSync(admin, fs.readFileSync(admin, 'utf8').replace(/(wp_set_script_translations\( '[\w-]+', 'kaos-header-footer'), KHF_DIR \. 'languages' \)/g, '$1 )'));

for (const [file, re] of [['kaos-header-footer.php', /load_plugin_textdomain/], ['includes/class-khf-admin.php', /'languages'/]]) {
	if (re.test(fs.readFileSync(path.join(dir, file), 'utf8'))) {
		throw new Error('Cleanup failed in ' + file);
	}
}

const out = path.join(ROOT, 'dist', 'kaos-header-footer-wporg-' + version + '.zip');
fs.mkdirSync(path.dirname(out), { recursive: true });
if (fs.existsSync(out)) {
	fs.unlinkSync(out);
}
if (WIN) {
	run(TAR, ['-a', '-c', '-f', out, 'kaos-header-footer'], tmp);
} else {
	run('zip', ['-qr', out, 'kaos-header-footer'], tmp);
}
fs.rmSync(tmp, { recursive: true, force: true });
console.log('Created ' + path.relative(ROOT, out) + ' (' + Math.round(fs.statSync(out).size / 1024) + ' KB)');
