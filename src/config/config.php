<?php

return [
    'default' => 'slack',


    'mail' => [
        'address' => env('MAIL_USERNAME'),
        'name' => env('MESSAGE_NAME'),
        'subject' => 'This is Subject!'
    ],

    /*
    * active value. we use the adapter if it is 1, we don't if it is 0. default value is 0.
    */
    'active' => [
        'slack' => env('MESSAGE_NOTIFY_SLACK', '0'),
        'mail' => env('MESSAGE_NOTIFY_EMAIL', '0')
    ],

];