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
        'slack' => env('MESSAGE_NOTIFY_SLACK', false),
        'mail' => env('MESSAGE_NOTIFY_EMAIL', false)
    ],

];
