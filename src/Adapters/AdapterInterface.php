<?php

namespace Notify\Laravel\Adapters;


interface AdapterInterface
{

    function send($content, $options);

    function setTo($address);

    function setFrom($name);

}