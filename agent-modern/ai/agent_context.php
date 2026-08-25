<?php
/*
 * VICIdial Agent AI
 * Phase 2A.2 - Agent Context Bridge
 *
 * READ-ONLY ONLY.
 *
 * This file normalizes agent-side context for the AI layer.
 * It must never contain passwords or perform call-control actions.
 */

require_once __DIR__ . '/ai_config.php';

function agent_ai_context_value($value, $max_length = 500)
{
    if (is_array($value) || is_object($value)) {
        return '';
    }

    $value = trim((string)$value);

    if (strlen($value) > $max_length) {
        $value = substr($value, 0, $max_length);
    }

    return $value;
}

function agent_ai_build_context($source = array())
{
    $context = array(
        'agent_id'       => '',
        'campaign'       => '',
        'session_name'   => '',
        'extension'      => '',
        'lead_id'        => '',
        'phone_number'   => '',
        'uniqueid'       => '',
        'agent_log_id'   => '',
        'call_id'        => '',
        'call_state'     => '',
        'timestamp'      => date('Y-m-d H:i:s')
    );

    $map = array(
        'agent_id'     => 'agent_id',
        'campaign'     => 'campaign',
        'session_name' => 'session_name',
        'extension'    => 'extension',
        'lead_id'      => 'lead_id',
        'phone_number' => 'phone_number',
        'uniqueid'     => 'uniqueid',
        'agent_log_id' => 'agent_log_id',
        'call_id'      => 'call_id',
        'call_state'   => 'call_state'
    );

    foreach ($map as $context_key => $source_key) {
        if (isset($source[$source_key])) {
            $context[$context_key] =
                agent_ai_context_value($source[$source_key]);
        }
    }

    return $context;
}
