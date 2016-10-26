<?php

namespace Notify\Laravel;


use Maknz\Slack\Facades\Slack;
use Notify\Laravel\Adapters\SlackAdapter;
use Notify\Laravel\Exception\NotifyException;


class Notify
{
    protected $adapter; // adapter that is going to be used to send message. (e.g. email or slack)
    protected $options; // array of options.
    protected $adapterName; // 'slack' or 'mail'


    /**
     * Notify constructor.
     * @param string $adapter name of adapter
     */
    function __construct($adapter = "")
    {
        $options = [];
        // get info from server
        $serverInfo = request()->capture()->server;
        $userAgent = $serverInfo->get("HTTP_USER_AGENT");
        $requestUri = $serverInfo->get("REQUEST_URI");
        if (isset($userAgent) && isset($requestUri)) {
            $fields = [$userAgent, $requestUri];
            $options['fields'] = $fields;
        }
        // set adapter
        $adapterName = config('notify.default');
        $adapter = $adapter ?: $adapterName;
        $this->options = $options;
        $this->adapter = $this->createAdapter($adapter, true);
        $this->adapterName = $adapterName;

    }

    /**
     * Creates and returns an adapter of given name.
     * @param $name
     * @param bool $isDefault true if it creates an adapter with default settings
     * @return mixed
     * @throws NotifyException
     */
    private function createAdapter($name, $isDefault =false) {
        $className = "Notify\\Laravel\\Adapters\\" . ucfirst($name) . "Adapter";
        if (class_exists($className)){
            $adapter = new $className($this->options);
            $this->adapter = $adapter;
            $this->adapterName = $name;
            return $adapter;
        } else {
            if($isDefault) {
                $default = "Default ";
            } else {
                $default = "";
            }
            throw new NotifyException(new \Exception($default . 'Input Adapter Name is in a Wrong Format.'));
        }
    }

    /**
     * force to send content regardless of what the active value (in config/notify.php) is.
     * @param $content
     * @param array $options
     * @param string $adapter
     * @return bool
     */
    public function force($content, $options = [], $adapter = "")
    {
        $options['force'] = true;
        return $this->send($content, $options, $adapter);
    }

    /**
     * Send content to an adapter with options. Automatically converts array to string.
     * For SlackAdapter, keys of options = ['from', 'to', 'icon', 'endpoint', 'fields']
     * For MailAdapter, keys of options = ['from', 'to', 'subject, 'fields']
     * @param mixed $content content that is going to be sent
     * @param array $options options for adapter
     * @param string $adapter name of adapter that is going to be used to sent a content
     * @return boolean
     */
    public function send($content, $options = [], $adapter = "")
    {
        $adapter = $adapter ? $this->createAdapter($adapter) : $this->adapter;
        $this->adapter = $adapter;
        $force = (isset($options['force']) && $options['force'] == true) ? true : false;
        if (!$force && !config('notify.active.' . $this->adapterName)) {
            return false;
        }

        if (is_array($content)) {
           $content = print_r($content, true);
        }

        if (isset($options['max_retry']) && is_numeric($options['max_retry'])) {
            $this->sendWithRetry($content, $options, $adapter, $options['max_retry']);
        } else {
            $this->sendWithRetry($content, $options, $adapter);
        }

        return true;
    }


    /**
     * Repeatedly sends a content unless it correctly sends a content.
     * @param $adapter
     * @param $content
     * @param $options
     * @param int $maxRetryCount
     * @param int $retryCount current number of retries.
     * @throws NotifyException
     */
    private function sendWithRetry($content, $options, $adapter, $maxRetryCount = 3, $retryCount = 0)
    {
        if ($retryCount == $maxRetryCount) {
            // retries are all failed. Throw NotifyException.
            throw new NotifyException(new \Exception("All Attempts Failed in " . get_class($adapter) . ". Max Retry Count : " . $maxRetryCount)); // return?
        } else {
            try {
                $adapter->send($content, $options);
            } catch (\Exception $exception) {
                $retryCount++;
//                sleep(2); // retry per 2 seconds

                $this->sendWithRetry($content, $options, $adapter, $maxRetryCount, $retryCount);

            }
        }
    }


    /**
     * Set new address. Throws NotifyException if address is wrong format.
     * @param $address
     */
    public function setTo($address) {
        $this->adapter->setTo($address);
    }

    /**
     * Set new name that is going to be displayed in the message.
     * @param $name
     */
    public function setFrom($name) {
        $this->adapter->setFrom($name);
    }

    /**
     * Set new adapter. Current available adapter is 'slack' or 'mail'.
     * @param $adapter name of adapter. (e.g.) 'slack' or 'mail'
     * @throws NotifyException
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $this->createAdapter($adapter);
    }


}
