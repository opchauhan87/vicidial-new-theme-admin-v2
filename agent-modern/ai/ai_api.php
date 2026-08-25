<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/ai_config.php';
require_once __DIR__ . '/ai_context.php';

$response = array(
    'success'  => false,
    'phase'    => '2A.4',
    'enabled'  => AGENT_AI_ENABLED,
    'provider' => 'ollama'
);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['error'] = 'POST required';
    echo json_encode($response);
    exit;
}

if (!AGENT_AI_ENABLED) {
    http_response_code(503);
    $response['error'] = 'Agent AI is disabled';
    echo json_encode($response);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    $input = array();
}

$context = agent_ai_get_context($input);

$user_message = '';

if (is_array($input) && isset($input['message'])) {
    $user_message = trim((string)$input['message']);
}

if ($user_message === '' && isset($_POST['message'])) {
    $user_message = trim((string)$_POST['message']);
}

if ($user_message === '') {
    http_response_code(400);
    $response['error'] = 'message required';
    echo json_encode($response);
    exit;
}

if (strlen($user_message) > 2000) {
    http_response_code(400);
    $response['error'] = 'message too long';
    echo json_encode($response);
    exit;
}

$system_prompt =
    'You are a read-only AI assistant for a VICIdial call-center agent. ' .
    'Give concise and practical assistance. ' .
    'You cannot dial, hang up, disposition, modify leads, or change VICIdial data. ' .
    'Never claim that you performed an action in VICIdial.';

$user_prompt =
    'Agent: ' . ($context['agent'] ?? '') . "\n" .
    'Campaign: ' . ($context['campaign'] ?? '') . "\n" .
    'Lead ID: ' . ($context['lead_id'] ?? '') . "\n" .
    'Phone: ' . ($context['phone_number'] ?? '') . "\n\n" .
    'Agent question: ' . $user_message;

$payload = array(
    'model' => 'llama3.2:3b',
    'messages' => array(
        array(
            'role' => 'system',
            'content' => $system_prompt
        ),
        array(
            'role' => 'user',
            'content' => $user_prompt
        )
    ),
    'stream' => false,
    'think' => false,
    'options' => array(
        'temperature' => 0.2,
        'num_predict' => 120
    )
);

$ch = curl_init('http://127.0.0.1:11434/api/chat');

curl_setopt_array($ch, array(
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_TIMEOUT => AGENT_AI_TIMEOUT
));

$result = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($result === false || $curl_error !== '') {
    http_response_code(502);
    $response['error'] = 'Ollama connection failed';

    if (AGENT_AI_DEBUG) {
        $response['details'] = $curl_error;
    }

    echo json_encode($response);
    exit;
}

if ($http_code < 200 || $http_code >= 300) {
    http_response_code(502);
    $response['error'] = 'Ollama returned HTTP ' . $http_code;

    if (AGENT_AI_DEBUG) {
        $response['details'] = $result;
    }

    echo json_encode($response);
    exit;
}

$ollama = json_decode($result, true);

if (!is_array($ollama)) {
    http_response_code(502);
    $response['error'] = 'Invalid Ollama response';
    echo json_encode($response);
    exit;
}

$answer = '';

if (isset($ollama['message']['content'])) {
    $answer = trim((string)$ollama['message']['content']);
}

if ($answer === '') {
    http_response_code(502);
    $response['error'] = 'Empty AI response';
    echo json_encode($response);
    exit;
}

$response['success'] = true;
$response['message'] = $answer;
$response['context_received'] = $context;

echo json_encode($response);
