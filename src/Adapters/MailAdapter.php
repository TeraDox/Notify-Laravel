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
        $options['to'] = config('notify.mail.address');
        $options['from'] = config('notify.mail.name');
        $options['subject'] = config('notify.mail.subject');
        $this->options = $options;
    }


    /**
     * Send content with specified options via email.
     * If there is no options specified, use one that is already specified. (at least default)
     * @param mixed $content content that is going to be sent
     * @param array $options array of options. keys = ['from', 'to', 'subject, 'fields', 'max_retry', 'force']
     * @throws NotifyException
     */
    function send($content, $options)
    {
        if (!$options) {
            $options = $this->options;
        } else {
            if (isset($options['to'])) {
                $this->setTo($options['to']);
            }
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
                $data['ipAddress'] = $options['fields'][2];

            }
        } else {
            // text message
            // if text is greater than 3500 chars, cut them at 3500 chars.
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
     * @param $to email address where the message is going to be sent
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
     * @param $address address where the message is going to be sent
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
     * @param $name name that is going to be displayed in the message
     */
    function setFrom($name)
    {
        $this->options['from'] = $name;
    }

    /**
     * Set new subject
     * @param $subject subject for the email
     */
    function setSubject($subject)
    {
        $this->options['subject'] = $subject;
    }

}