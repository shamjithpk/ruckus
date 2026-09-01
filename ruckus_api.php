<?php
$allowed = ['https://wifistore.online', 'https://admin.wifistore.online'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
header('Access-Control-Allow-Origin: ' . (in_array($origin, $allowed) ? $origin : 'https://wifistore.online'));
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
header('Content-Type: application/json');
error_reporting(0);

$router_ip = "10.106.0.1";
$username = "sujeesh";
$password = "Vaiga12@";
$wlan_name = "Javaid_4G"; // As per your Unleashed config

$action   = $_POST['action']   ?? '';
$fullname = $_POST['fullname'] ?? 'AppUser';
$duration = $_POST['duration'] ?? '30';
$pass_key_in = $_POST['pass_key'] ?? '';
$phone_in = $_POST['whatsapp'] ?? $_POST['phonenumber'] ?? '';
$limitnumber = $_POST['limitnumber'] ?? $_POST['devices'] ?? '1';

$countrycode = '';
$phonenumber = '';
if (!empty($phone_in)) {
    $clean_phone = preg_replace('/[^0-9]/', '', $phone_in);
    if (str_starts_with($clean_phone, '966') && strlen($clean_phone) >= 11) {
        $countrycode = '966';
        $phonenumber = substr($clean_phone, 3);
    } elseif (str_starts_with($clean_phone, '91') && strlen($clean_phone) >= 11) {
        $countrycode = '91';
        $phonenumber = substr($clean_phone, 2);
    } else {
        if (strlen($clean_phone) > 10) {
            $countrycode = substr($clean_phone, 0, -10);
            $phonenumber = substr($clean_phone, -10);
        } else {
            $phonenumber = $clean_phone;
        }
    }
}

$allowed_actions = ['create_pass', 'get_active_clients', 'get_guest_passes', 'delete_pass'];
if (!in_array($action, $allowed_actions)) {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
    exit;
}

$cookie_file = __DIR__ . '/cookie.txt';

// STEP 1: Login
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://$router_ip/admin/login.jsp");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => $username,
    'password' => $password,
    'ok' => 'Log in'
]));

$login_response = curl_exec($ch);

// STEP 2: Get Dashboard to find CSRF token
curl_setopt($ch, CURLOPT_URL, "https://$router_ip/admin/dashboard.jsp");
curl_setopt($ch, CURLOPT_HTTPGET, true);
$dashboard_response = curl_exec($ch);

$csrf_token = "";
if (preg_match("/var csfrToken = '([^']+)';/", $dashboard_response, $matches)) {
    $csrf_token = $matches[1];
} else if (preg_match('/csfrToken\s*=\s*"([^"]+)"/', $dashboard_response, $matches)) {
    $csrf_token = $matches[1];
}

if (empty($csrf_token)) {
    echo json_encode(["status" => "error", "message" => "Failed to get CSRF token from router"]);
    exit;
}

// ============================================================
// ACTION: get_active_clients
// ============================================================
if ($action === 'get_active_clients') {
    $xml_payload = "<ajax-request action='getstat' comp='stamgr' enable-gzip='0'><client LEVEL='1' /></ajax-request>";

    curl_setopt($ch, CURLOPT_URL, "https://$router_ip/admin/_cmdstat.jsp");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-CSRF-Token: $csrf_token",
        "X-Requested-With: XMLHttpRequest",
        "Content-Type: text/xml"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (empty($response)) {
        echo json_encode(["status" => "error", "message" => "Empty response from router"]);
        exit;
    }

    // Suppress XML parse errors; strip DOCTYPE to avoid external entity issues
    $clean = preg_replace('/<!DOCTYPE[^>]*>/i', '', $response);
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($clean);
    libxml_clear_errors();

    if (!$xml) {
        echo json_encode(["status" => "error", "message" => "Failed to parse router XML"]);
        exit;
    }

    $clients = [];
    foreach ($xml->response->{'apstamgr-stat'}->client as $client) {
        $attrs = $client->attributes();
        $rssi_dbm = (int)($attrs['received-signal-strength'] ?? 0);
        $rssi_val = (int)($attrs['rssi'] ?? 0);

        // Signal quality label
        $level = (string)($attrs['rssi-level'] ?? 'unknown');
        if ($level === 'excellent')       $signal_label = '🟢 Excellent';
        elseif ($level === 'healthy')     $signal_label = '🟡 Good';
        elseif ($level === 'marginal')    $signal_label = '🟠 Weak';
        else                              $signal_label = '🔴 Poor';

        $clients[] = [
            'mac'          => (string)($attrs['mac']      ?? ''),
            'user'         => (string)($attrs['user']     ?? ''),
            'ip'           => (string)($attrs['ip']       ?? ''),
            'hostname'     => (string)($attrs['hostname'] ?? ''),
            'model'        => (string)($attrs['model']    ?? ''),
            'band'         => (string)($attrs['radio-band'] ?? ''),
            'rssi_dbm'     => $rssi_dbm,
            'rssi_val'     => $rssi_val,
            'signal_label' => $signal_label,
            'rssi_level'   => $level,
        ];
    }

    echo json_encode(["status" => "success", "count" => count($clients), "clients" => $clients]);
    exit;
}

// ============================================================
// ACTION: get_guest_passes / delete_pass
// ============================================================
if ($action === 'get_guest_passes' || $action === 'delete_pass') {
    $ts = round(microtime(true) * 1000);
    $xml_payload = "<ajax-request action='getconf' DECRYPT_X='true' updater='guest-list.$ts.1001' comp='guest-list'><guest /></ajax-request>";

    curl_setopt($ch, CURLOPT_URL, "https://$router_ip/admin/_conf.jsp");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-CSRF-Token: $csrf_token",
        "X-Requested-With: XMLHttpRequest",
        "Content-Type: text/xml"
    ]);
    $response = curl_exec($ch);

    if (empty($response)) {
        echo json_encode(["status" => "error", "message" => "Empty response from router"]);
        exit;
    }

    $clean = preg_replace('/<!DOCTYPE[^>]*>/i', '', $response);
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($clean);
    libxml_clear_errors();

    if (!$xml || !isset($xml->response->resultset)) {
        echo json_encode(["status" => "error", "message" => "Failed to parse router XML"]);
        exit;
    }

    $passes = [];
    foreach ($xml->response->resultset->guest as $g) {
        $a = $g->attributes();
        $passes[] = [
            'id'          => (string)($a['id'] ?? ''),
            'fullname'    => (string)($a['full-name'] ?? ''),
            'key'         => (string)($a['key'] ?? ''),
            'wlan'        => (string)($a['wlan'] ?? ''),
            'create_time' => (int)($a['create-time'] ?? 0),
            'expire_time' => (int)($a['expire-time'] ?? 0),
            'phone'       => (string)($a['phone-number'] ?? ''),
            'used'        => ((string)($a['used'] ?? '')) === 'true',
            'devices'     => count($g->client),
        ];
    }

    if ($action === 'get_guest_passes') {
        curl_close($ch);
        echo json_encode(["status" => "success", "count" => count($passes), "passes" => $passes]);
        exit;
    }

    // delete_pass: find the pass by key and remove it from the router
    if ($pass_key_in === '') {
        curl_close($ch);
        echo json_encode(["status" => "error", "message" => "pass_key required"]);
        exit;
    }
    $del_id = null;
    foreach ($passes as $p) {
        if (strcasecmp($p['key'], $pass_key_in) === 0) { $del_id = $p['id']; break; }
    }
    if ($del_id === null) {
        curl_close($ch);
        echo json_encode(["status" => "success", "message" => "Pass not found on router (already removed)"]);
        exit;
    }

    $ts2 = $ts + 1;
    $del_payload = "<ajax-request action='delobj' updater='guest-list.$ts2.1002' comp='guest-list'><guest id='$del_id' /></ajax-request>";
    curl_setopt($ch, CURLOPT_URL, "https://$router_ip/admin/_conf.jsp");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $del_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-CSRF-Token: $csrf_token",
        "X-Requested-With: XMLHttpRequest",
        "Content-Type: text/xml"
    ]);
    curl_exec($ch);
    curl_close($ch);

    echo json_encode(["status" => "success", "message" => "Pass deleted from router", "deleted_key" => $pass_key_in]);
    exit;
}

// ============================================================
// ACTION: create_pass
// ============================================================
// Generate a 6-digit random pass key if no custom key is provided
$pass_key = !empty($pass_key_in) ? strtoupper($pass_key_in) : strtoupper(substr(md5(uniqid()), 0, 6));

$payload = [
    'gentype'      => 'single',
    'fullname'     => $fullname,
    'remarks'      => 'Generated by WiFi Manager',
    'duration'     => $duration,
    'duration-unit'=> 'day_Days',
    'key'          => $pass_key,
    'createToNum'  => '',
    'batchpass'    => '',
    'guest-wlan'   => $wlan_name,
    'shared'       => 'true',
    'reauth'       => 'false',
    'reauth-time'  => '',
    'reauth-unit'  => 'min',
    'email'        => '',
    'countrycode'  => $countrycode,
    'phonenumber'  => $phonenumber,
    'limitnumber'  => $limitnumber,
    '_'            => ''
];

curl_setopt($ch, CURLOPT_URL, "https://$router_ip/admin/mon_createguest.jsp");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-CSRF-Token: $csrf_token",
    "X-Requested-With: XMLHttpRequest",
    "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
]);

$create_response = curl_exec($ch);
curl_close($ch);

$resp_json = json_decode($create_response, true);
if ($resp_json && isset($resp_json['result']) && $resp_json['result'] === 'DONE') {
    $router_key = $resp_json['key'] ?? $pass_key;
    echo json_encode(["status" => "success", "pass_key" => $router_key, "fullname" => $fullname]);
} else {
    echo json_encode(["status" => "error", "message" => "Router rejected request", "raw" => $create_response]);
}
?>
