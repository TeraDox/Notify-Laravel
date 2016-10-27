# Notify-Laravel
A simple PHP package for sending notifications from laravel via [Slack](https://slack.com) with [incoming webhooks](https://my.slack.com/services/new/incoming-webhook) or via email.
This package will automatically format an instance of exception object, string text, or an array for a message.
While sending an exception as a message, it will attach an information of "user agent" and "request uri".
For Laravel, using this class in a Exceptions\Handler.php class is prefered.

## Requirements

* PHP >=5.6.4
* laravel/framework 5.3.
* maknz/slack ^1.7

## Installation

There are 4 steps to use this package.

*1. Use composer to install this package.

```
composer require tdx/notify-laravel
```

*2. Add 'provider' and 'alias' for config\app.php.
```
'providers' => [ ...
        Maknz\Slack\SlackServiceProvider::class,
        Notify\Laravel\NotifyServiceProvider::class,
        ...],
        
'aliases' => [ ...
        'Slack' => Maknz\Slack\Facades\Slack::class,
        'Notify' => Notify\Laravel\Facades\Notify::class,
        ...],
```

*3. Publish necessary config and view files.
```
php artisan vendor:publish
```
OR, 
If you want to publish only related files for this package,
```
php artisan vendor:publish --tag='notify-laravel'
php artisan vendor:publish --provider="Maknz\Slack\SlackServiceProviderLaravel5"
// add --force option to overwrite previously published files.
```

These commands should create  
/config/slack.php,  
/config/notify.php,  
/resources/views/vendor/notify/mail.blade.php



If these publish commands does not work, try 
```
php artisan config:clear
```
It will clear the config cache.


*4. [Create an incoming webhook](https://my.slack.com/services/new/incoming-webhook) on your Slack account. You need to write Webhook URL in config\slack.php file to send a message via Slack. 


## Settings
Write values for some config files.

In config\slack.php,
```
'endpoint'= (e.g.) 'https://hooks.slack.com/services/xxx/yyy/zzz' //webhook URL for your incoming webhook
'channel'= (e.g.) '#general' // channnel or username where you want to send a message
'username'= (e.g.) 'Robot' // username that is going to be displayed on the message
```

In .env, set suitable values for mail,
```
MAIL_DRIVER= (e.g.) smtp
MAIL_HOST= (e.g.) smtp.gmail.com
MAIL_PORT= (e.g.) 465
MAIL_USERNAME= (e.g.) YOUR_EMAIL_ADDRESS
MAIL_PASSWORD= (e.g.) YOUR_EMAIL_PASSWORD
MAIL_ENCRYPTION= (e.g.) ssl
```

and add
```
MESSAGE_NOTIFY_SLACK=1
MESSAGE_NOTIFY_MAIL=1
```
If the value = 1, the adapter is turned on (The adapter can send a message). If the value = 0, the adapter is turned off (The adapter cannot send a message). If there is no value defined in .env file, 0 is used as default.


## How to send Messages
This class automatically formats and sends an message. The content can be an exception object, string, or an array.

* Sending messages from Facade.
```
\Notify::send($content); // sends an exception with default setting.
\Notify::send($content, $options, 'slack'); // keys of options array for Slack =['from', 'to', 'icon', 'endpoint', 'fields', 'max_retry', 'force']
\Notify::send($content, $options, 'mail'); // keys of options array for Mail =['from', 'to', 'subject', 'fields', 'max_retry', 'force'] 
\Notify::force($content); // force method forces to send the content regardless of what the active value is.

```

* Sending messages from Instance.

```
$notify = new \Notify\Laravel\Notify(); // instance of Notify with default setting.
$notify->setTo($address); // change address. (channel or userid for slack)
$notify->setFrom($username); // change username on the message.
$notify->setAdapter($adapter_name); // set adapter to 'slack' or 'mail'
$notify->send($content); // send message
// or use $notify->force($content) to force to send.
```

## Example of Implementation using Laravel Exception Handler

In App\Exceptions\Handler class,
```
use Notify\Laravel\Exception\NotifyException;

    public function report(Exception $exception)
    {
        parent::report($exception);

        try {
            try {
                // Use Notify class here.
                \Notify::send($exception);  // Send with default settings.

            } catch (NotifyException $ne) {
                try {
                    // send via mail. (Another way to send a notification if first one failed.)
                    \Notify::send($ne, ['to' => 'YOUR_EMAIL_ADDRESS'], 'mail');
                    parent::report($ne);
                } catch (NotifyException $ne2) {
                    // Problem of mail settings. Dont't use Notify class here to avoid loop.
                    parent::report($ne2);
                }
            }
        } catch (Exception $e) {
            // Notify class should throw only NotifyException, but just in case, catch other Exception here to avoid loop.
            parent::report($e);
        }
    }
```
    
##How to Create Other Adapter

1. Create a class which implements AdapterInterface in an Adapters folder.  
2. Name the class to xxxAdapter. xxx will be an adapter name that is going to be called.  
3. Modify config/config.php file to define default values for the adapter.  


##Notes
If an adapter failed to send a message, it will automatically retry to send it. Write ['max_retry' = SOME_NUMBER] in the options array to change the number of attempts (default max_retry = 3). If all attempts failed, it will throw NotifyException. To get more specific info about the error, you should check laravel.log file (The log file captures errors for all attempts).
