<?php

namespace phpList\plugin\SendGridPlugin;

use phpList\plugin\Common\DAO\User;
use phpList\plugin\Common\DB;

class WebhookHandler
{
    public function run()
    {
        ob_end_clean();
        $logger = \phpList\plugin\Common\Logger::instance();
        $dao = new User(new DB());
        $postData = file_get_contents('php://input');

        if ($postData === false) {
            logEvent('SendGrid webhook error getting input');

            return;
        }
        $events = json_decode($postData);

        if ($events === null) {
            logEvent('SendGrid webhook error json decoding');
            $logger->debug(print_r($postData, true));

            return;
        }
        $logger->debug(print_r($events, true));
        $bounceCount = 0;
        $droppedCount = 0;
        $spamCount = 0;
        $unsubscribeCount = 0;
        $unknownCount = 0;
        $unhandledEventCount = 0;

        foreach ($events as $e) {
            $email = $e->email;

            if (!$dao->userByEmail($email)) {
                ++$unknownCount;
                continue;
            }

            switch ($e->event) {
                case 'bounce':
                    ++$bounceCount;
                    addUserToBlackList($email, "SendGrid bounce $e->reason $e->status");
                    break;
                case 'dropped':
                    ++$droppedCount;
                    addUserToBlackList($email, "SendGrid dropped $e->reason $e->status");
                    break;
                case 'spamreport':
                    ++$spamCount;
                    addUserToBlackList($email, 'SendGrid spam report');
                    break;
                case 'unsubscribe':
                    ++$unsubscribeCount;
                    addUserToBlackList($email, 'SendGrid unsubscribe');
                    break;
                default:
                    ++$unhandledEventCount;
            }
        }
        $event = sprintf(
            'SendGrid - bounces: %d, spam reports: %d, dropped: %d, unsubscribe: %d, unknown: %d, unhandled events: %d',
            $bounceCount,
            $spamCount,
            $droppedCount,
            $unsubscribeCount,
            $unknownCount,
            $unhandledEventCount
        );
        logEvent($event);
    }
}
