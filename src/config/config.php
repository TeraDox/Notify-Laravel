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
     * Active value. we use the adapter if it is true, we don't if it is false.
     * default value is false (An adapter is turned off if it is not specified in .env file).
     */
    'active' => [
        'slack' => env('NOTIFY_SLACK', false),
        'mail' => env('NOTIFY_EMAIL', false)
    ],

    /**
     * mention will be inserted at the beginning of contents. (e.g.)@here
     */
    'mention' => env('NOTIFY_SLACK_MENTION', ''),

];
