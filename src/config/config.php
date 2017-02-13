<?php

return [
    /**
     * Default adapter. e.g.'slack' or 'mail'
     */
    'default' => 'slack',


    /**
     * Settings for mail
     */
    'mail' => [
        'address' => env('MAIL_USERNAME'),
        'name' => 'MailTestBot',
        'subject' => 'This is Subject!'
    ],

    /**
     * Active value. we use the adapter if it is 1, we don't if it is 0.
     * default value is 0 (An adapter is turned off if it is not specified in .env file).
     */
    'active' => [
        'slack' => env('NOTIFY_SLACK', '0'),
        'mail' => env('NOTIFY_EMAIL', '0')
    ],

    /**
     * mention will be inserted at the beginning of contents. (e.g.)@here
     */
    'mention' => env('NOTIFY_SLACK_MENTION', ''),

];
