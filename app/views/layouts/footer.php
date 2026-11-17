    </div> <!-- End Container from Header -->
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASEURL; ?>/assets/js/script.js"></script>
    <!-- ===== AI CHATBOT WIDGET ===== -->
    <style>
        #chatbot-toggle {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #2563EB;
            color: white;
            border: none;
            font-size: 22px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9998;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        #chatbot-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
        }

        #chatbot-panel {
            position: fixed;
            bottom: 90px;
            right: 24px;
            width: 360px;
            max-height: 520px;
            height: 480px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 12px 36px rgba(15, 23, 42, 0.16);
            display: none;
            flex-direction: column;
            z-index: 9999;
            overflow: hidden;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            border: 1px solid #E5E7EB;
        }
        #chatbot-panel.open { display: flex; }

        #chatbot-header {
            background: #2563EB;
            color: white;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        #chatbot-header .header-info { display: flex; align-items: center; gap: 8px; }
        #chatbot-header .header-title { font-weight: 700; font-size: 14px; letter-spacing: -0.2px; }
        #chatbot-header .status-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #10B981; display: inline-block;
        }
        #chatbot-close {
            background: rgba(255,255,255,0.18);
            border: none; color: white;
            border-radius: 50%;
            width: 26px; height: 26px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 15px; transition: background 0.2s;
        }
        #chatbot-close:hover { background: rgba(255,255,255,0.3); }

        #chatbot-messages {
            flex: 1;
            overflow-y: auto;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #F8FAFC;
        }
        .chat-bubble {
            max-width: 85%;
            padding: 10px 14px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.5;
            word-wrap: break-word;
        }
        .chat-bubble.bot {
            background: #ffffff;
            border: 1px solid #E5E7EB;
            color: #1F2937;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .chat-bubble.user {
            background: #2563EB;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .chat-bubble.typing { color: #64748B; font-style: italic; background: #E2E8F0; }

        #chatbot-input-area {
            display: flex;
            padding: 10px 12px;
            gap: 8px;
            border-top: 1px solid #E5E7EB;
            background: #fff;
        }
        #chatbot-input {
            flex: 1;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        #chatbot-input:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12); }
        #chatbot-send {
            background: #2563EB;
            color: white;
            border: none;
            border-radius: 10px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            flex-shrink: 0;
            transition: opacity 0.2s, background-color 0.2s;
        }
        #chatbot-send:hover { background: #1D4ED8; }
        #chatbot-send:hover { opacity: 0.9; }
        #chatbot-send:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Responsive untuk HP */
        @media (max-width: 576px) {
            #chatbot-panel {
                width: calc(100vw - 32px);
                right: 16px;
                bottom: 80px;
                height: calc(100vh - 120px);
                max-height: 520px;
            }
            #chatbot-toggle {
                bottom: 16px;
                right: 16px;
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }
    </style>

    <!-- Floating Chatbot Toggle Button -->
    <button id="chatbot-toggle" title="Asisten Virtual SMK">
        <i class="bi bi-robot"></i>
    </button>

    <!-- Chat Panel Window -->
    <div id="chatbot-panel">
        <div id="chatbot-header">
            <div class="header-info">
                <span class="status-dot"></span>
                <span class="header-title">Asisten Virtual SMK</span>
                <span class="badge bg-light text-dark ms-1" style="font-size:10px;"><?= ucfirst(str_replace('_', ' ', $_SESSION['role'] ?? 'Pengunjung')) ?></span>
            </div>
            <button id="chatbot-close" title="Tutup">×</button>
        </div>

        <div id="chatbot-messages">
            <div class="chat-bubble bot">
                👋 Halo <strong><?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? 'Tamu') ?></strong>! Saya adalah Asisten Virtual Sistem Absensi SMK.<br><br>
                Bagaimana saya bisa membantu Anda hari ini?
            </div>
        </div>

        <div id="chatbot-input-area">
            <input type="text" id="chatbot-input" placeholder="Tanyakan sesuatu..." maxlength="500">
            <button id="chatbot-send" title="Kirim">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>

    <script>
    (function() {
        const toggle  = document.getElementById('chatbot-toggle');
        const panel   = document.getElementById('chatbot-panel');
        const closeBtn= document.getElementById('chatbot-close');
        const messages= document.getElementById('chatbot-messages');
        const input   = document.getElementById('chatbot-input');
        const sendBtn = document.getElementById('chatbot-send');
        const ENDPOINT = '<?= BASEURL ?>/chatbot';

        toggle.addEventListener('click', () => {
            panel.classList.toggle('open');
            if (panel.classList.contains('open')) {
                input.focus();
                scrollBottom();
            }
        });
        closeBtn.addEventListener('click', () => panel.classList.remove('open'));

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        sendBtn.addEventListener('click', sendMessage);

        function scrollBottom() {
            messages.scrollTop = messages.scrollHeight;
        }

        function addBubble(text, role) {
            const div = document.createElement('div');
            div.className = 'chat-bubble ' + role;
            let formatted = text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
            div.innerHTML = formatted;
            messages.appendChild(div);
            scrollBottom();
            return div;
        }

        function setLoading(state) {
            sendBtn.disabled = state;
            input.disabled = state;
        }

        async function fetchAIResponse(messageText) {
            const res = await fetch(ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: messageText })
            });
            const rawText = await res.text();
            try {
                return JSON.parse(rawText);
            } catch (e) {
                console.error("Chatbot Response Non-JSON:", rawText);
                return { success: false, message: "Terjadi sedikit kendala saat memproses jawaban. Silakan coba lagi ya!" };
            }
        }

        async function sendMessage() {
            const text = input.value.trim();
            if (!text) return;

            addBubble(text, 'user');
            input.value = '';
            setLoading(true);

            const typingBubble = addBubble('Sedang mengetik...', 'bot typing');

            try {
                const data = await fetchAIResponse(text);
                typingBubble.remove();

                if (data.success && data.message) {
                    addBubble(data.message, 'bot');
                } else if (!data.success && data.message) {
                    addBubble(data.message, 'bot');
                } else {
                    addBubble('Halo kak, ada sedikit kendala sistem. Silakan coba tanyakan kembali ya!', 'bot');
                }

            } catch (err) {
                typingBubble.remove();
                addBubble('Maaf kak, koneksi ke asisten AI sedang terputus. Pastikan server web aktif ya.', 'bot');
            } finally {
                setLoading(false);
                input.focus();
            }
        }
    })();
    </script>
    <!-- ===== END AI CHATBOT WIDGET ===== -->
</body>
</html>
