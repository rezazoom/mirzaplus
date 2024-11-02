<?php
require_once 'functions.php';
#-----------------------------#
function token_panel(string $panel_url, string $panel_username, string $panel_password): mixed
{
    $url_get_token = rtrim($panel_url, '/') . '/api/admin/token'; // Ensure URL does not have trailing slash
    $data_token = array(
        'username' => $panel_username,
        'password' => $panel_password
    );

    // Initialize cURL options
    $curl_options = array(
        CURLOPT_RETURNTRANSFER => true,  // Return response as a string
        CURLOPT_POST => true,            // Send a POST request
        CURLOPT_TIMEOUT => 3,            // Timeout in seconds
        CURLOPT_POSTFIELDS => http_build_query($data_token), // Form-encode the data
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        )
    );

    // Initialize cURL session
    $curl_handle = curl_init($url_get_token);
    curl_setopt_array($curl_handle, $curl_options);

    // Execute cURL request and fetch response
    $response = curl_exec($curl_handle);

    // Check for cURL errors
    if (curl_errno($curl_handle)) {
        $error_message = curl_error($curl_handle);
        curl_close($curl_handle); // Always close the cURL handle
        return array('error' => $error_message);
    }

    // Check HTTP status code
    $http_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
    curl_close($curl_handle);

    // Handle non-200 responses
    if ($http_status !== 200) {
        return array('error' => 'HTTP error code: ' . $http_status);
    }

    // Decode the JSON response
    $decoded_response = json_decode($response, true);

    // Check if JSON decoding was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        return array('error' => 'Invalid JSON response');
    }

    return $decoded_response;
}


#-----------------------------#

function getuser($usernameac, $location)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location, "select");
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $url = $marzban_list_get['url_panel'] . '/api/user/' . $usernameac;
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value . $Check_token['access_token']
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}

#-----------------------------#
function ResetUserDataUsage($usernameac, $location)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location, "select");
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $url = $marzban_list_get['url_panel'] . '/api/user/' . $usernameac . '/reset';
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value . $Check_token['access_token']
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}

#-----------------------------#
function adduser($username, $expire, $data_limit, $location)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location, "select");
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $url = $marzban_list_get['url_panel'] . "/api/user";
    $nameprotocol = array();
    if (isset($marzban_list_get['vless']) && $marzban_list_get['vless'] == "onvless") {
        $nameprotocol['vless'] = array();
    }
    if (isset($marzban_list_get['vmess']) && $marzban_list_get['vmess'] == "onvmess") {
        $nameprotocol['vmess'] = array();
    }
    if (isset($marzban_list_get['trojan']) && $marzban_list_get['trojan'] == "ontrojan") {
        $nameprotocol['trojan'] = array();
    }
    if (isset($marzban_list_get['shadowsocks']) && $marzban_list_get['shadowsocks'] == "onshadowsocks") {
        $nameprotocol['shadowsocks'] = array();
    }
    if (isset($nameprotocol['vless']) && $marzban_list_get['flow'] == "flowon") {
        $nameprotocol['vless']['flow'] = 'xtls-rprx-vision';
    }
    $header_value = 'Bearer ';
    $data = array(
        "proxies" => $nameprotocol,
        "data_limit" => $data_limit,
        "username" => $username
    );
    if ($expire == "0") {
        $data['expire'] = 0;
    } else {
        if ($marzban_list_get['onholdstatus'] == "ononhold") {
            $data["expire"] = 0;
            $data["status"] = "on_hold";
            $data["on_hold_expire_duration"] = $expire - time();
        } else {
            $data['expire'] = $expire;
        }
    }
    $payload = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value . $Check_token['access_token'],
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

//----------------------------------
function Get_System_Stats($location)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location, "select");
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $url = $marzban_list_get['url_panel'] . '/api/system';
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value . $Check_token['access_token'],
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $Get_System_Stats = json_decode($output, true);
    return $Get_System_Stats;
}

//----------------------------------
function removeuser($location, $username)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location, "select");
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);

    $url = $marzban_list_get['url_panel'] . '/api/user/' . $username;
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value . $Check_token['access_token']
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}

//----------------------------------
function Modifyuser($location, $username, array $data)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location, "select");
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $url = $marzban_list_get['url_panel'] . '/api/user/' . $username;
    $payload = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Authorization: Bearer ' . $Check_token['access_token'];
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($result, true);
    return $data_useer;
}

#-----------------------------------------------#
function revoke_sub($username, $location)
{
    global $connect;
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location, "select");
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $usernameac = $username;
    $url = $marzban_list_get['url_panel'] . '/api/user/' . $usernameac . '/revoke_sub';
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value . $Check_token['access_token']
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}
