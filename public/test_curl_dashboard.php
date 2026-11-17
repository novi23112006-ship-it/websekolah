<?php
// login_test2.php
session_start();

// Get the CSRF token from the login page
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/websekolah3/public/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
$response = curl_exec($ch);

// extract PHPSESSID
preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
$cookies = array();
foreach($matches[1] as $item) {
    parse_str($item, $cookie);
    $cookies = array_merge($cookies, $cookie);
}
$phpsessid = isset($cookies['PHPSESSID']) ? $cookies['PHPSESSID'] : '';

// extract csrf_token
preg_match('/name="csrf_token" value="(.*?)"/', $response, $csrf_match);
$csrf_token = isset($csrf_match[1]) ? $csrf_match[1] : '';

// POST to login
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, "http://localhost/websekolah3/public/auth/process");
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch2, CURLOPT_HEADER, 1);
curl_setopt($ch2, CURLOPT_POST, 1);
curl_setopt($ch2, CURLOPT_POSTFIELDS, "username=siswa&password=siswa&csrf_token=" . $csrf_token);
curl_setopt($ch2, CURLOPT_COOKIE, "PHPSESSID=" . $phpsessid);
$response2 = curl_exec($ch2);

// get new session id
preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response2, $matches2);
$cookies2 = array();
foreach($matches2[1] as $item) {
    parse_str($item, $cookie);
    $cookies2 = array_merge($cookies2, $cookie);
}
if(isset($cookies2['PHPSESSID'])) {
    $phpsessid = $cookies2['PHPSESSID'];
}

// GET dashboard
$ch3 = curl_init();
curl_setopt($ch3, CURLOPT_URL, "http://localhost/websekolah3/public/siswa");
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch3, CURLOPT_HEADER, 1);
curl_setopt($ch3, CURLOPT_COOKIE, "PHPSESSID=" . $phpsessid);
$response3 = curl_exec($ch3);

echo "RESPONSE 3 HEADER:\n";
echo substr($response3, 0, strpos($response3, "\r\n\r\n"));
echo "\n\nBODY SNIPPET:\n";
echo substr($response3, strpos($response3, "\r\n\r\n") + 4, 500);
?>
