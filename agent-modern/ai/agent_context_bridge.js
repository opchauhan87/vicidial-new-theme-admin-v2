/*
 * VICIdial Agent AI
 * Phase 2A.2 - Browser Context Bridge
 *
 * READ-ONLY ONLY.
 *
 * This file reads the current VICIdial agent context.
 * It does NOT dial, hang up, disposition, or modify lead data.
 */

(function (window, document) {
    'use strict';

    function valueById(id) {
        var element = document.getElementById(id);

        if (!element) {
            return '';
        }

        return String(element.value || '').trim();
    }

    function valueByName(name) {
        var element = document.querySelector('[name="' + name + '"]');

        if (!element) {
            return '';
        }

        return String(element.value || '').trim();
    }

    window.AgentAIContextBridge = {

        getContext: function () {
            return {
                /*
                 * Safe identity values supplied by the PHP runtime.
                 * No passwords or credentials are exposed.
                 */
                agent_id: (window.AgentAIIdentity && window.AgentAIIdentity.agent_id) || '',
                campaign: (window.AgentAIIdentity && window.AgentAIIdentity.campaign) || '',
                session_name: (window.AgentAIIdentity && window.AgentAIIdentity.session_name) || '',
                extension: '',

                /*
                 * Live VICIdial lead/call fields.
                 */
                lead_id: valueById('lead_id'),
                phone_number: valueById('phone_number'),
                uniqueid: valueById('uniqueid'),
                agent_log_id: valueById('agent_log_id'),
                call_id: valueById('LasTCID'),

                /*
                 * Current visible agent status.
                 */
                call_state: valueById('AgentStatusStatus'),

                timestamp: new Date().toISOString()
            };
        }
    };

})(window, document);
