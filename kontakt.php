<?php
/**
 * Kontaktformular-Handler – Tschudnow Profi Service
 *
 * GET  kontakt.php?token=1  -> {"token": "..."} (zeitgebundenes, signiertes Formular-Token)
 * POST kontakt.php          -> prüft die Anfrage und leitet sie per E-Mail weiter
 *
 * Konfiguration (optional) liegt AUSSERHALB des Webroots:
 *   ../kontakt-config.php  (gibt ein Array zurück, siehe $defaults unten)
 * Laufzeitdaten (Schlüssel, Rate-Limit) liegen ebenfalls außerhalb des Webroots:
 *   ../kontakt-data/
 */

declare(strict_types=1);

header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

$defaults = [
    'to'         => 'eugen.tschudnow@outlook.de',
    'from'       => 'info@tschudnow-handwerk.de',
    'from_name'  => 'Website Tschudnow Profi Service',
    // 'mail' = PHP mail(); 'smtp' = Versand über das Postfach (empfohlen, SPF/DKIM-konform)
    'transport'  => 'mail',
    'smtp_host'  => 'mxe992.netcup.net',
    'smtp_port'  => 465,          // implizites TLS
    'smtp_user'  => '',
    'smtp_pass'  => '',
    'allowed_hosts' => ['tschudnow-handwerk.de', 'www.tschudnow-handwerk.de', 'hosting254728.ae990.netcup.net'],
    'min_seconds'   => 4,         // Mindestzeit zwischen Laden des Formulars und Absenden
    'max_seconds'   => 4 * 3600,  // Token-Gültigkeit
    'limit_ip_hour' => 5,         // Anfragen pro IP und Stunde
    'limit_day'     => 60,        // Anfragen insgesamt pro Tag
];

$configFile = dirname(__DIR__) . '/kontakt-config.php';
$config = $defaults;
if (is_file($configFile)) {
    $custom = include $configFile;
    if (is_array($custom)) {
        $config = array_merge($defaults, $custom);
    }
}

$dataDir = dirname(__DIR__) . '/kontakt-data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0700, true);
}
if (!is_dir($dataDir) || !is_writable($dataDir)) {
    // Fallback: privates Temp-Verzeichnis (nicht im Webroot)
    $dataDir = rtrim(sys_get_temp_dir(), '/') . '/kontakt-' . md5(__DIR__);
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0700, true);
    }
}

function secret_key(string $dataDir): string
{
    $file = $dataDir . '/secret.key';
    if (is_file($file)) {
        $key = trim((string) file_get_contents($file));
        if (strlen($key) >= 64) {
            return $key;
        }
    }
    $key = bin2hex(random_bytes(32));
    file_put_contents($file, $key, LOCK_EX);
    @chmod($file, 0600);
    return $key;
}

function wants_json(): bool
{
    return stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
}

function respond(int $code, string $status, string $message, array $extra = []): void
{
    if (wants_json()) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => $status, 'message' => $message] + $extra, JSON_UNESCAPED_UNICODE);
    } else {
        // Ohne JavaScript: zurück zur Seite mit Statusanzeige
        header('Location: index.html?anfrage=' . ($status === 'ok' ? 'ok' : 'fehler') . '#kontakt', true, 303);
    }
    exit;
}

function single_line(string $value, int $max): string
{
    $value = trim($value);
    if (preg_match('/[\r\n\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
        return "\0invalid";
    }
    if (mb_strlen($value, 'UTF-8') > $max) {
        return "\0invalid";
    }
    return $value;
}

function encode_header(string $value): string
{
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

function rate_limit_ok(string $dataDir, string $secret, array $config): bool
{
    $file = $dataDir . '/ratelimit.json';
    $fp = fopen($file, 'c+');
    if ($fp === false) {
        return true; // im Zweifel nicht blockieren
    }
    flock($fp, LOCK_EX);
    $raw = stream_get_contents($fp);
    $data = json_decode($raw ?: '[]', true);
    if (!is_array($data)) {
        $data = [];
    }
    $now = time();
    $ipHash = hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? '', $secret);
    $day = date('Y-m-d');
    $data['ip'] = array_filter($data['ip'] ?? [], fn ($list) => is_array($list) && $list !== []);
    foreach ($data['ip'] as $k => $list) {
        $data['ip'][$k] = array_values(array_filter($list, fn ($t) => $t > $now - 3600));
        if ($data['ip'][$k] === []) {
            unset($data['ip'][$k]);
        }
    }
    if (($data['day'] ?? '') !== $day) {
        $data['day'] = $day;
        $data['count'] = 0;
    }
    $ok = count($data['ip'][$ipHash] ?? []) < $config['limit_ip_hour'] && ($data['count'] ?? 0) < $config['limit_day'];
    if ($ok) {
        $data['ip'][$ipHash][] = $now;
        $data['count'] = ($data['count'] ?? 0) + 1;
    }
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    @chmod($file, 0600);
    return $ok;
}

function smtp_send(array $config, string $to, string $data): bool
{
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $config['smtp_host']]]);
    $fp = @stream_socket_client('ssl://' . $config['smtp_host'] . ':' . (int) $config['smtp_port'], $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        error_log('kontakt.php: SMTP-Verbindung fehlgeschlagen: ' . $errstr);
        return false;
    }
    stream_set_timeout($fp, 15);
    $read = function () use ($fp): string {
        $resp = '';
        while (($line = fgets($fp, 515)) !== false) {
            $resp .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $resp;
    };
    $cmd = function (string $c, array $okCodes) use ($fp, $read): bool {
        if ($c !== '') {
            fwrite($fp, $c . "\r\n");
        }
        $resp = $read();
        $ok = in_array((int) substr($resp, 0, 3), $okCodes, true);
        if (!$ok) {
            error_log('kontakt.php: SMTP-Fehler bei "' . strtok($c, ' ') . '": ' . trim($resp));
        }
        return $ok;
    };
    $from = $config['from'];
    $ok = $cmd('', [220])
        && $cmd('EHLO tschudnow-handwerk.de', [250])
        && $cmd('AUTH LOGIN', [334])
        && $cmd(base64_encode($config['smtp_user']), [334])
        && $cmd(base64_encode($config['smtp_pass']), [235])
        && $cmd('MAIL FROM:<' . $from . '>', [250])
        && $cmd('RCPT TO:<' . $to . '>', [250, 251])
        && $cmd('DATA', [354])
        // Dot-Stuffing nach RFC 5321
        && $cmd(preg_replace('/^\./m', '..', $data) . "\r\n.", [250]);
    @fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return $ok;
}

$secret = secret_key($dataDir);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Token für das Formular ausgeben
if ($method === 'GET' && isset($_GET['token'])) {
    $ts = (string) time();
    $nonce = bin2hex(random_bytes(8));
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['token' => $ts . '.' . $nonce . '.' . hash_hmac('sha256', $ts . '.' . $nonce, $secret)]);
    exit;
}

if ($method !== 'POST') {
    header('Allow: POST');
    respond(405, 'error', 'Methode nicht erlaubt.');
}

// Herkunft prüfen (Origin bzw. Referer muss die eigene Website sein)
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$originHost = strtolower((string) parse_url($origin, PHP_URL_HOST));
if ($originHost === '' || !in_array($originHost, $config['allowed_hosts'], true)) {
    respond(403, 'error', 'Die Anfrage konnte nicht zugeordnet werden. Bitte senden Sie das Formular direkt über unsere Website ab.');
}

// Honeypot: für Menschen unsichtbares Feld muss leer bleiben -> Bots erhalten eine scheinbare Erfolgsmeldung
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    respond(200, 'ok', 'Vielen Dank für Ihre Anfrage.');
}

// Zeit-Token prüfen
$token = (string) ($_POST['token'] ?? '');
$parts = explode('.', $token);
$tokenOk = false;
if (count($parts) === 3 && ctype_digit($parts[0])) {
    $age = time() - (int) $parts[0];
    $tokenOk = hash_equals(hash_hmac('sha256', $parts[0] . '.' . $parts[1], $secret), $parts[2])
        && $age >= $config['min_seconds'] && $age <= $config['max_seconds'];
}
if (!$tokenOk) {
    respond(400, 'error', 'Das Formular ist abgelaufen oder wurde zu schnell abgeschickt. Bitte laden Sie die Seite neu und versuchen Sie es noch einmal.');
}

// Felder prüfen
$topics = [
    'entruempelung'       => 'Entrümpelung',
    'haushaltsaufloesung' => 'Haushaltsauflösung',
    'bodenleger'          => 'Bodenlegerarbeiten',
    'sonstiges'           => 'Sonstiges',
];
$errors = [];
$name    = single_line((string) ($_POST['name'] ?? ''), 100);
$email   = single_line((string) ($_POST['email'] ?? ''), 254);
$telefon = single_line((string) ($_POST['telefon'] ?? ''), 40);
$ort     = single_line((string) ($_POST['ort'] ?? ''), 100);
$termin  = single_line((string) ($_POST['termin'] ?? ''), 100);
$anliegen = (string) ($_POST['anliegen'] ?? '');
$nachricht = trim(str_replace(["\r\n", "\r"], "\n", (string) ($_POST['nachricht'] ?? '')));

if ($name === '' || $name === "\0invalid" || mb_strlen($name, 'UTF-8') < 2) $errors[] = 'name';
if ($email === "\0invalid" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email';
if ($telefon === "\0invalid" || ($telefon !== '' && !preg_match('/^[0-9+()\/\s-]{6,40}$/', $telefon))) $errors[] = 'telefon';
if ($ort === '' || $ort === "\0invalid") $errors[] = 'ort';
if ($termin === "\0invalid") $errors[] = 'termin';
if (!isset($topics[$anliegen])) $errors[] = 'anliegen';
$len = mb_strlen($nachricht, 'UTF-8');
if ($len < 10 || $len > 5000 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $nachricht)) $errors[] = 'nachricht';
if (($_POST['datenschutz'] ?? '') !== 'on') $errors[] = 'datenschutz';
if (!mb_check_encoding(implode('', [$name, $email, $telefon, $ort, $termin, $nachricht]), 'UTF-8')) $errors[] = 'encoding';

if ($errors) {
    respond(422, 'error', 'Bitte prüfen Sie Ihre Angaben.', ['fields' => array_values(array_unique($errors))]);
}

if (!rate_limit_ok($dataDir, $secret, $config)) {
    respond(429, 'error', 'Es wurden in kurzer Zeit zu viele Anfragen gesendet. Bitte versuchen Sie es später erneut oder rufen Sie uns an: 0176 312 245 60.');
}

// E-Mail zusammenstellen
$topic = $topics[$anliegen];
$subject = 'Anfrage über die Website: ' . $topic . ' in ' . $ort;
$body = implode("\n", [
    'Neue Anfrage über das Kontaktformular auf tschudnow-handwerk.de',
    '',
    'Anliegen:     ' . $topic,
    'Name:         ' . $name,
    'E-Mail:       ' . $email,
    'Telefon:      ' . ($telefon !== '' ? $telefon : '–'),
    'Ort / PLZ:    ' . $ort,
    'Wunschtermin: ' . ($termin !== '' ? $termin : '–'),
    '',
    'Nachricht:',
    $nachricht,
    '',
    '--',
    'Gesendet am ' . date('d.m.Y \u\m H:i') . ' Uhr. Datenschutzhinweis bestätigt.',
    'Antworten Sie einfach auf diese E-Mail, um ' . $name . ' zu erreichen.',
]);

$to = $config['to'];
$fromHeader = encode_header($config['from_name']) . ' <' . $config['from'] . '>';
$replyName = encode_header($name);
$messageId = '<' . bin2hex(random_bytes(12)) . '@tschudnow-handwerk.de>';
$headers = [
    'From: ' . $fromHeader,
    'Reply-To: ' . $replyName . ' <' . $email . '>',
    'Date: ' . date('r'),
    'Message-ID: ' . $messageId,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: base64',
    'X-Mailer: tschudnow-kontakt',
];
$encodedBody = rtrim(chunk_split(base64_encode(str_replace("\n", "\r\n", $body)), 76, "\r\n"));

if ($config['transport'] === 'smtp' && $config['smtp_user'] !== '' && $config['smtp_pass'] !== '') {
    $data = 'To: <' . $to . ">\r\n" . 'Subject: ' . encode_header($subject) . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $encodedBody;
    $sent = smtp_send($config, $to, $data);
} else {
    $sent = mail($to, encode_header($subject), $encodedBody, implode("\r\n", $headers), '-f' . $config['from']);
}

if (!$sent) {
    error_log('kontakt.php: Versand fehlgeschlagen');
    respond(500, 'error', 'Die Anfrage konnte leider nicht gesendet werden. Bitte schreiben Sie uns direkt an Eugen.Tschudnow@outlook.de oder rufen Sie an: 0176 312 245 60.');
}

respond(200, 'ok', 'Vielen Dank für Ihre Anfrage! Wir melden uns schnellstmöglich bei Ihnen.');
