<?php

namespace Notify\Laravel\Exception;

class NotifyException extends \Exception
{
    /**
     * get only message and error code from previous exception
     * NotifyException constructor.
     * @param \Exception $previous
     */
    function __construct(\Exception $previous)
    {
        parent::__construct($previous->getMessage(), $previous->getCode());
    }
}
