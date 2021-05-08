<?php

namespace Notify\Laravel\Adapters;

use Notify\Laravel\Exception\NotifyException;
use Notify\Laravel\Notifications\SlackErrorNotification;
use Notify\Laravel\Notifications\SlackTextNotification;
use Illuminate\Notifications\Notifiable;

class SlackAdapter implements AdapterInterface
{
    use Notifiable;

    /**
     * keys = ['from', 'to', 'icon', 'endpoint', 'fields', 'max_retry', 'force', 'mention' ]
     * @var array $options
     */
    protected $options;

    /**
     * SlackAdapter constructor.
     * Initialize values from config file.
     * @param array $options
     */
    function __construct($options)
    {
        $options['to'] = config('notify.slack.channel');
        $options['endpoint'] = config('notify.slack.endpoint');
        $options['from'] = config('notify.slack.username');
        $options['icon'] = config('notify.slack.icon');
        $options['mention'] = config('notify.slack.mention');
        $this->options = $options;
    }

    /**
     * Send content with specified options via slack.
     * If there is no options specified, use one that is already specified. (at least default)
     * @param mixed $content content that is going to be sent
     * @param array $options array of options. keys = ['from', 'to', 'icon', 'endpoint', 'fields', 'max_retry', 'force', 'mention']
     * @throws NotifyException
     */
    function send($content, $options)
    {
        if(!$options){
            $options = $this->options;
        } else {
            if (isset($options['to'])) {
                $this->setTo($options['to']);
            }
            foreach($this->options as $key => $value) {
                if(!key_exists($key, $options)) {
                    $options[$key] = $this->options[$key];
                }
            }
        }

        if ($content instanceof \Exception) {
            // exception
            $notification = new SlackErrorNotification($content, $options);
        } else {
            // text message
            $notification = new SlackTextNotification($content, $options);
        }

        try {

            $this->notify($notification);
            sleep(1); // slack is limited to 1 message/second

        } catch (\Exception $exception) {
            throw new NotifyException($exception);
        }
    }

    /**
     * Returns true if $to is in a correct format, false if it is not.
     * @param string $to channel or userId. channel start with '#', userId start with '@'.
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
     * set new channel(or user)
     * @param string $channel channel or userId where the message is going to be sent.
     * @throws NotifyException
     */
    function setTo($channel)
    {
        if ($this->isCorrect($channel)) {
            $this->options['to'] = $channel;
        } else {
            throw new NotifyException(new \Exception("Input Channel is in a Wrong Format."));
        }
    }

    /**
     * set new username
     * @param string $username name that is going to be displayed in the message.
     */
    function setFrom($username)
    {
        $this->options['from'] = $username;
    }

    private function routeNotificationForSlack()
    {
        return $this->options['to'];
    }
}
