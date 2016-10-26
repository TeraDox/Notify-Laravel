<?php

return [
    'default' => 'slack',


    'mail' => [
        'address' => env('MAIL_USERNAME'),
        'name' => 'Mail Test Bot',
        'subject' => 'Sending a Message From Bot!'
    ],

    /*
    * active value. we use the adapter if it is 1, we don't if it is 0. default value is 0.
    */
    'active' => [
        'slack' => env('MESSAGE_NOTIFY_SLACK', '0'),
        'mail' => env('MESSAGE_NOTIFY_EMAIL', '0')
    ],

];