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

    function textById(id) {
        var element = document.getElementById(id);

        if (!element) {
            return '';
        }

        return String(element.textContent || '').trim();
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
                lead_id: (
                    document.callcenter_form &&
                    document.callcenter_form.lead_id
                ) ? String(document.callcenter_form.lead_id.value || '').trim() : '',

                phone_number: (
                    document.callcenter_form &&
                    document.callcenter_form.phone_number
                ) ? String(document.callcenter_form.phone_number.value || '').trim() : '',

                vendor_lead_code: (
                    document.callcenter_form &&
                    document.callcenter_form.vendor_lead_code
                ) ? String(document.callcenter_form.vendor_lead_code.value || '').trim() : '',

                first_name: (
                    document.callcenter_form &&
                    document.callcenter_form.first_name
                ) ? String(document.callcenter_form.first_name.value || '').trim() : '',

                last_name: (
                    document.callcenter_form &&
                    document.callcenter_form.last_name
                ) ? String(document.callcenter_form.last_name.value || '').trim() : '',

                uniqueid: valueById('uniqueid'),
                agent_log_id: valueById('agent_log_id'),
                call_id: valueById('LasTCID'),

                /*
                 * Current visible agent status.
                 * AgentStatusStatus is a text span, not an input.
                 */
                call_state: textById('AgentStatusStatus'),

                timestamp: new Date().toISOString()
            };
        }
    };

})(window, document);
