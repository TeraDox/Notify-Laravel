# Notify-Laravel
A simple PHP package for sending notifications from laravel via [Slack](https://slack.com) with [incoming webhooks](https://my.slack.com/services/new/incoming-webhook) or via email.
This package will automatically format an instance of exception object, string text, or an array for a message.
While sending an exception as a message, it will attach an information of "user agent", "request uri", and "ip address.
For Laravel, using this class in a Exceptions\Handler.php class is prefered.


## Requirements

* PHP ^8.3
* laravel/framework ^13.0
* laravel/slack-notification-channel ^3.9

## Installation
### 1. Use composer to install this package.

```
composer require tdx/notify-laravel
```

### 2. Add 'provider' and 'alias' for config\app.php. (No need above Laravel 5.5. Thanks to Auto-Discovery feature! YaY)
```
'providers' => [ ...
        Notify\Laravel\NotifyServiceProvider::class,
        ...],
        
'aliases' => [ ...
        'Notify' => Notify\Laravel\Facades\Notify::class,
        ...],
```

### 3. Publish necessary config and view files.
```
php artisan vendor:publish --tag='notify-laravel'
```
add `--force` option to overwrite previously published files.

These commands should create  
```
/config/notify.php,  
/resources/views/vendor/notify-laravel/mail.blade.php
```


If these publish commands does not work, try 
```
php artisan config:clear
```
It will clear the config cache.


### 4. [Create an incoming webhook](https://my.slack.com/services/new/incoming-webhook) on your Slack account. You need to set the Webhook URL to `NOTIFY_SLACK_ENDPOINT` in .env file to send a message via Slack.


## Settings
Settings are read from config/notify.php, which refers to the values in .env file.

### In .env, set suitable values for Slack,
```
NOTIFY_SLACK_ENDPOINT= (e.g.) https://hooks.slack.com/services/xxx/yyy/zzz  // webhook URL for your incoming webhook
NOTIFY_SLACK_CHANNEL= (e.g.) "#general"  // channel or username where you want to send a message. empty for default
NOTIFY_SLACK_USERNAME= (e.g.) Robot  // username that is going to be displayed on the message. empty for default
NOTIFY_SLACK_ICON= (e.g.) :smile:  // icon URL or emoji. empty for default
```
Values starting with `#` must be quoted (e.g. `"#general"`). Otherwise they are treated as comments and become empty.

### In .env, set suitable values for mail,
```
MAIL_MAILER= (e.g.) smtp
MAIL_HOST= (e.g.) smtp.gmail.com
MAIL_PORT= (e.g.) 465
MAIL_USERNAME= (e.g.) YOUR_EMAIL_ADDRESS
MAIL_PASSWORD= (e.g.) YOUR_EMAIL_PASSWORD
MAIL_ENCRYPTION= (e.g.) ssl
MAIL_FROM_ADDRESS= (e.g.) YOUR_EMAIL_ADDRESS
MAIL_FROM_NAME= (e.g.) Notify
```

and add
```
NOTIFY_SLACK=(e.g.) true
NOTIFY_EMAIL=(e.g.) true
NOTIFY_SLACK_MENTION=(e.g.) @here
```
If the value = true, the adapter is turned on (The adapter can send a message). If the value = false, the adapter is turned off (The adapter cannot send a message). If there is no value defined in .env file, false is used as default.
Mention will be attached at the beginning of the content.

## How to send Messages
This class automatically formats and sends an message. The content can be an exception object, string, or an array.

* Sending messages from Facade.
```
\Notify::send($content); // sends an exception with default setting.
\Notify::send($content, $options, 'slack'); // keys of options array for Slack =['from', 'to', 'icon', 'fields', 'max_retry', 'force']
\Notify::send($content, $options, 'mail'); // keys of options array for Mail =['from', 'to', 'subject', 'fields', 'max_retry', 'force'] 
\Notify::force($content); // force method forces to send the content regardless of what the active value is.
\Notify::send($content, ['mention' => '@here']); // sends an exception with mention.

```

* Sending messages from Instance.

```
$notify = new \Notify\Laravel\Notify(); // instance of Notify with default setting.
$notify->setTo($address); // change address. (channel or username for slack)
$notify->setFrom($username); // change username on the message.
$notify->setAdapter($adapter_name); // set adapter to 'slack' or 'mail'
$notify->send($content); // send message
// or use $notify->force($content) to force to send.
```

## Options for Adapters
For SlackAdapter,  
  
Parameter | Type | Description
----- | ---- | -----------
`to` | string | channel or username that messages is going to be sent to.
`from` | string | username for the message.
`icon` | string | The icon URL or stamp string. (e.g.) `:smile:`
`fields` | array | has UserAgent and RequestUri if there exist.
`max_retry` | bool | maximum number of retries. (default `max_retry = 3`)
`force` | bool | forces to send if it is true. Otherwise, do not force (follows to config/active values).
`mention` | string | mention is attached at the beginning of the content. (e.g.) '@channel'
`raw` | bool | send text without formatting if it is true.

For MailAdapter,  
  
Parameter | Type | Description
----- | ---- | -----------
`to` | string | email address that messages is going to be sent to.
`from` | string | username for the email. This does not need to be actual email address.
`subject` | string | subject for the email.
`fields` | array | has UserAgent and RequestUri if there exist.
`max_retry` | bool | maximum number of retries. (default `max_retry = 3`)
`force` | bool | force to send if it is true. Otherwise, do not force (follow to active values).


## Example of Implementation using Laravel Exception Handler

In bootstrap/app.php,
```
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Facades\Log;
use Notify\Laravel\Exception\NotifyException;

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Exception $exception) {
            try {
                // Use Notify class here.
                \Notify::send($exception);  // Send with default settings.
            } catch (NotifyException $ne) {
                Log::error($ne);
                try {
                    // send via mail. (Another way to send a notification if first one failed.)
                    // Use a new instance so that the default adapter of the Facade is not changed to 'mail'.
                    (new \Notify\Laravel\Notify())->send($ne, ['to' => 'YOUR_EMAIL_ADDRESS'], 'mail');
                } catch (\Throwable $e2) {
                    // Problem of mail settings.
                    Log::error($e2);
                }
            } catch (\Throwable $e) {
                // Notify class should throw only NotifyException, but just in case, catch others here.
                Log::error($e);
            }
            // Don't call report() here. It calls this callback again and causes a loop.
        });
    })
```
This example notifies only `\Exception` and its subclasses (e.g. `RuntimeException`). `\Error` such as `TypeError` is not notified by this example, but it is still reported to laravel.log as usual.

The exception is also reported to laravel.log as usual after this callback.
If anything is thrown from this callback, Laravel stops reporting and the original exception is not logged. So catch `\Throwable` in every path, including the fallback.

The mail adapter uses the from address of the mailer (`mail.mailers.<name>.from`, or `mail.from` if not set), and the `from` option is used as the display name.
    
## How to Create Other Adapter

1. Create a class which implements AdapterInterface in an Adapters folder.  
2. Name the class to xxxAdapter. xxx will be an adapter name that is going to be called.  
3. Modify config/config.php file to define default values for the adapter.  


## Notes
If an adapter failed to send a message, it will automatically retry to send it. Write ['max_retry' = SOME_NUMBER] in the options array to change the number of attempts (default max_retry = 3). If all attempts failed, it will throw NotifyException. To get more specific info about the error, you should check laravel.log file (The log file captures errors for all attempts).
