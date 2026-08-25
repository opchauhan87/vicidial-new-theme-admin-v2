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

function agent_ai_get_context($input = array())
{
    $context = array(
        'agent'            => '',
        'session_name'     => '',
        'extension'        => '',
        'campaign'         => '',
        'lead_id'          => '',
        'phone_number'     => '',
        'vendor_lead_code' => '',
        'first_name'       => '',
        'last_name'        => '',
        'call_state'       => '',
        'timestamp'        => date('Y-m-d H:i:s')
    );

    foreach ($context as $key => $unused) {
        if (isset($input[$key])) {
            $context[$key] = agent_ai_clean_context_value($input[$key]);
        } elseif (isset($_POST[$key])) {
            $context[$key] = agent_ai_clean_context_value($_POST[$key]);
        }
    }

    /*
     * Browser uses agent_id.
     * Internal context structure uses agent.
     */
    if ($context['agent'] === '' && isset($input['agent_id'])) {
        $context['agent'] =
            agent_ai_clean_context_value($input['agent_id']);
    } elseif ($context['agent'] === '' && isset($_POST['agent_id'])) {
        $context['agent'] =
            agent_ai_clean_context_value($_POST['agent_id']);
    }

    return $context;
}
