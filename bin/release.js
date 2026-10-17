#!/usr/bin/env node
/**
 * Release of a new version of KaosH&F.
 *
 *   node bin/release.js keygen   creates the signing key pair (only once)
 *   node bin/release.js          builds the zip, signs it and publishes the release on GitHub
 *
 * The private key stays in %USERPROFILE%\.kaoshf (outside the repository and OneDrive).
 * The public key goes in includes/class-khf-updater.php (PUBLIC_KEY).
 */

'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');
const crypto = require('crypto');
const { execFileSync } = require('child_process');

const ROOT = path.resolve(__dirname, '..');
const KEY_DIR = path.join(os.homedir(), '.kaoshf');
const KEY_FILE = path.join(KEY_DIR, 'signing-key.pem');
const GH = process.platform === 'win32' && fs.existsSync('C:\\Program Files\\GitHub CLI\\gh.exe') ? 'C:\\Program Files\\GitHub CLI\\gh.exe' : 'gh';

function fail(msg) {
	console.error('\n✖ ' + msg + '\n');
	process.exit(1);
}

function run(cmd, args, opts = {}) {
	const out = execFileSync(cmd, args, Object.assign({ cwd: ROOT, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] }, opts));
	return out ? String(out).trim() : ''; // con stdio "inherit" l'output va a schermo e qui arriva null
}

function rawPublicKey(keyObject) {
	// SPKI DER of an Ed25519 key: the last 32 bytes are the "raw" public key used by libsodium.
	return crypto.createPublicKey(keyObject).export({ type: 'spki', format: 'der' }).subarray(-32).toString('base64');
}

function keygen() {
	if (fs.existsSync(KEY_FILE)) {
		fail('The key already exists in ' + KEY_FILE + '. I will not overwrite it: if you lose it, sites will no longer accept updates.');
	}
	fs.mkdirSync(KEY_DIR, { recursive: true });
	const { privateKey } = crypto.generateKeyPairSync('ed25519');
	fs.writeFileSync(KEY_FILE, privateKey.export({ type: 'pkcs8', format: 'pem' }), { mode: 0o600 });
	console.log('Private key saved in: ' + KEY_FILE);
	console.log('Make an offline backup (e.g. a USB stick): without it you will not be able to sign new versions.');
	console.log('\nPublic key (goes in PUBLIC_KEY of includes/class-khf-updater.php):\n' + rawPublicKey(privateKey));
}

function read(rel) {
	return fs.readFileSync(path.join(ROOT, rel), 'utf8');
}

function release() {
	if (!fs.existsSync(KEY_FILE)) {
		fail('Signing key not found in ' + KEY_FILE + '. Create it with: node bin/release.js keygen');
	}
	const privateKey = crypto.createPrivateKey(fs.readFileSync(KEY_FILE));

	// Version: it must match in the plugin header, the constant and the readme.
	const main = read('kaos-header-footer.php');
	const header = (main.match(/^\s*\*\s*Version:\s*(\S+)/m) || [])[1];
	const constant = (main.match(/define\(\s*'KAOSHF_VERSION',\s*'([^']+)'/) || [])[1];
	const readme = read('readme.txt');
	const stable = (readme.match(/^Stable tag:\s*(\S+)/m) || [])[1];
	if (!header || header !== constant || header !== stable) {
		fail('Different versions: header ' + header + ', constant ' + constant + ', readme ' + stable + '.');
	}
	const version = header;
	if (!/^\d+\.\d+\.\d+$/.test(version)) {
		fail('The version must be in the 1.2.3 format (found ' + version + ').');
	}
	const notes = (readme.split(/^= /m).find((s) => s.startsWith(version + ' =')) || '').replace(/^.*=\s*\n/, '').trim();
	if (!notes) {
		fail('The entry "= ' + version + ' =" is missing from the Changelog in readme.txt.');
	}

	// Public key in the plugin: it must match the private key, otherwise sites would reject the update.
	const updater = read('includes/class-khf-updater.php');
	const embedded = (updater.match(/const PUBLIC_KEY\s*=\s*'([^']+)'/) || [])[1];
	if (embedded !== rawPublicKey(privateKey)) {
		fail('The public key in class-khf-updater.php does not match the private key.');
	}

	// Repository: everything committed, on main, in sync with GitHub, version not yet published.
	if (run('git', ['status', '--porcelain'])) {
		fail('There are uncommitted changes.');
	}
	if (run('git', ['rev-parse', '--abbrev-ref', 'HEAD']) !== 'main') {
		fail('Releases are made from the main branch.');
	}
	run('git', ['fetch', 'origin', 'main']);
	if (run('git', ['rev-parse', 'HEAD']) !== run('git', ['rev-parse', 'origin/main'])) {
		fail('The local main branch is not in sync with GitHub: run git push (or git pull) first.');
	}
	if (run('git', ['tag', '--list', 'v' + version])) {
		fail('Version ' + version + ' has already been released: raise the version number.');
	}

	// Zip (without the development files, see .gitattributes) and signature.
	const dist = path.join(ROOT, 'dist');
	fs.mkdirSync(dist, { recursive: true });
	const zip = path.join(dist, 'kaos-header-footer-' + version + '.zip');
	run('git', ['archive', '--format=zip', '--prefix=kaos-header-footer/', '-o', zip, 'HEAD']);
	const data = fs.readFileSync(zip);
	const signature = crypto.sign(null, data, privateKey);
	if (!crypto.verify(null, data, crypto.createPublicKey(privateKey), signature)) {
		fail('Signature verification failed.');
	}
	fs.writeFileSync(zip + '.sig', signature.toString('base64') + '\n');
	console.log('Created ' + path.relative(ROOT, zip) + ' (' + Math.round(data.length / 1024) + ' KB) and its signature.');

	// GitHub release (it also creates the tag).
	const notesFile = path.join(dist, 'notes-' + version + '.md');
	fs.writeFileSync(notesFile, notes + '\n');
	run(GH, ['release', 'create', 'v' + version, zip, zip + '.sig', '--title', 'KaosH&F ' + version, '--notes-file', notesFile, '--target', 'main'], { stdio: ['ignore', 'inherit', 'inherit'] });
	run('git', ['fetch', '--tags']);
	console.log('\n✔ Version ' + version + ' published. Sites will see it under Dashboard → Updates within a few hours (or right away with "Check for updates").');
}

if (process.argv[2] === 'keygen') {
	keygen();
} else if (!process.argv[2]) {
	release();
} else {
	fail('Unknown command. Use: node bin/release.js [keygen]');
}
