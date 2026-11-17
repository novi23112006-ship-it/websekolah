<?php
require_once __DIR__ . '/core/App.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Database.php';

require_once __DIR__ . '/helpers/Flasher.php';
require_once __DIR__ . '/helpers/AuthHelper.php';
require_once __DIR__ . '/helpers/DateHelper.php';
require_once __DIR__ . '/helpers/LokasiHelper.php';
require_once __DIR__ . '/helpers/TelegramHelper.php';
require_once __DIR__ . '/helpers/ai/AiProviderFactory.php';
require_once __DIR__ . '/helpers/ChatbotHelper.php';

require_once __DIR__ . '/../config/config.php';
date_default_timezone_set('Asia/Jakarta');
