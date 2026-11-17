            </main>
        </div><!-- /.app-main-wrapper -->
    </div><!-- /.app-layout -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASEURL; ?>/assets/js/script.js"></script>

    <script>
    // Global Theme Engine Initialization & Switcher
    window.applyTheme = function(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('app_theme', theme);
        localStorage.setItem('theme', theme);

        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        const themeTextBadge = document.getElementById('themeTextBadge');

        if (themeIcon && themeText) {
            if (theme === 'dark') {
                themeIcon.className = 'bi bi-sun-fill text-warning fs-5';
                themeText.textContent = 'Terang';
                if (themeTextBadge) {
                    themeTextBadge.className = 'badge rounded-pill bg-warning text-dark border px-2 py-1 small fw-semibold';
                }
            } else {
                themeIcon.className = 'bi bi-moon-stars text-primary fs-5';
                themeText.textContent = 'Gelap';
                if (themeTextBadge) {
                    themeTextBadge.className = 'badge rounded-pill bg-light text-secondary border px-2 py-1 small fw-semibold';
                }
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        const currentTheme = localStorage.getItem('app_theme') || localStorage.getItem('theme') || 'light';
        window.applyTheme(currentTheme);

        const themeToggleBtn = document.getElementById('globalThemeToggle');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                const nextTheme = isDark ? 'light' : 'dark';
                window.applyTheme(nextTheme);
            });
        }
    });
    </script>

<?php if(isset($_SESSION['user_id']) && ChatbotHelper::isActive()): ?>
    <!-- ===== AI CHATBOT WIDGET (LIGHT MINIMALIST THEMED) ===== -->
    <style>
        #chatbot-toggle {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background-color: #2563EB;
            color: #FFFFFF;
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
            transform: scale(1.06);
            background-color: #1D4ED8;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
        }

        #chatbot-panel {
            position: fixed;
            bottom: 86px;
            right: 24px;
            width: 360px;
            max-height: 520px;
            height: 500px;
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 20px 30px rgba(15, 23, 42, 0.12), 0 8px 12px rgba(15, 23, 42, 0.06);
            display: none;
            flex-direction: column;
            z-index: 9999;
            overflow: hidden;
            border: 1px solid #E5E7EB;
        }
        #chatbot-panel.open { display: flex; }

        #chatbot-header {
            background-color: #2563EB;
            color: white;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        #chatbot-header .header-info { display: flex; align-items: center; gap: 8px; }
        #chatbot-header .header-title { font-weight: 600; font-size: 14px; }
        #chatbot-header .status-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #1cc88a; display: inline-block;
        }
        #chatbot-close {
            background: rgba(255,255,255,0.15);
            border: none; color: white;
            border-radius: 50%;
            width: 26px; height: 26px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 16px; transition: background 0.2s;
        }
        #chatbot-close:hover { background: rgba(255,255,255,0.3); }

        #chatbot-messages {
            flex: 1;
            overflow-y: auto;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #f8f9fc;
        }
        .chat-bubble {
            max-width: 85%;
            padding: 9px 13px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.5;
            word-wrap: break-word;
        }
        .chat-bubble.bot {
            background: #ffffff;
            border: 1px solid #e3e6f0;
            color: #2e384d;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .chat-bubble.user {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .chat-bubble.typing { color: #888; font-style: italic; background: #eef2f7; }

        #chatbot-input-area {
            display: flex;
            padding: 10px 12px;
            gap: 8px;
            border-top: 1px solid #e3e6f0;
            background: #fff;
        }
        #chatbot-input {
            flex: 1;
            border: 1px solid #d1d3e2;
            border-radius: 20px;
            padding: 8px 14px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }
        #chatbot-input:focus { border-color: #4e73df; }
        #chatbot-send {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            flex-shrink: 0;
            transition: opacity 0.2s;
        }
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
                <span class="badge bg-light text-dark ms-1" style="font-size:10px;"><?= ucfirst(str_replace('_', ' ', $_SESSION['role'] ?? 'User')) ?></span>
            </div>
            <button id="chatbot-close" title="Tutup">×</button>
        </div>

        <div id="chatbot-messages">
            <div class="chat-bubble bot">
                <?php if (($_SESSION['role'] ?? '') === 'kepala_sekolah'): ?>
                    👋 Halo <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Bapak/Ibu Kepala Sekolah') ?></strong>! Saya adalah Asisten Virtual Dashboard Kepala Sekolah.<br><br>
                    Saya siap membantu Anda memantau kehadiran dewan guru, rekapitulasi presensi guru hari ini, dan jadwal kegiatan belajar mengajar (KBM).
                <?php else: ?>
                    👋 Halo <strong><?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? 'User') ?></strong>! Saya adalah Asisten Virtual Sistem Absensi SMK.<br><br>
                    Bagaimana saya bisa membantu Anda hari ini?
                <?php endif; ?>
            </div>
        </div>

        <div id="chatbot-input-area">
            <input type="text" id="chatbot-input" placeholder="Tanyakan bantuan sistem..." maxlength="500">
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
        const ENDPOINT= '<?= BASEURL ?>/chatbot';

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

        async function sendMessage() {
            const text = input.value.trim();
            if (!text) return;

            addBubble(text, 'user');
            input.value = '';
            setLoading(true);

            const typingBubble = addBubble('Sedang mengetik...', 'bot typing');

            try {
                const res = await fetch(ENDPOINT, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text })
                });

                const rawText = await res.text();
                let data = null;
                try {
                    const startJson = rawText.indexOf('{');
                    const endJson = rawText.lastIndexOf('}');
                    if (startJson !== -1 && endJson !== -1 && endJson >= startJson) {
                        data = JSON.parse(rawText.substring(startJson, endJson + 1));
                    } else {
                        data = JSON.parse(rawText);
                    }
                } catch (e) {
                    console.error("Chatbot Response Non-JSON:", rawText);
                }

                typingBubble.remove();

                if (data && data.success && data.message) {
                    addBubble(data.message, 'bot');
                } else if (data && data.message) {
                    addBubble(data.message, 'bot');
                } else {
                    addBubble('Halo kak, sistem sedang menyiapkan jawaban. Silakan coba tanyakan kembali ya!', 'bot');
                }

            } catch (err) {
                typingBubble.remove();
                addBubble('Maaf, koneksi server terputus. Silakan coba kembali.', 'bot');
            } finally {
                setLoading(false);
                input.focus();
            }
        }
    })();
    </script>
    <!-- ===== END AI CHATBOT WIDGET ===== -->
<?php endif; ?>

</body>
</html>
