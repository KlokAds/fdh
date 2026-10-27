<?php

/*
 * Friends Dream Home — web installer for shared hosting without SSH (Hostinger, cPanel).
 * A step-by-step wizard in one browser tab. Locks itself when done; see App\Support\Install\WebInstaller.
 */

use App\Support\Install\WebInstaller;
use Illuminate\Validation\ValidationException;

$base = dirname(__DIR__);

if (! is_file($base.'/vendor/autoload.php')) {
    http_response_code(500);
    exit('vendor/ is missing: upload the release zip from GitHub (branch "release"), not the plain source code.');
}
require $base.'/vendor/autoload.php';

$installer = new WebInstaller($base);
if ($installer->closed()) {
    http_response_code(404);
    exit('Not found.');
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');

$https = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
session_name('fdh_install');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => $https]);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(16));
$host = preg_replace('/[^A-Za-z0-9.:-]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
$_SESSION['in'] ??= [
    'token' => '', 'db_driver' => 'mysql', 'db_host' => 'localhost', 'db_port' => '3306', 'db_name' => '', 'db_user' => '', 'db_pass' => '',
    'app_url' => ($https ? 'https://' : 'http://').$host, 'admin_path' => 'admin-'.bin2hex(random_bytes(3)), 'mail_from' => '', 'mail_pass' => '',
    'admin_name' => '', 'admin_email' => '', 'admin_password' => '', 'samples' => '1',
];
$_SESSION['step'] ??= 1;
$in = &$_SESSION['in'];
$step = (int) $_SESSION['step'];
$errors = [];
$log = null;
$installer->token(); // creates the token file on the first visit
$requirements = $installer->requirements();
$ready = ! in_array(false, array_column($requirements, 'ok'), true);

$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$post = static fn (string $k): string => trim((string) ($_POST[$k] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $errors[] = 'The page expired. Reload and try again.';
    } elseif (($_POST['action'] ?? '') === 'back') {
        $step = max(1, $step - 1);
    } elseif ($step === 1) {
        $in['token'] = $post('token');
        if (! $ready) {
            $errors[] = 'Fix the server checks marked in red first, then reload this page.';
        } elseif (! $installer->tokenMatches($in['token'])) {
            $errors[] = 'The setup code is wrong. Open storage/app/install-token.txt in File Manager and copy the code.';
        } else {
            $step = 2;
        }
    } elseif ($step === 2) {
        foreach (['db_driver', 'db_host', 'db_port', 'db_name', 'db_user'] as $k) {
            $in[$k] = $post($k);
        }
        $in['db_driver'] = $in['db_driver'] === 'sqlite' ? 'sqlite' : 'mysql';
        if ($in['db_driver'] === 'mysql') {
            if (($_POST['db_pass'] ?? '') !== '') {
                $in['db_pass'] = (string) $_POST['db_pass'];
            }
            foreach (['db_host' => 'Database host', 'db_name' => 'Database name', 'db_user' => 'Database user'] as $k => $label) {
                if ($in[$k] === '') {
                    $errors[] = "{$label} is needed.";
                }
            }
        }
        if ($errors === [] && ($dbError = $installer->databaseError($in)) !== null) {
            $errors[] = $dbError;
        }
        if ($errors === []) {
            $step = 3;
        }
    } elseif ($step === 3) {
        $in['app_url'] = rtrim($post('app_url'), '/');
        $in['admin_path'] = strtolower($post('admin_path'));
        $in['mail_from'] = $post('mail_from');
        if (($_POST['mail_pass'] ?? '') !== '') {
            $in['mail_pass'] = (string) $_POST['mail_pass'];
        }
        if (! filter_var($in['app_url'], FILTER_VALIDATE_URL) || ! preg_match('#^https?://#', $in['app_url'])) {
            $errors[] = 'Website address must be a full address like https://fdh.com.bd';
        }
        if (! preg_match('/^[a-z0-9][a-z0-9-]{3,40}$/', $in['admin_path'])) {
            $errors[] = 'Admin path: 4–40 lowercase letters, numbers or dashes.';
        }
        if ($in['mail_from'] !== '' && ! filter_var($in['mail_from'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'The sending email address is not valid.';
        }
        if ($errors === []) {
            $step = 4;
        }
    } elseif ($step === 4) {
        $in['admin_name'] = $post('admin_name');
        $in['admin_email'] = strtolower($post('admin_email'));
        $in['samples'] = isset($_POST['samples']) ? '1' : '0';
        $password = (string) ($_POST['admin_password'] ?? '');
        if ($in['admin_name'] === '') {
            $errors[] = 'Your name is needed.';
        }
        if (! filter_var($in['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Your email is not valid.';
        }
        if ($password !== '' || $in['admin_password'] === '') {
            if (strlen($password) < 12 || ! preg_match('/[a-z]/', $password) || ! preg_match('/[A-Z]/', $password) || ! preg_match('/\d/', $password) || ! preg_match('/[^A-Za-z0-9]/', $password)) {
                $errors[] = 'Password: at least 12 characters with upper and lower case letters, a number and a symbol.';
            } elseif ($password !== (string) ($_POST['admin_password_confirmation'] ?? '')) {
                $errors[] = 'The two passwords are not the same.';
            } else {
                $in['admin_password'] = $password;
            }
        }
        if ($errors === []) {
            $step = 5;
        }
    } elseif ($step === 5) {
        try {
            $log = $installer->install($in);
            $done = ['site' => $in['app_url'], 'admin' => $in['app_url'].'/'.$in['admin_path'].'/login', 'email' => $in['admin_email']];
            session_destroy();
            $step = 6;
        } catch (ValidationException $ex) {
            $errors = array_values(array_merge(...array_values($ex->errors())));
        } catch (Throwable $ex) {
            $errors[] = 'Install stopped: '.$ex->getMessage();
        }
    }
    if ($step !== 6) {
        $_SESSION['step'] = $step;
    }
}

$steps = [1 => 'Server check', 2 => 'Database', 3 => 'Website & email', 4 => 'Admin account', 5 => 'Install'];
$field = static function (string $name, string $label, array $o = []) use ($e, $in): string {
    $type = $o['type'] ?? 'text';
    $value = $type === 'password' ? '' : ($in[$name] ?? '');
    $attrs = (isset($o['placeholder']) ? ' placeholder="'.$e($o['placeholder']).'"' : '').(isset($o['auto']) ? ' autocomplete="'.$o['auto'].'"' : ' autocomplete="off"');
    $hint = isset($o['hint']) ? '<small>'.$o['hint'].'</small>' : '';

    return '<label><span>'.$e($label).(($o['optional'] ?? false) ? ' <em>optional</em>' : '').'</span><input name="'.$name.'" type="'.$type.'" value="'.$e($value).'"'.$attrs.'>'.$hint.'</label>';
};
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<meta name="color-scheme" content="light dark">
<title>Set up Friends Dream Home</title>
<link rel="icon" href="/images/fdh-logo.jpg">
<style>
    :root { --bg: #f2f5f9; --panel: #fff; --text: #14213a; --muted: #5f6b7d; --line: #dfe5ee; --brand: #1179b5; --navy: #0a1628; --ok: #1f8a4c; --ok-bg: #e6f5ec; --bad: #c0392b; --bad-bg: #fdf0ee; }
    @media (prefers-color-scheme: dark) { :root { --bg: #0b1424; --panel: #111f36; --text: #e6edf6; --muted: #8d9ab0; --line: #22344f; --ok: #4cc283; --ok-bg: rgba(76,194,131,.14); --bad: #f07467; --bad-bg: rgba(240,116,103,.12); } }
    * { box-sizing: border-box; }
    body { margin: 0; font: 15px/1.55 system-ui, -apple-system, 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); }
    header { background: var(--navy); color: #fff; padding: 18px 20px; }
    header .in { max-width: 760px; margin: 0 auto; display: flex; align-items: center; gap: 12px; }
    header img { width: 40px; height: 40px; border-radius: 8px; background: #fff; }
    header b { display: block; font-size: 1.1rem; } header small { color: #9fb0c6; }
    main { max-width: 760px; margin: 0 auto; padding: 24px 16px 60px; }
    ol.steps { list-style: none; padding: 0; margin: 0 0 20px; display: flex; gap: 6px; flex-wrap: wrap; counter-reset: s; }
    ol.steps li { counter-increment: s; flex: 1 1 120px; padding: 10px 12px; border-radius: 10px; background: var(--panel); border: 1px solid var(--line); color: var(--muted); font-size: .85rem; }
    ol.steps li::before { content: counter(s) ". "; font-weight: 700; }
    ol.steps li.on { border-color: var(--brand); color: var(--text); box-shadow: inset 0 -3px 0 var(--brand); }
    ol.steps li.done { color: var(--ok); }
    .card { background: var(--panel); border: 1px solid var(--line); border-radius: 14px; padding: 24px; display: grid; gap: 16px; }
    h1 { font-size: 1.35rem; margin: 0; } p { margin: 0; } .muted { color: var(--muted); }
    label { display: grid; gap: 6px; align-content: start; font-weight: 600; font-size: .9rem; } label em { font-weight: 400; color: var(--muted); }
    label small { font-weight: 400; color: var(--muted); }
    input[type=text], input[type=password], input[type=email], input[type=url], input:not([type]) { font: inherit; padding: .7em .85em; border-radius: 9px; border: 1px solid var(--line); background: var(--bg); color: var(--text); width: 100%; }
    input:focus { outline: 2px solid var(--brand); outline-offset: 1px; }
    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; align-items: start; } @media (max-width: 600px) { .row { grid-template-columns: 1fr; } }
    .check { display: flex; gap: 10px; align-items: flex-start; font-weight: 500; } .check input { margin-top: 4px; }
    .checks { list-style: none; padding: 0; margin: 0; display: grid; gap: 6px; }
    .checks li { display: flex; gap: 10px; padding: 8px 10px; border-radius: 8px; background: var(--ok-bg); color: var(--ok); font-size: .9rem; }
    .checks li.bad { background: var(--bad-bg); color: var(--bad); flex-direction: column; gap: 2px; }
    .checks li.bad small { color: var(--text); }
    .errors { background: var(--bad-bg); color: var(--bad); border-radius: 10px; padding: 12px 14px; display: grid; gap: 4px; }
    .actions { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    button { font: inherit; font-weight: 600; padding: .75em 1.4em; border-radius: 9px; border: 1px solid var(--brand); background: var(--brand); color: #fff; cursor: pointer; }
    button.ghost { background: transparent; color: var(--text); border-color: var(--line); }
    .seg { display: flex; gap: 10px; flex-wrap: wrap; } .seg label { flex: 1 1 200px; border: 1px solid var(--line); border-radius: 10px; padding: 12px; display: flex; gap: 10px; align-items: flex-start; font-weight: 500; }
    code { background: var(--bg); padding: .1em .35em; border-radius: 5px; }
    dl { display: grid; grid-template-columns: auto 1fr; gap: 6px 16px; margin: 0; } dt { color: var(--muted); } dd { margin: 0; font-weight: 600; word-break: break-all; }
    a { color: var(--brand); }
</style>
</head>
<body>
<header><div class="in"><img src="/images/fdh-logo.jpg" alt=""><div><b>Friends Dream Home</b><small>Website setup</small></div></div></header>
<main>
<?php if ($step === 6 && $log !== null) { ?>
    <section class="card">
        <h1>Setup complete ✓</h1>
        <ul class="checks"><?php foreach ($log as $line) { ?><li><?= $e($line) ?></li><?php } ?></ul>
        <dl>
            <dt>Website</dt><dd><a href="<?= $e($done['site']) ?>"><?= $e($done['site']) ?></a></dd>
            <dt>Admin sign-in</dt><dd><a href="<?= $e($done['admin']) ?>"><?= $e($done['admin']) ?></a></dd>
            <dt>Admin email</dt><dd><?= $e($done['email']) ?></dd>
        </dl>
        <p class="muted">Save the admin address — it is private. At the first sign-in you will set up two-factor authentication with Google Authenticator. Future updates are installed from <b>Admin → System update</b>.</p>
    </section>
<?php } else { ?>
    <ol class="steps"><?php foreach ($steps as $n => $label) { ?><li class="<?= $n === $step ? 'on' : ($n < $step ? 'done' : '') ?>"><?= $e($label) ?></li><?php } ?></ol>
    <form method="post" class="card" novalidate>
        <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
        <?php if ($errors) { ?><div class="errors" role="alert"><?php foreach ($errors as $err) { ?><span><?= $e($err) ?></span><?php } ?></div><?php } ?>

        <?php if ($step === 1) { ?>
            <h1>Server check</h1>
            <p class="muted">These must all be green before the website can be installed.</p>
            <ul class="checks">
                <?php foreach ($requirements as $c) { ?>
                    <li class="<?= $c['ok'] ? '' : 'bad' ?>"><span><?= $c['ok'] ? '✓' : '✗' ?> <?= $e($c['label']) ?></span><?php if (! $c['ok']) { ?><small><?= $e($c['help']) ?></small><?php } ?></li>
                <?php } ?>
            </ul>
            <?= $field('token', 'Setup code', ['hint' => 'Proves you own this server. Open <code>storage/app/install-token.txt</code> in File Manager and copy the code.']) ?>
        <?php } elseif ($step === 2) { ?>
            <h1>Database</h1>
            <div class="seg">
                <label><input type="radio" name="db_driver" value="mysql" <?= $in['db_driver'] === 'mysql' ? 'checked' : '' ?>><span><b>MySQL</b><br><small class="muted">Recommended. Create it in hPanel → Databases.</small></span></label>
                <label><input type="radio" name="db_driver" value="sqlite" <?= $in['db_driver'] === 'sqlite' ? 'checked' : '' ?>><span><b>SQLite file</b><br><small class="muted">No setup needed; fine for a small site.</small></span></label>
            </div>
            <div class="row"><?= $field('db_host', 'Host') ?><?= $field('db_port', 'Port') ?></div>
            <?= $field('db_name', 'Database name', ['placeholder' => 'u123456789_fdh']) ?>
            <div class="row"><?= $field('db_user', 'Database user', ['placeholder' => 'u123456789_fdh']) ?><?= $field('db_pass', 'Database password', ['type' => 'password', 'hint' => $in['db_pass'] !== '' ? 'Saved — leave empty to keep it.' : null]) ?></div>
            <p class="muted">MySQL fields are ignored when SQLite is chosen.</p>
        <?php } elseif ($step === 3) { ?>
            <h1>Website &amp; email</h1>
            <?= $field('app_url', 'Website address', ['type' => 'url', 'hint' => 'Exactly as visitors type it, e.g. https://fdh.com.bd']) ?>
            <?= $field('admin_path', 'Private admin address', ['hint' => 'The admin panel will be at <b>'.$e($in['app_url']).'/<span>'.$e($in['admin_path']).'</span>/login</b>. Keep it secret.']) ?>
            <div class="row">
                <?= $field('mail_from', 'Sending email address', ['type' => 'email', 'optional' => true, 'placeholder' => 'info@fdh.com.bd']) ?>
                <?= $field('mail_pass', 'Email password', ['type' => 'password', 'optional' => true, 'hint' => 'Hostinger email password. Leave empty to set up email later.']) ?>
            </div>
        <?php } elseif ($step === 4) { ?>
            <h1>Admin account</h1>
            <p class="muted">This first account is the Super Admin. You can add your team with their own roles later.</p>
            <div class="row"><?= $field('admin_name', 'Your name', ['auto' => 'name']) ?><?= $field('admin_email', 'Your email', ['type' => 'email', 'auto' => 'email']) ?></div>
            <div class="row">
                <?= $field('admin_password', 'Password', ['type' => 'password', 'auto' => 'new-password', 'hint' => $in['admin_password'] !== '' ? 'Saved — leave empty to keep it.' : '12+ characters: upper and lower case, a number and a symbol.']) ?>
                <?= $field('admin_password_confirmation', 'Password again', ['type' => 'password', 'auto' => 'new-password']) ?>
            </div>
            <label class="check"><input type="checkbox" name="samples" value="1" <?= $in['samples'] === '1' ? 'checked' : '' ?>><span>Add sample photos and projects so the site looks complete now. Your own uploads replace them; remove them all from the dashboard anytime.</span></label>
        <?php } elseif ($step === 5) { ?>
            <h1>Ready to install</h1>
            <dl>
                <dt>Website</dt><dd><?= $e($in['app_url']) ?></dd>
                <dt>Admin address</dt><dd><?= $e($in['app_url'].'/'.$in['admin_path'].'/login') ?></dd>
                <dt>Database</dt><dd><?= $in['db_driver'] === 'sqlite' ? 'SQLite file' : $e($in['db_user'].'@'.$in['db_host'].'/'.$in['db_name']) ?></dd>
                <dt>Email</dt><dd><?= $in['mail_pass'] === '' ? 'Set up later' : $e($in['mail_from'] ?: $in['admin_email']) ?></dd>
                <dt>Super Admin</dt><dd><?= $e($in['admin_name'].' <'.$in['admin_email'].'>') ?></dd>
                <dt>Sample content</dt><dd><?= $in['samples'] === '1' ? 'Yes' : 'No' ?></dd>
            </dl>
            <p class="muted">Installing takes under a minute. Keep this tab open.</p>
        <?php } ?>

        <div class="actions">
            <?php if ($step > 1) { ?><button type="submit" name="action" value="back" class="ghost">← Back</button><?php } else { ?><span></span><?php } ?>
            <button type="submit" name="action" value="next"><?= $step === 5 ? 'Install now' : 'Continue →' ?></button>
        </div>
    </form>
<?php } ?>
</main>
</body>
</html>
