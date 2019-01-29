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

/**
 * This class implements the IMailClient interface to send emails through SendGrid.
 *
 * @see https://sendgrid.com/docs/API_Reference/api_v3.html
 */
class MailClient implements \phpList\plugin\Common\IMailClient
{
    /** @var string API key */
    private $apiKey;

    /**
     * Constructor.
     *
     * @param string $apiKey the API key to use
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function requestBody(\PHPlistMailer $phplistmailer, $headers, $body)
    {
        $to = $phplistmailer->getToAddresses();
        $request['personalizations'][0] = [
            'to' => [
                ['email' => $to[0][0]],
            ],
            'subject' => $phplistmailer->Subject,
        ];
        $request['from'] = [
            'email' => $phplistmailer->From,
            'name' => $phplistmailer->FromName,
        ];
        /*
         * for an html message both Body and AltBody will be populated
         * for a plain text message only Body will be populated
         */
        $isHtml = $phplistmailer->AltBody != '';

        if ($isHtml) {
            $request['content'][0] = [
                'type' => 'text/plain',
                'value' => $phplistmailer->AltBody,
            ];
            $request['content'][1] = [
                'type' => 'text/html',
                'value' => $phplistmailer->Body,
            ];
        } else {
            $request['content'][0] = [
                'type' => 'text/plain',
                'value' => $phplistmailer->Body,
            ];
        }

        foreach ($phplistmailer->getCustomHeaders() as $item) {
            $request['headers'][$item[0]] = $item[1];
        }

        foreach ($phplistmailer->getAttachments() as $i => $item) {
            list($content, $filename, $name, $encoding, $type, $isString, $disposition, $cid) = $item;
            $request['attachments'][$i] = [
                'content' => base64_encode($content),
                'type' => $type,
                'filename' => $filename,
                'disposition' => $disposition,
            ];

            if ($disposition == 'inline') {
                $request['attachments'][$i]['content_id'] = $cid;
            }
        }

        return json_encode($request);
    }

    public function httpHeaders()
    {
        return [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];
    }

    public function endpoint()
    {
        return 'https://api.sendgrid.com/v3/mail/send';
    }

    public function verifyResponse($response)
    {
        return true;
    }
}
