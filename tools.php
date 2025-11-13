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

function canUseShell()
{
    return function_exists('shell_exec') || function_exists('exec') || function_exists('passthru');
}

function escapeHostForShell($host)
{
    if (isWindows()) {
        return '"' . str_replace('"', '""', $host) . '"';
    }

    return escapeshellarg($host);
}

function makeResponse($output = null, $error = null, $meta = array())
{
    return array(
        'output' => $output,
        'error' => $error,
        'meta' => $meta,
    );
}

function runShellCommand($command, $wrapWithCmd = false)
{
    $fullCommand = $command;

    if (isWindows() && $wrapWithCmd) {
        $fullCommand = 'cmd /c ' . $command;
    }

    if (!canUseShell()) {
        return makeResponse(null, 'Semua fungsi shell (exec, shell_exec, passthru) dinonaktifkan.', array(
            'mode' => 'shell',
            'command' => $fullCommand,
        ));
    }

    $errors = array();

    if (function_exists('shell_exec')) {
        $output = shell_exec($fullCommand . ' 2>&1');

        if ($output !== null) {
            return makeResponse("Perintah: {$fullCommand}\nEksekutor: shell_exec\n\n" . ltrim($output), null, array(
                'mode' => 'shell',
                'executor' => 'shell_exec',
                'command' => $fullCommand,
            ));
        }

        $errors[] = 'shell_exec gagal mengeksekusi perintah.';
    } else {
        $errors[] = 'Fungsi shell_exec tidak tersedia.';
    }

    if (function_exists('exec')) {
        $lines = array();
        $status = 0;
        exec($fullCommand . ' 2>&1', $lines, $status);
        $output = implode("\n", $lines);

        if ($status === 0 || $output !== '') {
            return makeResponse("Perintah: {$fullCommand}\nEksekutor: exec\nStatus kode: {$status}\n\n" . $output, null, array(
                'mode' => 'shell',
                'executor' => 'exec',
                'command' => $fullCommand,
                'status_code' => $status,
            ));
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
            return makeResponse("Perintah: {$fullCommand}\nEksekutor: passthru\nStatus kode: {$status}\n\n" . $output, null, array(
                'mode' => 'shell',
                'executor' => 'passthru',
                'command' => $fullCommand,
                'status_code' => $status,
            ));
        }

        $errors[] = 'passthru tidak menghasilkan keluaran berguna.';
    } else {
        $errors[] = 'Fungsi passthru tidak tersedia.';
    }

    return makeResponse(null, 'Gagal menjalankan perintah (' . $fullCommand . '). Rincian: ' . implode(' ', $errors), array(
        'mode' => 'shell',
        'command' => $fullCommand,
        'errors' => $errors,
    ));
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

function pingUsingSocket($host, $attempts = 4, $timeout = 3.0, $port = 80)
{
    if (!function_exists('fsockopen')) {
        return makeResponse(null, 'Fungsi fsockopen tidak tersedia untuk fallback ping.', array('mode' => 'socket'));
    }

    list($ip, $resolveError) = resolveHostToIp($host);
    if ($resolveError !== null) {
        return makeResponse(null, $resolveError, array('mode' => 'socket'));
    }

    $lines = array();
    $success = 0;

    for ($i = 1; $i <= $attempts; $i++) {
        $start = microtime(true);
        $errno = 0;
        $errstr = '';
        $connection = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        $elapsed = (microtime(true) - $start) * 1000;

        if ($connection) {
            fclose($connection);
            $success++;
            $lines[] = sprintf('Percobaan %d: Sukses (%.2f ms)', $i, $elapsed);
        } else {
            $lines[] = sprintf('Percobaan %d: Gagal (errno %d: %s)', $i, $errno, $errstr !== '' ? $errstr : 'tidak diketahui');
        }
    }

    $summary = sprintf("Ping TCP fallback ke %s:%d (%d/%d sukses)\n", $ip, $port, $success, $attempts);
    $summary .= implode("\n", $lines);

    return makeResponse($summary, null, array(
        'mode' => 'socket',
        'description' => 'Ping TCP menggunakan fsockopen',
        'attempts' => $attempts,
        'success' => $success,
    ));
}

function tracerouteUsingSocket($host, $maxHops = 20, $timeout = 3)
{
    if (!function_exists('socket_create')) {
        return makeResponse(null, 'Ekstensi sockets tidak tersedia untuk fallback traceroute.', array('mode' => 'socket'));
    }

    list($ip, $resolveError) = resolveHostToIp($host);
    if ($resolveError !== null) {
        return makeResponse(null, $resolveError, array('mode' => 'socket'));
    }

    $udpSocket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    $recvSocket = @socket_create(AF_INET, SOCK_RAW, getprotobyname('icmp'));

    if ($udpSocket === false || $recvSocket === false) {
        $errorCode = socket_last_error();
        $message = $errorCode ? socket_strerror($errorCode) : 'Tidak diketahui';
        if (is_resource($udpSocket)) {
            socket_close($udpSocket);
        }
        if (is_resource($recvSocket)) {
            socket_close($recvSocket);
        }

        return makeResponse(null, 'Gagal membuat socket untuk traceroute fallback: ' . $message, array('mode' => 'socket'));
    }

    socket_set_option($recvSocket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));

    $port = 33434;
    $lines = array();

    for ($ttl = 1; $ttl <= $maxHops; $ttl++) {
        socket_set_option($udpSocket, SOL_IP, IP_TTL, $ttl);
        $message = "PHPTR";
        $start = microtime(true);
        @socket_sendto($udpSocket, $message, strlen($message), 0, $ip, $port + $ttl);

        $addr = '';
        $portOut = 0;
        $result = @socket_recvfrom($recvSocket, $buffer, 512, 0, $addr, $portOut);
        $elapsed = (microtime(true) - $start) * 1000;

        if ($result === false) {
            $lines[] = sprintf('%2d  *', $ttl);
        } else {
            $hostname = @gethostbyaddr($addr);
            if ($hostname && $hostname !== $addr) {
                $lines[] = sprintf('%2d  %s (%s)  %.2f ms', $ttl, $hostname, $addr, $elapsed);
            } else {
                $lines[] = sprintf('%2d  %s  %.2f ms', $ttl, $addr, $elapsed);
            }

            if ($addr === $ip) {
                break;
            }
        }
    }

    socket_close($udpSocket);
    socket_close($recvSocket);

    if (empty($lines)) {
        return makeResponse(null, 'Traceroute fallback tidak mendapatkan respons.', array('mode' => 'socket'));
    }

    $output = "Traceroute fallback (UDP/ICMP)\n" . implode("\n", $lines);

    return makeResponse($output, null, array(
        'mode' => 'socket',
        'description' => 'Traceroute menggunakan socket UDP/ICMP',
    ));
}

function httpRequest($url, $timeout = 15)
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; NetworkTools/1.1)',
        ));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return array(null, ($error !== '' ? $error : 'Kesalahan tidak diketahui ketika mengakses ' . $url));
        }
        curl_close($ch);

        if ($status >= 400) {
            return array(null, 'HTTP ' . $status . ' dari ' . $url);
        }

        return array($body, null);
    }

    $context = stream_context_create(array(
        'http' => array(
            'timeout' => $timeout,
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0 (compatible; NetworkTools/1.1)\r\n",
        ),
        'ssl' => array(
            'verify_peer' => true,
            'verify_peer_name' => true,
        ),
    ));

    $body = @file_get_contents($url, false, $context);

    if ($body === false) {
        return array(null, 'Gagal mengambil data dari ' . $url . ' (file_get_contents).');
    }

    if (isset($http_response_header[0])) {
        if (preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches) && (int) $matches[1] >= 400) {
            return array(null, 'HTTP ' . $matches[1] . ' dari ' . $url);
        }
    }

    return array($body, null);
}

function tracerouteViaApi($host)
{
    $url = 'https://api.hackertarget.com/traceroute/?q=' . rawurlencode($host);
    list($body, $error) = httpRequest($url, 20);

    if ($error !== null) {
        return makeResponse(null, 'Traceroute API: ' . $error, array(
            'mode' => 'api',
            'source' => 'api.hackertarget.com',
        ));
    }

    if (stripos($body, 'error input invalid') !== false) {
        return makeResponse(null, 'Traceroute API: host tidak valid atau diblokir.', array(
            'mode' => 'api',
            'source' => 'api.hackertarget.com',
        ));
    }

    $output = "Traceroute via api.hackertarget.com\n" . trim($body);

    return makeResponse($output, null, array(
        'mode' => 'api',
        'source' => 'api.hackertarget.com',
        'description' => 'Traceroute dari layanan publik api.hackertarget.com',
    ));
}

function lookupIspViaApi($ip)
{
    $url = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,message,query,org,isp,as';
    list($body, $error) = httpRequest($url, 10);

    if ($error !== null) {
        return makeResponse(null, 'ip-api.com: ' . $error, array(
            'mode' => 'ip-api.com',
            'ip' => $ip,
        ));
    }

    $data = json_decode($body, true);
    if (!is_array($data)) {
        return makeResponse(null, 'ip-api.com: respons tidak valid.', array(
            'mode' => 'ip-api.com',
            'ip' => $ip,
        ));
    }

    $statusValue = isset($data['status']) ? $data['status'] : '';
    if ($statusValue !== 'success') {
        $message = isset($data['message']) ? $data['message'] : 'permintaan gagal.';
        return makeResponse(null, 'ip-api.com: ' . $message, array(
            'mode' => 'ip-api.com',
            'ip' => $ip,
        ));
    }

    $fields = array(
        'IP' => isset($data['query']) ? $data['query'] : $ip,
        'ISP' => isset($data['isp']) ? $data['isp'] : '',
        'Organisasi' => isset($data['org']) ? $data['org'] : '',
        'AS' => isset($data['as']) ? $data['as'] : '',
    );

    $fields = array_filter($fields, function ($value) {
        return $value !== null && $value !== '';
    });

    if (empty($fields)) {
        return makeResponse(null, 'ip-api.com tidak memberikan data yang relevan.', array(
            'mode' => 'ip-api.com',
            'ip' => $ip,
        ));
    }

    return makeResponse($fields, null, array(
        'mode' => 'ip-api.com',
        'ip' => $ip,
        'description' => 'Data ISP dari ip-api.com',
    ));
}

function fetchBgpHeInfo($ip)
{
    list($body, $error) = httpRequest('https://bgp.he.net/ip/' . rawurlencode($ip), 15);

    if ($error !== null) {
        return makeResponse(null, 'bgp.he: ' . $error, array(
            'mode' => 'bgp.he',
            'ip' => $ip,
        ));
    }

    if (!class_exists('DOMDocument')) {
        return makeResponse(null, 'bgp.he: Ekstensi DOM tidak tersedia untuk parsing HTML.', array(
            'mode' => 'bgp.he',
            'ip' => $ip,
        ));
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();

    if (!$dom->loadHTML($body)) {
        libxml_clear_errors();

        return makeResponse(null, 'bgp.he: Tidak dapat mem-parsing halaman.', array(
            'mode' => 'bgp.he',
            'ip' => $ip,
        ));
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
        return makeResponse(null, 'bgp.he tidak menemukan data untuk IP tersebut.', array(
            'mode' => 'bgp.he',
            'ip' => $ip,
        ));
    }

    $fields = array_merge(array('IP' => $ip), $fields);

    return makeResponse($fields, null, array(
        'mode' => 'bgp.he',
        'ip' => $ip,
        'description' => 'Data ISP dari bgp.he',
    ));
}

function runPing($host)
{
    $arg = escapeHostForShell($host);
    $command = isWindows() ? "ping -n 4 {$arg}" : "ping -c 4 {$arg}";

    $lastError = null;

    if (canUseShell()) {
        $shellResult = runShellCommand($command, isWindows());
        if ($shellResult['error'] === null) {
            return $shellResult;
        }
        $lastError = $shellResult['error'];
    }

    $fallback = pingUsingSocket($host);
    if ($fallback['error'] === null) {
        if ($lastError !== null) {
            $fallback['meta']['notice'] = $lastError;
        } elseif (!canUseShell()) {
            $fallback['meta']['notice'] = 'Fungsi shell dinonaktifkan, menggunakan ping berbasis socket (TCP).';
        }

        return $fallback;
    }

    $error = $lastError !== null ? $lastError . ' ' . $fallback['error'] : $fallback['error'];

    return makeResponse(null, $error, array('mode' => 'unavailable'));
}

function runTraceroute($host)
{
    $arg = escapeHostForShell($host);
    $command = isWindows() ? "tracert -d {$arg}" : "traceroute -n {$arg}";

    $lastError = null;

    if (canUseShell()) {
        $shellResult = runShellCommand($command, isWindows());
        if ($shellResult['error'] === null) {
            return $shellResult;
        }
        $lastError = $shellResult['error'];
    }

    $socketResult = tracerouteUsingSocket($host);
    if ($socketResult['error'] === null) {
        if ($lastError !== null) {
            $socketResult['meta']['notice'] = $lastError;
        } elseif (!canUseShell()) {
            $socketResult['meta']['notice'] = 'Fungsi shell dinonaktifkan, menggunakan traceroute berbasis socket.';
        }

        return $socketResult;
    }

    $apiResult = tracerouteViaApi($host);
    if ($apiResult['error'] === null) {
        $notices = array();
        if ($lastError !== null) {
            $notices[] = $lastError;
        }
        if ($socketResult['error'] !== null) {
            $notices[] = $socketResult['error'];
        }
        if (!empty($notices)) {
            $apiResult['meta']['notice'] = implode(' ', $notices);
        }
        if (!canUseShell()) {
            $apiResult['meta']['notice'] = (isset($apiResult['meta']['notice']) ? $apiResult['meta']['notice'] . ' ' : '') . 'Fungsi shell dinonaktifkan, menggunakan layanan traceroute API.';
        }

        return $apiResult;
    }

    $errors = array();
    if ($lastError !== null) {
        $errors[] = $lastError;
    }
    if ($socketResult['error'] !== null) {
        $errors[] = $socketResult['error'];
    }
    if ($apiResult['error'] !== null) {
        $errors[] = $apiResult['error'];
    }

    return makeResponse(null, implode(' ', $errors), array('mode' => 'unavailable'));
}

function lookupBgpInfo($host)
{
    list($ip, $resolveError) = resolveHostToIp($host);

    if ($resolveError !== null) {
        return makeResponse(null, $resolveError, array('mode' => 'unavailable'));
    }

    $bgpResult = fetchBgpHeInfo($ip);
    if ($bgpResult['error'] === null) {
        return $bgpResult;
    }

    $apiResult = lookupIspViaApi($ip);
    if ($apiResult['error'] === null) {
        $notice = $bgpResult['error'];
        if ($notice !== null && $notice !== '') {
            $apiResult['meta']['notice'] = $notice;
        }

        return $apiResult;
    }

    $errors = array($bgpResult['error']);
    if ($apiResult['error'] !== null) {
        $errors[] = $apiResult['error'];
    }

    return makeResponse(null, implode(' ', $errors), array('mode' => 'unavailable', 'ip' => $ip));
}

$functionStatuses = array(
    'exec' => getFunctionStatus('exec'),
    'shell_exec' => getFunctionStatus('shell_exec'),
    'passthru' => getFunctionStatus('passthru'),
);

$shellAvailable = canUseShell();

$hostInput = isset($_POST['host']) ? trim($_POST['host']) : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$errorMessage = '';
$resultOutput = null;
$resultMeta = null;
$bgpInfo = null;
$bgpMeta = null;
$infoNotices = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($hostInput === '') {
        $errorMessage = 'Host atau IP wajib diisi.';
    } elseif (!preg_match('/^[a-zA-Z0-9\-\.:]+$/', $hostInput)) {
        $errorMessage = 'Host atau IP mengandung karakter yang tidak diperbolehkan.';
    } elseif (!in_array($action, array('ping', 'traceroute', 'isp_lookup'), true)) {
        $errorMessage = 'Aksi tidak dikenali.';
    } else {
        if ($action === 'ping') {
            $response = runPing($hostInput);
            if ($response['error'] === null) {
                $resultOutput = $response['output'];
                $resultMeta = $response['meta'];
            } else {
                $errorMessage = $response['error'];
                $resultMeta = $response['meta'];
            }
        } elseif ($action === 'traceroute') {
            $response = runTraceroute($hostInput);
            if ($response['error'] === null) {
                $resultOutput = $response['output'];
                $resultMeta = $response['meta'];
            } else {
                $errorMessage = $response['error'];
                $resultMeta = $response['meta'];
            }
        } else {
            $response = lookupBgpInfo($hostInput);
            if ($response['error'] === null) {
                $bgpInfo = $response['output'];
                $bgpMeta = $response['meta'];
            } else {
                $errorMessage = $response['error'];
                $bgpMeta = $response['meta'];
            }
        }

        if ($resultMeta !== null && isset($resultMeta['notice']) && $resultMeta['notice'] !== '') {
            $infoNotices[] = $resultMeta['notice'];
        }

        if ($bgpMeta !== null && isset($bgpMeta['notice']) && $bgpMeta['notice'] !== '') {
            $infoNotices[] = $bgpMeta['notice'];
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
        .warning { background: #fff3cd; color: #856404; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #ffeeba; }
        .info { background: #d1ecf1; color: #0c5460; padding: 0.8rem 1rem; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #bee5eb; }
        .error { background: #f8d7da; color: #842029; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .result { background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .meta { font-size: 0.9rem; color: #555; margin-bottom: 1rem; }
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

<?php if (!$shellAvailable): ?>
    <div class="warning">Semua fungsi shell dinonaktifkan. Sistem akan menggunakan fallback socket/API untuk ping dan traceroute.</div>
<?php endif; ?>

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

<?php if (!empty($infoNotices)): ?>
    <?php foreach ($infoNotices as $notice): ?>
        <div class="info"><?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if ($errorMessage !== ''): ?>
    <div class="error"><?php echo nl2br(htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')); ?></div>
<?php endif; ?>

<?php if ($resultMeta !== null && $resultMeta !== array() && $errorMessage === ''): ?>
    <div class="meta">
        Sumber data: <?php echo htmlspecialchars(isset($resultMeta['description']) ? $resultMeta['description'] : (isset($resultMeta['mode']) ? ucfirst($resultMeta['mode']) : 'Tidak diketahui'), ENT_QUOTES, 'UTF-8'); ?>
        <?php if (isset($resultMeta['source'])): ?> (<?php echo htmlspecialchars($resultMeta['source'], ENT_QUOTES, 'UTF-8'); ?>)<?php endif; ?>
        <?php if (isset($resultMeta['executor'])): ?> (executor: <?php echo htmlspecialchars($resultMeta['executor'], ENT_QUOTES, 'UTF-8'); ?>)<?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($resultOutput !== null && $errorMessage === ''): ?>
    <div class="result">
        <h2>Hasil</h2>
        <pre><?php echo htmlspecialchars($resultOutput, ENT_QUOTES, 'UTF-8'); ?></pre>
    </div>
<?php endif; ?>

<?php if ($bgpMeta !== null && $bgpMeta !== array() && $errorMessage === ''): ?>
    <div class="meta">
        Sumber data: <?php echo htmlspecialchars(isset($bgpMeta['description']) ? $bgpMeta['description'] : (isset($bgpMeta['mode']) ? $bgpMeta['mode'] : 'Tidak diketahui'), ENT_QUOTES, 'UTF-8'); ?>
        <?php if (isset($bgpMeta['ip'])): ?> (IP: <?php echo htmlspecialchars($bgpMeta['ip'], ENT_QUOTES, 'UTF-8'); ?>)<?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($bgpInfo !== null && $errorMessage === ''): ?>
    <div class="result">
        <h2>Informasi ISP</h2>
        <ul>
            <?php foreach ($bgpInfo as $label => $value): ?>
                <li><strong><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></strong>: <?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
</body>
</html>