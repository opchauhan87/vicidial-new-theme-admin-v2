<?php
/*
 * VICIdial Agent AI
 * Phase 2A.1 - AI API Gateway
 *
 * READ-ONLY FOUNDATION.
 *
 * No lead update
 * No disposition
 * No dialing
 * No hangup
 * No VICIdial database modification
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/ai_config.php';
require_once __DIR__ . '/ai_context.php';

$response = array(
    'success' => false,
    'phase'   => '2A.1',
    'enabled' => AGENT_AI_ENABLED,
    'provider' => AGENT_AI_PROVIDER
);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    $response['error'] = 'POST required';

    echo json_encode($response);
    exit;
}

$context = agent_ai_get_context();

$response['success'] = true;
$response['message'] = 'Agent AI foundation is reachable.';
$response['context_received'] = $context;

echo json_encode($response);
