<?php

namespace Notify\Laravel\Adapters;

use Illuminate\Support\Facades\Mail;
use Notify\Laravel\Exception\NotifyException;


class MailAdapter implements AdapterInterface
{

    protected $options; // array of options. keys = ['from', 'to', 'subject, 'fields']

    /**
     * MailAdapter constructor.
     * Initialize values from config file.
     * @param $options
     */
    function __construct($options)
    {
        if($this->isCorrect(config('notify.mail.address'))){
            $options['to'] = config('notify.mail.address');
        }
        $options['from'] = config('notify.mail.name');
        $options['subject'] = config('notify.mail.subject');
        $this->options = $options;
    }


    /**
     * Send content with specified options via email.
     * If there is no options specified, use one that is already specified. (at least default)
     * @param $content
     * @param $options
     * @throws NotifyException
     */
    function send($content, $options)
    {
        if(isset($options['to'])){
            $this->setTo($options['to']);
        }

        if (!$options) {
            $options = $this->options;
        } else {
            foreach ($this->options as $key => $value) {
                if (!key_exists($key, $options)) {
                    $options[$key] = $this->options[$key];
                }
            }
        }
        if($content instanceof \Exception) {
            // exception
            $data = $this->exceptionMessage($content);
            if (isset($options['fields'])) {
                $data['userAgent'] = $options['fields'][0];
                $data['requestUri'] = $options['fields'][1];
            }
        } else {
            // text message
            // if text is greater than 3000 chars, cut them at 3000 chars.
            if (strlen($content) > 3500) {
                $content = substr($content, 0, 3500);
                $content = $content . " ... ----- TEXT IS LIMITED TO 3500 CHARS-----";
            }
            $content = explode("\n", $content);
            $data['text'] = $content;
        }

        try {
            // send email
            Mail::send('vendor.notify-laravel.mail', $data, function ($message) use ($options) {
                $message->from(env('MAIL_USERNAME'), $options['from'])->to($options['to'])->subject($options['subject']);
            });
        } catch (\Exception $exception) {
            throw new NotifyException($exception);
        }




    }

    /**
     * Handles an exception object and returns as a message array.
     * @param $exception
     * @return array
     */
    private function exceptionMessage(\Exception $exception)
    {
        $className = get_class($exception);
        if($exception instanceof NotifyException) {
            $className = "NotifyException";
        }
        $errorName = $className;
        $errorPlace = " in " . $exception->getFile() . " line: " . $exception->getLine() . "\n";
        $errorTitle = $exception->getMessage();
        $trace = $exception->getTraceAsString();
        if (strlen($trace) > 1000) {
            $trace = substr($trace, 0, 1000);
            $trace = $trace . " ... ----- TRACE IS LIMITED TO 1000 CHARS -----";
        }
        $trace = explode("\n", $trace);
        $data = ['errorName' => $errorName,
            'errorPlace' => $errorPlace,
            'errorTitle' => $errorTitle,
            'trace' => $trace];
        return $data;

    }

    /**
     * Returns true if $to is in a correct format, false if it is not.
     * @param $to
     * @return bool
     */
    private function isCorrect($to)
    {
        $validator = \Validator::make(['email' => $to], ['email' => 'required|email']);
        if ($validator->fails()) {
            return false;
        }
        return true;

    }


    /**
     * Set new address
     * @param $address
     * @throws NotifyException
     */
    function setTo($address)
    {
        if ($this->isCorrect($address)) {
            $this->options['to'] = $address;
        } else {
            throw new NotifyException(new \Exception("Input Address is in a Wrong Format."));
        }
    }

    /**
     * Set new name
     * @param $name
     */
    function setFrom($name)
    {
        $this->options['from'] = $name;
    }

    /**
     * Set new subject
     * @param $subject
     */
    function setSubject($subject)
    {
        $this->options['subject'] = $subject;
    }

}