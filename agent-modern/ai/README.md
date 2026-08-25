# VICIdial Agent AI

## Phase 2A.1

This directory contains the isolated Agent AI foundation.

### Current components

- `ai_config.php`
  - AI configuration
  - No credentials
  - AI disabled by default

- `ai_context.php`
  - Read-only context collector
  - Agent/call/lead context

- `ai_api.php`
  - Isolated JSON endpoint
  - POST only
  - No VICIdial data modification

## Current AI permissions

The AI currently has:

- NO dialing permission
- NO hangup permission
- NO disposition permission
- NO lead update permission
- NO database write permission

## Future phases

2A.2 - Agent context bridge
2A.3 - AI assistant UI
2A.4 - Provider integration
2A.5 - Live call assistance
2A.6 - AI suggestions/actions
