<?php
// Vtiger API credentials
$vtiger_url  = "https://192.168.68.150/hnccrm/webservice.php";
$vtiger_user = "admin";               // Vtiger username
$vtiger_key  = "Ma9xwXfDgYfmt7G";     // From My Preferences (Access Key)

// Get data from VICIdial (or manual test in browser)
$first  = $_GET['first'];
$lastname   = $_GET['last'] ;
$phone  = $_GET['phone'];
$status = $_GET['status'];
$email = $_GET['email'];
$city = $_GET['city'];
$country = $_GET['country'];
$state = $_GET['state'];
$company = $_GET['company'];


// ?? cURL helper
function vici_curl($url, $post = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if (!empty($post)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Ignore SSL issues (self-signed cert)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        die("cURL error: " . curl_error($ch));
    }
    curl_close($ch);
    return json_decode($resp, true);
}

// 1. Get challenge
$challenge = vici_curl($vtiger_url."?operation=getchallenge&username=".$vtiger_user);
if (!$challenge || !$challenge['success']) {
    die("Challenge failed: " . json_encode($challenge));
}
$token = $challenge['result']['token'];

// 2. Login
$generatedKey = md5($token.$vtiger_key);
$login = vici_curl($vtiger_url, [
    'operation' => 'login',
    'username'  => $vtiger_user,
    'accessKey' => $generatedKey
]);
if (!$login || !$login['success']) {
    die("Login failed: " . json_encode($login));
}
$sessionName = $login['result']['sessionName'];

// 3. Prepare lead data
$lead = [
   'firstname'  => $first,
   'lastname'   => $lastname,
   'phone'     => $phone,
   'leadstatus' => $status,
'email' => $email,
'city' => $city,
'country' => $country,
'state' => $state,
'company' => $company,
   'assigned_user_id' => '19x1' // replace with valid user id
];

// 4. Create lead
$post = [
   'operation'   => 'create',
   'sessionName' => $sessionName,
   'elementType' => 'Leads',
   'element'     => json_encode($lead)
];
$response = vici_curl($vtiger_url, $post);

// 5. Show result
header('Content-Type: application/json');
echo json_encode($response);
