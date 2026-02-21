<?php

$pluginsDir = dirname(__DIR__);

return [
    'phpList\plugin\SendGridPlugin\MailClient' => $pluginsDir . '/SendGridPlugin/MailClient.php',
    'phpList\plugin\SendGridPlugin\WebhookHandler' => $pluginsDir . '/SendGridPlugin/WebhookHandler.php',
];
