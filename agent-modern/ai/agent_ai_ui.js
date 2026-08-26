/*
 * VICIdial Agent AI
 * Phase 2A.3 - Agent AI UI
 *
 * READ-ONLY ONLY.
 *
 * This UI:
 * - Reads AgentAIContextBridge
 * - Sends questions to ai_api.php
 * - Displays AI responses
 *
 * It does NOT:
 * - Dial
 * - Hang up
 * - Disposition
 * - Modify lead data
 */

(function (window, document) {
    'use strict';

    var panel = null;
    var input = null;
    var askButton = null;
    var responseBox = null;
    var contextBox = null;

    function createElement(tag, className, text) {
        var el = document.createElement(tag);

        if (className) {
            el.className = className;
        }

        if (typeof text !== 'undefined') {
            el.textContent = text;
        }

        return el;
    }

    function getContext() {
        if (
            window.AgentAIContextBridge &&
            typeof window.AgentAIContextBridge.getContext === 'function'
        ) {
            return window.AgentAIContextBridge.getContext();
        }

        return {};
    }

    function updateContext() {
        var context = getContext();

        var agent = context.agent_id || '';
        var campaign = context.campaign || '';
        var lead = context.lead_id || '';

        var parts = [];

        if (agent) {
            parts.push('Agent ' + agent);
        }

        if (campaign) {
            parts.push(campaign);
        }

        if (lead) {
            parts.push('Lead #' + lead);
        }

        contextBox.textContent =
            parts.length ? parts.join(' • ') : 'Waiting for agent context';
    }

    function setLoading(loading) {
        askButton.disabled = loading;
        input.disabled = loading;

        askButton.textContent = loading ? 'Thinking…' : 'Ask AI';
    }

    function askAI() {
        var message = String(input.value || '').trim();

        if (!message) {
            responseBox.textContent = 'Enter a question first.';
            return;
        }

        var context = getContext();

        setLoading(true);
        responseBox.textContent = 'AI is preparing a response…';

        var payload = {};

        Object.keys(context).forEach(function (key) {
            payload[key] = context[key];
        });

        payload.message = message;

        fetch('ai/ai_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(function (response) {
            return response.json().then(function (data) {
                return {
                    ok: response.ok,
                    data: data
                };
            });
        })
        .then(function (result) {
            if (!result.ok || !result.data.success) {
                throw new Error(
                    result.data.error || 'AI request failed'
                );
            }

            responseBox.textContent =
                result.data.message || 'No response received.';
        })
        .catch(function (error) {
            responseBox.textContent =
                'AI unavailable: ' + error.message;
        })
        .finally(function () {
            setLoading(false);
        });
    }

    function createPanel() {
        if (document.getElementById('agent-ai-panel')) {
            return;
        }

        panel = createElement('div');
        panel.id = 'agent-ai-panel';

        panel.style.cssText =
            'position:fixed;' +
            'right:205px;' +
            'bottom:70px;' +
            'width:340px;' +
            'max-width:calc(100vw - 225px);' +
            'z-index:99990;' +
            'background:#ffffff;' +
            'border:1px solid #cfe3d7;' +
            'border-radius:12px;' +
            'box-shadow:0 8px 28px rgba(0,0,0,.14);' +
            'font-family:Arial,sans-serif;' +
            'color:#173b2a;' +
            'overflow:hidden;';

        var header = createElement('div');
        header.style.cssText =
            'padding:12px 14px;' +
            'background:#eaf7ef;' +
            'border-bottom:1px solid #d7eadf;';

        var title = createElement(
            'div',
            '',
            '✦ Agent AI'
        );

        title.style.cssText =
            'font-size:15px;' +
            'font-weight:700;';

        contextBox = createElement(
            'div',
            '',
            'Waiting for agent context'
        );

        contextBox.style.cssText =
            'margin-top:4px;' +
            'font-size:11px;' +
            'color:#557565;';

        header.appendChild(title);
        header.appendChild(contextBox);

        var body = createElement('div');

        body.style.cssText =
            'padding:12px 14px;';

        input = createElement('textarea');

        input.rows = 3;
        input.placeholder =
            'Ask AI for a customer-service suggestion…';

        input.style.cssText =
            'width:100%;' +
            'box-sizing:border-box;' +
            'resize:vertical;' +
            'padding:9px;' +
            'border:1px solid #cbded3;' +
            'border-radius:8px;' +
            'font-size:13px;' +
            'outline:none;';

        askButton = createElement(
            'button',
            '',
            'Ask AI'
        );

        askButton.type = 'button';

        askButton.style.cssText =
            'margin-top:8px;' +
            'padding:8px 14px;' +
            'border:0;' +
            'border-radius:7px;' +
            'background:#2f7d52;' +
            'color:#ffffff;' +
            'font-weight:600;' +
            'cursor:pointer;';

        responseBox = createElement(
            'div',
            '',
            'AI response will appear here.'
        );

        responseBox.style.cssText =
            'margin-top:10px;' +
            'padding:10px;' +
            'min-height:42px;' +
            'background:#f7fbf8;' +
            'border:1px solid #e1eee6;' +
            'border-radius:8px;' +
            'font-size:13px;' +
            'line-height:1.45;' +
            'white-space:pre-wrap;';

        askButton.addEventListener('click', askAI);

        input.addEventListener('keydown', function (event) {
            if (
                event.key === 'Enter' &&
                !event.shiftKey
            ) {
                event.preventDefault();
                askAI();
            }
        });

        body.appendChild(input);
        body.appendChild(askButton);
        body.appendChild(responseBox);

        panel.appendChild(header);
        panel.appendChild(body);

        document.body.appendChild(panel);

        updateContext();

        window.setInterval(updateContext, 1000);
    }

    window.AgentAIUI = {
        init: createPanel,
        ask: askAI,
        refreshContext: updateContext
    };

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            createPanel
        );
    } else {
        createPanel();
    }

})(window, document);
