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
 * @copyright 2016 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/**
 * Registers the plugin with phplist.
 */
class SendGridPlugin extends phplistPlugin
{
    /** @var SendGrid connector instance */
    private $sendgrid;

    /*
     *  Inherited variables
     */
    public $name = 'SendGrid Plugin';
    public $authors = 'Duncan Cameron';
    public $description = 'Use SendGrid to send emails';
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
     * Remove temporary files created for attachments.
     * 
     * @param array $temporaryFiles files to be removed
     */
    private function removeTemporaryFiles($temporaryFiles)
    {
        foreach ($temporaryFiles as $item) {
            unlink($item);
        }
    }

    /**
     * Create a temporary file for attachments.
     * 
     * @param string $content file content
     *
     * @return string the temporary file name
     */
    private function createTemporaryFile($content)
    {
        global $tmp;

        $tempName = tempnam($tmp, 'phplist');
        file_put_contents($tempName, $content);

        return $tempName;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->coderoot = dirname(__FILE__) . '/' . 'SendGridPlugin' . '/';
        parent::__construct();
    }

    /**
     * Send an email using SendGrid.
     *
     * @param PHPlistMailer $phpmailer mailer instance
     *
     * @return bool Whether the response code is 200
     */
    public function send(PHPlistMailer $phpmailer)
    {
        if ($this->sendgrid === null) {
            $this->sendgrid = new SendGrid(
                getConfig('sendgrid_api_key'),
                array('raise_exceptions' => false)
            );
        }
        $email = new SendGrid\Email();
        $to = $phpmailer->getToAddresses();
        $email
            ->addTo($to[0][0])
            ->setFrom($phpmailer->From)
            ->setFromName($phpmailer->FromName)
            ->setSubject($phpmailer->Subject)
        ;
        /*
         * for an html message both Body and AltBody will be populated
         * for a plain text message only Body will be populated
         */
        $isHtml = $phpmailer->AltBody != '';

        if ($isHtml) {
            $email
                ->setHtml($phpmailer->Body)
                ->setText($phpmailer->AltBody)
            ;
        } else {
            $email->setText($phpmailer->Body);
        }

        foreach ($phpmailer->getCustomHeaders() as $item) {
            $email->addheader($item[0], $item[1]);
        };
        $temporaryFiles = array();

        foreach ($phpmailer->getAttachments() as $item) {
            $tempName = $this->createTemporaryFile($item[0]);
            $temporaryFiles[] = $tempName;
            $email->addAttachment($tempName, $item[2]);
        };

        $response = $this->sendgrid->send($email);
        $this->removeTemporaryFiles($temporaryFiles);

        return $response->getCode() == 200;
    }
}
