<?php
/**
 * SendGridPlugin plugin for phplist.
 *
 * This file is a part of SendGridPlugin Plugin.
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category  phplist
 *
 * @author    Duncan Cameron
 * @copyright 2016-2017 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/**
 * Registers the plugin with phplist.
 */
if (!interface_exists('EmailSender')) {
    return;
}
class SendGridPlugin extends phplistPlugin implements EmailSender
{
    const VERSION_FILE = 'version.txt';

    /** @var SendGrid connector instance */
    private $connector;

    /*
     *  Inherited variables
     */
    public $name = 'SendGrid Plugin';
    public $authors = 'Duncan Cameron';
    public $description = 'Use SendGrid to send emails';
    public $documentationUrl = 'https://resources.phplist.com/plugin/sendgrid';
    public $settings = array(
        'sendgrid_api_key' => array(
            'value' => '',
            'description' => 'API key',
            'type' => 'text',
            'allowempty' => false,
            'category' => 'Sendgrid',
        ),
    );

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->coderoot = dirname(__FILE__) . '/' . 'SendGridPlugin' . '/';
        parent::__construct();
        $this->version = (is_file($f = $this->coderoot . self::VERSION_FILE))
            ? file_get_contents($f)
            : '';
    }

    /**
     * Provide the dependencies for enabling this plugin.
     *
     * @return array
     */
    public function dependencyCheck()
    {
        global $emailsenderplugin;

        return array(
            'PHP version 5.4.0 or greater' => version_compare(PHP_VERSION, '5.4') > 0,
            'phpList version 3.3.0 or greater' => version_compare(getConfig('version'), '3.3') > 0,
            'No other plugin to send emails can be enabled' => empty($emailsenderplugin) || get_class($emailsenderplugin) == __CLASS__,
            'curl extension installed' => extension_loaded('curl'),
        );
    }

    /**
     * Send an email using the SendGrid API.
     *
     * @see https://sendgrid.com/docs/API_Reference/Web_API/mail.html
     *
     * @param PHPlistMailer $phpmailer mailer instance
     * @param string        $headers   the message http headers
     * @param string        $body      the message body
     *
     * @return bool success/failure
     */
    public function send(PHPlistMailer $phpmailer, $headers, $body)
    {
        if ($this->connector === null) {
            require $this->coderoot . 'Connector.php';
            $this->connector = new phpList\plugin\SendGridPlugin\Connector(getConfig('sendgrid_api_key'));
        }
        $to = $phpmailer->getToAddresses();
        $postData = array(
            'to' => $to[0][0],
            'from' => $phpmailer->From,
            'fromname' => $phpmailer->FromName,
            'subject' => $phpmailer->Subject,
        );
        /*
         * for an html message both Body and AltBody will be populated
         * for a plain text message only Body will be populated
         */
        $isHtml = $phpmailer->AltBody != '';

        if ($isHtml) {
            $postData['html'] = $phpmailer->Body;
            $postData['text'] = $phpmailer->AltBody;
        } else {
            $postData['text'] = $phpmailer->Body;
        }

        $headers = array();

        foreach ($phpmailer->getCustomHeaders() as $item) {
            $headers[$item[0]] = $item[1];
        }

        if (count($headers) > 0) {
            $postData['headers'] = json_encode($headers);
        }

        foreach ($phpmailer->getAttachments() as $item) {
            list($content, $filename, $name, $encoding, $type, $isString, $disposition, $cid) = $item;
            $key = sprintf('files[%s]', $name);
            $postData[$key] = $content;

            if ($disposition == 'inline') {
                $key = sprintf('content[%s]', $name);
                $postData[$key] = $cid;
            }
        }
        $result = $this->connector->makeApiCall('mail.send', $postData);

        if ($result['message'] === 'success') {
            return true;
        }
        $error = implode(', ', $result['errors']);
        echo "send failed: $error";

        return false;
    }
}
