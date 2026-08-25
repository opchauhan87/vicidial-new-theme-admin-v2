<?php
/*
 * VICIdial Agent AI
 * Phase 2A.1 - Configuration
 *
 * IMPORTANT:
 * No API key is stored in this file.
 * Provider credentials will be configured separately.
 */

if (!defined('AGENT_AI_ENABLED')) {
    define('AGENT_AI_ENABLED', true);
}

if (!defined('AGENT_AI_DEBUG')) {
    define('AGENT_AI_DEBUG', false);
}

/*
 * AI provider endpoint will be added in a later phase.
 */
if (!defined('AGENT_AI_PROVIDER')) {
    define('AGENT_AI_PROVIDER', 'ollama');
}

if (!defined('AGENT_AI_TIMEOUT')) {
    define('AGENT_AI_TIMEOUT', 20);
}
