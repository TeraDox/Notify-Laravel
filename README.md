# Notify-Laravel
A simple PHP package for sending notifications from laravel to [Slack](https://slack.com) with [incoming webhooks](https://my.slack.com/services/new/incoming-webhook) or email.
This package will handle an instance of exception object and string text message.
While sending an exception as an message, it will attach an information of "user agent" and "request uri".
For Laravel, use this class in a Exceptions\Handler.php class.

## Requirements

* PHP >=5.6.4
* laravel/framework 5.3.
* maknz/slack ^1.7

## Installation

There are 4 steps to use this package.

1. Use composer to install this package.

```
composer require tdx-rikeda/notify-laravel
```

2. Add 'provider' and 'alias' for config\app.php.
```
'providers' => [ ...
        Maknz\Slack\SlackServiceProvider::class,
        Notify\Laravel\NotifyServiceProvider::class, ...
        ],
        
'aliases' => [ ...
        'Slack' => Maknz\Slack\Facades\Slack::class,
        'Notify' => Notify\Laravel\Facades\Notify::class, ...
        ],
```

3. Publish necessary config and view files.
```
php artisan vendor:publish
```

4. [Create an incoming webhook](https://my.slack.com/services/new/incoming-webhook) on your Slack account.


## Settings
Write values for some config files.

In config\slack.php,
```
'endpoint'
'channel'
'username'
```

In .env, set suitable values for mail,
```
MAIL_DRIVER=
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
```

and add
```
MESSAGE_NOTIFY_SLACK=1
MESSAGE_NOTIFY_MAIL=1
```

## How to send Messages
* Sending messages from Facade.
```
Notify::send($exception); // sends an exception with default setting.
Notify::send($text); // sends string with default setting.
Notify::send($exceptionOrText, $options, 'slack'); // keys of options array for Slack =['from', 'to', 'icon'] 
Notify::send($exceptionOrText, $options, 'mail'); // keys of options array for Mail =['from', 'to', 'subject'] 

```

* Sending messages from Instance.

```
$notify = new Notify(); // instance of Notify with default setting.
$notify->setTo($address);
$notify->setFrom($username);
$notify->setAdapter($slackOrMail);
$notify->send($exceptionOrText);
Notify::send($text, $options, 'slack'); 
```
