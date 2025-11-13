<?php
function getFunctionStatus($name)
{
    return function_exists($name) ? 'Aktif' : 'Dinonaktifkan';
}

function isWindows()
{
    $osFamily = defined('PHP_OS_FAMILY') ? PHP_OS_FAMILY : PHP_OS;

    return stripos($osFamily, 'Windows') === 0;
}

function escapeHostForShell($host)
{
    if (isWindows()) {
        return '"' . str_replace('"', '""', $host) . '"';
    }

    return escapeshellarg($host);
}

function runShellCommand($command, $wrapWithCmd = false)
{
    $fullCommand = $command;

    if (isWindows() && $wrapWithCmd) {
        $fullCommand = 'cmd /c ' . $command;
    }

    $errors = array();

    if (function_exists('shell_exec')) {
        $output = shell_exec($fullCommand . ' 2>&1');

        if ($output !== null) {
            return array("Perintah: {$fullCommand}\nEksekutor: shell_exec\n\n" . ltrim($output), null);
        }

        $errors[] = "shell_exec gagal mengeksekusi perintah.";
    } else {
        $errors[] = 'Fungsi shell_exec tidak tersedia.';
    }

    if (function_exists('exec')) {
        $lines = array();
        $status = 0;
        exec($fullCommand . ' 2>&1', $lines, $status);
        $output = implode("\n", $lines);

        if ($status === 0 || $output !== '') {
            return array("Perintah: {$fullCommand}\nEksekutor: exec\nStatus kode: {$status}\n\n" . $output, null);
        }

        $errors[] = 'exec mengembalikan keluaran kosong dan status non-zero.';
    } else {
        $errors[] = 'Fungsi exec tidak tersedia.';
    }

    if (function_exists('passthru')) {
        ob_start();
        $status = 0;
        passthru($fullCommand . ' 2>&1', $status);
        $output = ob_get_clean();

        if ($status === 0 || $output !== '') {
            return array("Perintah: {$fullCommand}\nEksekutor: passthru\nStatus kode: {$status}\n\n" . $output, null);
        }

        $errors[] = 'passthru tidak menghasilkan keluaran berguna.';
    } else {
        $errors[] = 'Fungsi passthru tidak tersedia.';
    }

    return array(null, 'Gagal menjalankan perintah (' . $fullCommand . '). Rincian: ' . implode(' ', $errors));
}

function resolveHostToIp($host)
{
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return array($host, null);
    }

    $resolved = gethostbyname($host);

    if ($resolved === $host) {
        return array(null, 'Tidak dapat melakukan resolusi DNS untuk host tersebut.');
    }

    return array($resolved, null);
}

function runPing($host)
{
    $arg = escapeHostForShell($host);
    $command = isWindows() ? "ping -n 4 {$arg}" : "ping -c 4 {$arg}";

    return runShellCommand($command, isWindows());
}

function runTraceroute($host)
{
    $arg = escapeHostForShell($host);
    $command = isWindows() ? "tracert -d {$arg}" : "traceroute -n {$arg}";

    return runShellCommand($command, isWindows());
}

function lookupBgpInfo($host)
{
    list($ip, $resolveError) = resolveHostToIp($host);

    if ($resolveError !== null) {
        return array(null, $resolveError);
    }

    if (!function_exists('curl_init')) {
        return array(null, 'Ekstensi cURL tidak tersedia di server.');
    }

    $userAgentHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $url = 'https://bgp.he.net/ip/' . rawurlencode($ip);
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; NetworkTools/1.0; +' . $userAgentHost . ')',
    ));

    $body = curl_exec($ch);

    if ($body === false) {
        $error = curl_error($ch);
        if ($error === '') {
            $error = 'Kesalahan tidak diketahui saat mengakses bgp.he.';
        }

        curl_close($ch);

        return array(null, 'Gagal mengambil data dari bgp.he: ' . $error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        return array(null, 'bgp.he mengembalikan status HTTP ' . $status . '.');
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();

    if (!$dom->loadHTML($body)) {
        libxml_clear_errors();

        return array(null, 'Tidak dapat mem-parsing halaman bgp.he.');
    }

    libxml_clear_errors();
    $xpath = new DOMXPath($dom);
    $fields = array(
        'Prefix' => null,
        'Origin AS' => null,
        'Origin Name' => null,
    );

    foreach ($xpath->query('//table//tr[th]') as $row) {
        $label = trim(preg_replace('/\s+/', ' ', $xpath->evaluate('string(th[1])', $row)));

        if (!array_key_exists($label, $fields)) {
            continue;
        }

        $value = trim(preg_replace('/\s+/', ' ', $xpath->evaluate('string(td[1])', $row)));

        if ($value !== '') {
            $fields[$label] = $value;
        }
    }

    $fields = array_filter($fields, function ($value) {
        return $value !== null && $value !== '';
    });

    if (empty($fields)) {
        return array(null, 'Tidak menemukan informasi ISP untuk IP tersebut di bgp.he.');
    }

    $fields = array_merge(array('IP' => $ip), $fields);

    return array($fields, null);
}

$functionStatuses = array(
    'exec' => getFunctionStatus('exec'),
    'shell_exec' => getFunctionStatus('shell_exec'),
    'passthru' => getFunctionStatus('passthru'),
);

$hostInput = isset($_POST['host']) ? trim($_POST['host']) : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$errorMessage = '';
$resultOutput = null;
$bgpInfo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($hostInput === '') {
        $errorMessage = 'Host atau IP wajib diisi.';
    } elseif (!preg_match('/^[a-zA-Z0-9\-\.:]+$/', $hostInput)) {
        $errorMessage = 'Host atau IP mengandung karakter yang tidak diperbolehkan.';
    } elseif (!in_array($action, array('ping', 'traceroute', 'isp_lookup'), true)) {
        $errorMessage = 'Aksi tidak dikenali.';
    } else {
        if ($action === 'ping') {
            list($resultOutput, $errorMessage) = runPing($hostInput);
        } elseif ($action === 'traceroute') {
            list($resultOutput, $errorMessage) = runTraceroute($hostInput);
        } else {
            list($bgpInfo, $errorMessage) = lookupBgpInfo($hostInput);
        }
    }
}

?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Alat Jaringan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f7f7f7; color: #333; }
        h1 { margin-bottom: 0.5rem; }
        form { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type="text"], select { width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 0.6rem 1.5rem; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .status { margin-bottom: 1rem; }
        .status span { display: inline-block; margin-right: 1rem; }
        .status .aktif { color: #28a745; }
        .status .nonaktif { color: #dc3545; }
        .error { background: #f8d7da; color: #842029; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .result { background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        pre { white-space: pre-wrap; word-wrap: break-word; background: #1e1e1e; color: #dcdcdc; padding: 1rem; border-radius: 6px; overflow-x: auto; }
        ul { padding-left: 1.2rem; }
    </style>
</head>
<body>
<h1>Alat Diagnostik Jaringan</h1>
<div class="status">
    <?php foreach ($functionStatuses as $name => $status): ?>
        <?php $isActive = $status === 'Aktif'; ?>
        <span class="<?php echo $isActive ? 'aktif' : 'nonaktif'; ?>">
            <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>: <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
        </span>
    <?php endforeach; ?>
</div>

<form method="post" action="">
    <label for="host">Host atau IP</label>
    <input type="text" id="host" name="host" required placeholder="contoh: google.com atau 8.8.8.8" value="<?php echo htmlspecialchars($hostInput, ENT_QUOTES, 'UTF-8'); ?>">

    <label for="action">Aksi</label>
    <select id="action" name="action" required>
        <option value="ping" <?php echo $action === 'ping' ? 'selected' : ''; ?>>Ping</option>
        <option value="traceroute" <?php echo $action === 'traceroute' ? 'selected' : ''; ?>>Traceroute</option>
        <option value="isp_lookup" <?php echo $action === 'isp_lookup' ? 'selected' : ''; ?>>Lookup ISP (bgp.he)</option>
    </select>

    <button type="submit">Jalankan</button>
</form>

<?php if ($errorMessage !== ''): ?>
    <div class="error"><?php echo nl2br(htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')); ?></div>
<?php endif; ?>

<?php if ($resultOutput !== null && $errorMessage === ''): ?>
    <div class="result">
        <h2>Hasil</h2>
        <pre><?php echo htmlspecialchars($resultOutput, ENT_QUOTES, 'UTF-8'); ?></pre>
    </div>
<?php endif; ?>

<?php if ($bgpInfo !== null && $errorMessage === ''): ?>
    <div class="result">
        <h2>Informasi ISP dari bgp.he</h2>
        <ul>
            <?php foreach ($bgpInfo as $label => $value): ?>
                <li><strong><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></strong>: <?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
</body>
</html>