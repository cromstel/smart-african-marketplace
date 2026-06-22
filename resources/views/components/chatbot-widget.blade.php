<div id="chatbot-widget" class="fixed bottom-20 right-5 z-50">
    <button id="chatbot-toggle" class="bg-gradient-to-r from-orange-600 to-red-600 text-white px-4 py-3 rounded-full shadow-lg">Chat Support</button>
    <div id="chatbot-panel" class="hidden mt-3 w-80 max-w-[90vw] bg-white border border-gray-200 rounded-xl shadow-2xl overflow-hidden">
        <div class="px-4 py-3 bg-gray-900 text-white font-semibold">Support Assistant</div>
        <div id="chatbot-status" class="px-4 py-2 text-xs text-gray-600 bg-gray-50 border-b border-gray-200 hidden"></div>
        <div id="chatbot-messages" class="h-80 overflow-y-auto p-3 space-y-2 bg-gray-50"></div>
        <div id="chatbot-typing" class="px-4 py-2 text-xs text-gray-500 hidden">Assistant is typing...</div>
        <form id="chatbot-form" class="p-3 border-t border-gray-200 flex gap-2">
            <label for="chatbot-input" class="sr-only">Chat message</label>
            <input id="chatbot-input" type="text" aria-label="Chat message" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Ask about products, orders, payments..." />
            <button type="submit" class="bg-gray-900 text-white px-3 py-2 rounded-lg text-sm">Send</button>
        </form>
        <div id="chatbot-error" class="px-4 pb-2 text-xs text-red-600 hidden"></div>
        <div class="px-3 pb-3">
            <button id="chatbot-handoff" type="button" class="text-xs text-gray-600 hover:text-gray-900 underline">Request a human agent</button>
        </div>
    </div>
</div>

<script>
    (function () {
        const toggle = document.getElementById('chatbot-toggle');
        const panel = document.getElementById('chatbot-panel');
        const form = document.getElementById('chatbot-form');
        const input = document.getElementById('chatbot-input');
        const messages = document.getElementById('chatbot-messages');
        const handoffButton = document.getElementById('chatbot-handoff');
        const statusBanner = document.getElementById('chatbot-status');
        const typingIndicator = document.getElementById('chatbot-typing');
        const errorBanner = document.getElementById('chatbot-error');
        let sessionKey = sessionStorage.getItem('chatbot_session_key');
        let hasGreeted = false;
        let pollingTimer = null;
        let lastMessageId = 0;
        let handoffActive = sessionStorage.getItem('chatbot_handoff_active') === '1';
        let pending = false;
        let pollDelay = 4000;
        let reinitAttempted = false;

        function appendMessage(text, role) {
            const div = document.createElement('div');
            div.dataset.role = role;
            div.textContent = text;

            if (role === 'user') {
                div.className = 'bg-gray-900 text-white text-sm px-3 py-2 rounded-lg ml-8';
            } else if (role === 'agent') {
                div.className = 'bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm px-3 py-2 rounded-lg';
            } else {
                div.className = 'bg-white border border-gray-200 text-gray-800 text-sm px-3 py-2 rounded-lg';
            }
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        function setStatus(message) {
            if (!message) {
                statusBanner.classList.add('hidden');
                statusBanner.textContent = '';
                return;
            }
            statusBanner.textContent = message;
            statusBanner.classList.remove('hidden');
        }

        function setError(message) {
            if (!message) {
                errorBanner.classList.add('hidden');
                errorBanner.textContent = '';
                return;
            }
            errorBanner.textContent = message;
            errorBanner.classList.remove('hidden');
        }

        function setPendingState(isPending) {
            pending = isPending;
            input.disabled = isPending;
            form.querySelector('button[type="submit"]').disabled = isPending;
            typingIndicator.classList.toggle('hidden', !isPending);
        }

        function updateHandoffUI() {
            if (handoffActive) {
                sessionStorage.setItem('chatbot_handoff_active', '1');
                handoffButton.disabled = true;
                handoffButton.classList.add('opacity-60', 'cursor-not-allowed');
                handoffButton.textContent = 'Human agent requested';
            } else {
                sessionStorage.removeItem('chatbot_handoff_active');
                handoffButton.disabled = false;
                handoffButton.classList.remove('opacity-60', 'cursor-not-allowed');
                handoffButton.textContent = 'Request a human agent';
            }
        }

        function lastUserMessageText() {
            const items = Array.from(messages.querySelectorAll('[data-role="user"]'));
            if (items.length === 0) {
                return null;
            }
            return items[items.length - 1].textContent;
        }

        async function initSession() {
            setError('');
            const response = await fetch('{{ route('chatbot.session') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ session_key: sessionKey })
            });

            if (!response.ok) {
                appendMessage('Hello! I am currently unavailable. Please try again shortly.', 'assistant');
                setError('Chat service unavailable.');
                return false;
            }

            const data = await response.json();
            sessionKey = data.session_key;
            sessionStorage.setItem('chatbot_session_key', sessionKey);
            handoffActive = !!data.handoff_active;
            reinitAttempted = false;
            if (handoffActive) {
                setStatus('A human agent is on this chat. Responses may take a moment.');
            }
            updateHandoffUI();
            if (!hasGreeted) {
                appendMessage(data.greeting, 'assistant');
                hasGreeted = true;
            }

            return true;
        }

        function resetSessionState() {
            sessionKey = null;
            sessionStorage.removeItem('chatbot_session_key');
            sessionStorage.removeItem('chatbot_handoff_active');
            handoffActive = false;
            updateHandoffUI();
        }

        async function fetchMessages() {
            if (!sessionKey) {
                return;
            }
            const response = await fetch(`{{ route('chatbot.messages') }}?session_key=${encodeURIComponent(sessionKey)}&after_id=${lastMessageId}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const incoming = data.messages || [];
            if (incoming.length === 0) {
                pollDelay = Math.min(pollDelay + 1000, 10000);
                return;
            }

            incoming.forEach((message) => {
                const role = message.role === 'agent' ? 'agent' : (message.role === 'user' ? 'user' : 'assistant');
                if (role === 'user' && message.content === lastUserMessageText()) {
                    lastMessageId = Math.max(lastMessageId, message.id);
                    return;
                }
                appendMessage(message.content, role);
                if (role === 'agent') {
                    handoffActive = true;
                    setStatus('A human agent is on this chat. Responses may take a moment.');
                    updateHandoffUI();
                }
                lastMessageId = Math.max(lastMessageId, message.id);
            });
            pollDelay = 4000;
        }

        function startPolling() {
            if (pollingTimer) {
                return;
            }
            const poll = async () => {
                await fetchMessages();
                pollingTimer = setTimeout(poll, pollDelay);
            };
            pollingTimer = setTimeout(poll, pollDelay);
        }

        function stopPolling() {
            if (pollingTimer) {
                clearTimeout(pollingTimer);
                pollingTimer = null;
            }
        }

        function bindRealtime() {
            if (!window.Echo || !sessionKey) {
                return false;
            }

            window.Echo.channel(`chatbot.session.${sessionKey}`)
                .listen('.ChatbotMessageCreated', (event) => {
                    if (!event || !event.content) {
                        return;
                    }
                    if (event.role === 'user') {
                        return;
                    }
                    appendMessage(event.content, event.role === 'agent' ? 'agent' : 'assistant');
                    if (event.role === 'agent') {
                        handoffActive = true;
                        setStatus('A human agent is on this chat. Responses may take a moment.');
                        updateHandoffUI();
                    }
                    if (event.id) {
                        lastMessageId = Math.max(lastMessageId, event.id);
                    }
                });

            return true;
        }

        toggle.addEventListener('click', async () => {
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) {
                const ok = await initSession();
                if (ok) {
                    await fetchMessages();
                    if (!bindRealtime()) {
                        startPolling();
                    }
                }
            } else {
                stopPolling();
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = input.value.trim();
            if (!message) {
                return;
            }
            if (pending) {
                return;
            }

            if (!sessionKey) {
                const ok = await initSession();
                if (!ok) {
                    return;
                }
            }

            appendMessage(message, 'user');
            input.value = '';
            setPendingState(true);
            setError('');

            let response = await fetch('{{ route('chatbot.message') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ session_key: sessionKey, message })
            });

            if (response.status === 404 && !reinitAttempted) {
                reinitAttempted = true;
                resetSessionState();
                const ok = await initSession();
                if (ok) {
                    response = await fetch('{{ route('chatbot.message') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ session_key: sessionKey, message })
                    });
                }
            }

            if (!response.ok) {
                appendMessage('Hello! I am currently unavailable. Please try again shortly.', 'assistant');
                setError('Unable to reach the support assistant.');
                setPendingState(false);
                return;
            }

            const data = await response.json();
            appendMessage(data.message, data.source === 'handoff' ? 'agent' : 'assistant');
            if (data.source === 'handoff') {
                handoffActive = true;
                setStatus('A human agent is on this chat. Responses may take a moment.');
                updateHandoffUI();
            }
            setPendingState(false);
        });

        handoffButton.addEventListener('click', async () => {
            if (!sessionKey) {
                const ok = await initSession();
                if (!ok) {
                    return;
                }
            }
            if (pending) {
                return;
            }
            setPendingState(true);
            setError('');
            let response = await fetch('{{ route('chatbot.handoff') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ session_key: sessionKey })
            });

            if (response.status === 404 && !reinitAttempted) {
                reinitAttempted = true;
                resetSessionState();
                const ok = await initSession();
                if (ok) {
                    response = await fetch('{{ route('chatbot.handoff') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ session_key: sessionKey })
                    });
                }
            }

            if (!response.ok) {
                appendMessage('Hello! I am currently unavailable. Please try again shortly.', 'assistant');
                setError('Unable to request a human agent right now.');
                setPendingState(false);
                return;
            }

            const data = await response.json();
            appendMessage(data.message, 'agent');
            handoffActive = true;
            setStatus('A human agent is on this chat. Responses may take a moment.');
            updateHandoffUI();
            setPendingState(false);
        });
    })();
</script>

