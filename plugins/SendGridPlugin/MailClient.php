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
 * @copyright 2016-2018 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

namespace phpList\plugin\SendGridPlugin;

class MailClient implements \phpList\plugin\Common\IMailClient
{
    /** @var string API key */
    private $apiKey;

    /**
     * Constructor.
     *
     * @param string $apiKey    the API key to use
     * @param bool   $sslVerify set false to disable CURL ssl cert verification
     * @param int    $timeout   timeout period
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function requestBody(\PHPlistMailer $phplistmailer, $headers, $body)
    {
        $to = $phplistmailer->getToAddresses();
        $request = array(
            'to' => $to[0][0],
            'from' => $phplistmailer->From,
            'fromname' => $phplistmailer->FromName,
            'subject' => $phplistmailer->Subject,
        );
        /*
         * for an html message both Body and AltBody will be populated
         * for a plain text message only Body will be populated
         */
        $isHtml = $phplistmailer->AltBody != '';

        if ($isHtml) {
            $request['html'] = $phplistmailer->Body;
            $request['text'] = $phplistmailer->AltBody;
        } else {
            $request['text'] = $phplistmailer->Body;
        }
        $customHeaders = array();

        foreach ($phplistmailer->getCustomHeaders() as $item) {
            $customHeaders[$item[0]] = $item[1];
        }

        if (count($customHeaders) > 0) {
            $request['headers'] = json_encode($customHeaders);
        }

        foreach ($phplistmailer->getAttachments() as $item) {
            list($content, $filename, $name, $encoding, $type, $isString, $disposition, $cid) = $item;
            $key = sprintf('files[%s]', $name);
            $request[$key] = $content;

            if ($disposition == 'inline') {
                $key = sprintf('content[%s]', $name);
                $request[$key] = $cid;
            }
        }

        return $request;
    }

    public function httpHeaders()
    {
        return [
            'Authorization: Bearer ' . $this->apiKey,
        ];
    }

    public function endpoint()
    {
        return 'https://api.sendgrid.com/api/mail.send.json';
    }

    public function verifyResponse($response)
    {
        $result = json_decode($response);

        return $result !== null && $result->message === 'success';
    }
}
