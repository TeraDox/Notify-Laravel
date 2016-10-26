<?php

namespace Notify\Laravel\Adapters;


use Notify\Laravel\Exception\NotifyException;
use Maknz\Slack\Attachment;
use Maknz\Slack\AttachmentField;
use Maknz\Slack\Facades\Slack;

class SlackAdapter implements AdapterInterface
{
    protected $options; // array of options. keys = ['from', 'to', 'icon', 'endpoint', 'fields']

    /**
     * SlackAdapter constructor.
     * Initialize values from config file.
     * @param $options
     */
    function __construct($options)
    {
        $options['to'] = config('slack.channel');
        $options['endpoint'] = config('slack.endpoint');
        $options['from'] = config('slack.username');
        $options['icon'] = config('slack.icon');
        $this->options = $options;
    }

    /**
     * Send content with specified options via slack.
     * If there is no options specified, use one that is already specified. (at least default)
     * @param $content content that is going to be sent
     * @param $options array of options. keys = ['from', 'to', 'icon', 'endpoint', 'fields']
     * @throws NotifyException
     */
    function send($content, $options)
    {
        if (isset($options['to'])) {
            $this->setTo($options['to']);
        }

        $message = Slack::createMessage();

        if(!$options){
            $options = $this->options;
        } else {
            foreach($this->options as $key => $value) {
                if(!key_exists($key, $options)) {
                    $options[$key] = $this->options[$key];
                }
            }
        }

        $icon = $options['icon'];
        if($icon != null){
            // set icon if it is specified
            $message->withIcon($icon);
        }

        if($content instanceof \Exception) {
            // exception
            $fields = [];
            if (isset($options['fields'])) {
                $fields =
                    [new AttachmentField(['title' => 'HTTP_USER_AGENT', 'value' => $options['fields'][0]]),
                        new AttachmentField(['title' => 'REQUEST_URI', 'value' => $options['fields'][1]])];
            }
            $message = $this->exceptionMessage($message, $fields, $content);

        } else {
            // text message
            if(strlen($content) > 3000) {
                $content = substr($content, 0, 3000);
                $content = $content . " ... ----- TEXT IS LIMITED TO 3000 CHARS-----";
            }
            $message->setText("```" . $content . "```");
        }

        $message->from($options['from']);
        $message->to($options['to']);

        try {
            $message->send();

            // slack is limited to 1 message/second
            sleep(1);

        } catch (\Exception $exception) {
            throw new NotifyException($exception);
        }


    }

    /**
     * Handles an exception object and returns as a message array.
     * @param $message
     * @param $fields
     * @param \Exception $exception
     * @return mixed
     */
    private function exceptionMessage($message, $fields, \Exception $exception)
    {
        $className = get_class($exception);
        if ($exception instanceof NotifyException) {
            $className = "NotifyException";
        }

        $message->setText("*" . $className. "* in `" . $exception->getFile() . "` line: " . $exception->getLine());

        $trace = $exception->getTraceAsString();
        if(strlen($trace) > 1000) {
            $trace = substr($trace, 0, 1000);
            $trace = $trace . " ... ----- TRACE IS LIMITED TO 1000 CHARS -----";
        }

        $attachment = new Attachment([
            'color' => 'danger',
            'title' => $exception->getMessage(),
            'text' => $trace,

        ]);
        if ($fields) {
            $attachment->setFields($fields);
        }
        $message->attach($attachment);
        return $message;
    }

    /**
     * Returns true if $to is in a correct format, false if it is not.
     * @param $to
     * @return bool
     */
    private function isCorrect($to)
    {
        // useraccount start with '@', channel start with '#'
        if(preg_match('/^[\#\@]([a-zA-Z0-9\._-])+$/', $to)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * set new Icon(or stamp)
     * @param $url
     */
    function setIcon($url)
    {
        $this->options['icon'] = $url;
    }

    /**
     * set new channel(or user)
     * @param $channel
     * @throws NotifyException
     */
    function setTo($channel)
    {
        if ($this->isCorrect($channel)) {
            $this->options['channel'] = $channel;
        } else {
            throw new NotifyException(new \Exception("Input Channel is in a Wrong Format."));
        }
    }

    /**
     * set new username
     * @param $username
     */
    function setFrom($username)
    {
        $this->options['username'] = $username;
    }

}