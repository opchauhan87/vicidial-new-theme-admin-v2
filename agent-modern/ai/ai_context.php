<?php
/*
 * VICIdial Agent AI
 * Phase 2A.1 - Context Collector
 *
 * Read-only.
 * Does NOT modify VICIdial data.
 */

function agent_ai_clean_context_value($value, $max_length = 500)
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

function agent_ai_get_context()
{
    $context = array(
        'agent'           => '',
        'session_name'    => '',
        'extension'       => '',
        'campaign'       => '',
        'lead_id'         => '',
        'phone_number'    => '',
        'vendor_lead_code'=> '',
        'first_name'      => '',
        'last_name'       => '',
        'call_state'      => '',
        'timestamp'       => date('Y-m-d H:i:s')
    );

    foreach ($context as $key => $unused) {
        if (isset($_POST[$key])) {
            $context[$key] = agent_ai_clean_context_value($_POST[$key]);
        }
    }

    return $context;
}
