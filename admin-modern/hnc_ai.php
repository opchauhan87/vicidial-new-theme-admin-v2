<?php
/*
 * HNC AI - Local Ollama Endpoint
 * Model: Qwen3 4B
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error'   => 'POST method required'
    ]);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid JSON request'
    ]);
    exit;
}

$message = isset($data['message']) ? trim((string)$data['message']) : '';

if ($message === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Message cannot be empty'
    ]);
    exit;
}

/* Prevent unnecessarily large requests */
if (mb_strlen($message, 'UTF-8') > 4000) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Message is too long'
    ]);
    exit;
}

$hncKnowledge = require __DIR__ . '/hnc_ai_knowledge.php';

$payload = [
    'model' => 'qwen3:4b',
    'messages' => [
        [
            'role' => 'system',
            'content' =>
                'You are HNC AI Assistant for HNC Smart CRM. ' .
                'Be helpful, concise and professional. ' .
                'If you do not know something, say so clearly. ' .
                'Do not invent system information. ' .
                'Use the following verified HNC Smart CRM knowledge when answering system-specific questions. ' .
                'Do not treat information outside this knowledge as confirmed HNC functionality.' .
                "\n\nVERIFIED HNC SMART CRM KNOWLEDGE:\n" .
                $hncKnowledge
        ],
        [
            'role' => 'user',
            'content' => $message
        ]
    ],
    'stream' => false
];

$ch = curl_init('http://127.0.0.1:11434/api/chat');

curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT        => 60
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($response === false || $curlError !== '') {
    http_response_code(502);

    echo json_encode([
        'success' => false,
        'error'   => 'Unable to connect to local AI service: ' . $curlError
    ]);

    exit;
}

$result = json_decode($response, true);

if (!is_array($result)) {
    http_response_code(502);

    echo json_encode([
        'success' => false,
        'error'   => 'Invalid response from AI service'
    ]);

    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
    http_response_code(502);

    echo json_encode([
        'success' => false,
        'error'   => 'AI service returned an error'
    ]);

    exit;
}

$answer = '';

if (isset($result['message']['content'])) {
    $answer = trim((string)$result['message']['content']);
}

if ($answer === '') {
    http_response_code(502);

    echo json_encode([
        'success' => false,
        'error'   => 'AI returned an empty response'
    ]);

    exit;
}

/*
 * Deliberately return only the final answer.
 * Qwen internal "thinking" is not exposed to the browser.
 */
echo json_encode([
    'success' => true,
    'answer'  => $answer
], JSON_UNESCAPED_UNICODE);
