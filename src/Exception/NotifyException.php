<?php

namespace Notify\Laravel\Exception;

class NotifyException extends \Exception
{
    /**
     * get only message and error code from previous exception
     * NotifyException constructor.
     * @param \Throwable $previous
     */
    function __construct(\Throwable $previous)
    {
        parent::__construct($previous->getMessage(), $previous->getCode());
    }
}
