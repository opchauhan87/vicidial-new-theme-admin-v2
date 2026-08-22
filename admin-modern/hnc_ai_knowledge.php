<?php

return <<<'KNOWLEDGE'
HNC SMART CRM - VERIFIED SYSTEM KNOWLEDGE

PRODUCT
HNC AI Assistant for HNC Smart CRM.
The current environment is integrated with VICIdial administration.

RULES
- Use only verified information in this knowledge.
- Do not invent HNC Smart CRM features.
- If information is unavailable, say so clearly.
- Do not claim that undocumented functionality exists.

LEAD AND CUSTOMER DATA
The current VICIdial environment uses vicidial_list
for lead/customer records.

Known fields include:
lead_id
first_name
middle_initial
last_name
phone_number
alt_phone
email
address1
address2
address3
city
state
postal_code
date_of_birth
status
user
list_id
owner
comments
called_count
last_local_call_time

CAMPAIGNS
Current active campaigns:
CLIENT-1
CLIENT-2
HNC-AUTO

Current CRM-related campaign configuration:
- Search category: LEAD
- Create call record: enabled
- Create lead record: enabled
- Screen login: enabled
- Dead lead handling: ASK
- CRM status call integration: disabled
- CRM popup login: disabled
- CRM login address: not configured

CRM INTEGRATION
Current campaigns contain CRM/Vtiger integration settings
for lead search, call-record creation, lead-record creation,
and screen login.

No crm_* database tables were found during discovery.

CALLBACKS
The current environment uses vicidial_callbacks
for callback records.

Known callback fields include:
lead_id
list_id
campaign_id
status
entry_time
callback_time
user
recipient
comments
user_group
lead_status
customer_timezone
customer_time

DISPOSITIONS
Known VICIdial disposition classifications include:
human answered
sale
DNC
customer contact
not interested
unworkable
scheduled callback
completed
answering machine

AI BEHAVIOR
When answering HNC Smart CRM questions:
- Be concise and professional.
- Prefer verified system knowledge.
- Never invent undocumented functionality.
- Clearly state when information is unavailable.
KNOWLEDGE;
