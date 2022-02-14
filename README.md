# Send Grid Plugin #

## Description ##

This plugin sends emails through SendGrid using their API.

## Installation ##

### Dependencies ###

This plugin is for phplist 3.3.2 or later and requires php version 5.6 or later.

It also requires CommonPlugin version to be enabled, and the php curl extension to be installed.

### Install through phplist ###

Install through the Plugins page (menu Config > Manage Plugins) using the package
URL `https://github.com/bramley/phplist-plugin-sendgrid/archive/master.zip`.
The installation should create

* the file SendGridPlugin.php
* the directory SendGridPlugin

##Usage##

For guidance on using the plugin see the plugin's page within the phplist documentation site <https://resources.phplist.com/plugin/sendgrid>

## Support ##

Please raise any questions or problems in the user forum <https://discuss.phplist.org/>.

## Donation ##

This plugin is free but if you install and find it useful then a donation to support further development is greatly appreciated.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W5GLX53WDM7T4)

## Version history ##

    version     Description
    1.4.1+20220214  Handle unsubscribe events in the webhook
    1.4.0+20220214  Add webhook to process bounces
    1.3.1+20190213  Ensure that multi-curl calls are completed
    1.3.0+20190129  Use the SendGrid Web API v3
    1.2.0+20181130  Use the phpList\plugin\Common\MailSender class to send emails
    1.1.0+20170213  Integrate with phplist 3.3.0 to send emails
    1.0.0+20160615  Initial release
