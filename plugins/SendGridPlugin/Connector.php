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
namespace phpList\plugin\SendGridPlugin;

class Connector
{
    /** @var string API key */
    private $apiKey;

    /** @var bool enable ssl cert verification */
    private $sslVerify;

    /** @var int request timeout period*/
    private $timeout;

    /** @var string user agent */
    const USER_AGENT = 'phplist';

    /**@var string sendgrid endpoint */
    const SG_ENDPOINT = 'https://api.sendgrid.com/api/';

    /**
     * Constructor.
     *
     * @param string $apiKey    the API key to use
     * @param bool   $sslVerify set false to disable CURL ssl cert verification
     * @param int    $timeout   timeout period
     */
    public function __construct($apiKey, $sslVerify = true, $timeout = 20)
    {
        $this->apiKey = $apiKey;
        $this->sslVerify = $sslVerify;
        $this->timeout = $timeout;
    }

    /**
     * Make an API call using curl.
     * 
     * @param string $action   the API module.action
     * @param array  $postData the content of the post
     * @param string $method   the http method to be used 
     *
     * @return array the API return structure
     */
    public function makeApiCall($action, $postData, $method = 'POST')
    {
        $headers = array('Authorization: Bearer ' . $this->apiKey);
        $session = curl_init(self::SG_ENDPOINT . $action . '.json');

        if (!$this->sslVerify) {
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($session, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($session, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($session, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        $jsonResponse = curl_exec($session);
        curl_close($session);

        return json_decode($jsonResponse, true);
    }
}
