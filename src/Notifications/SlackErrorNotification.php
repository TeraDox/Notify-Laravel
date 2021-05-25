<?php
namespace Notify\Laravel\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class SlackErrorNotification extends Notification
{
    use Queueable;

    /** @var \Throwable */
    private $e;

    /** @var array */
    private $options;

    /**
     * @param \Throwable $exception
     */
    public function __construct(\Throwable $exception, array $options = [])
    {
        $this->e = $exception;
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function via()
    {
        return ['slack'];
    }

    /**
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack()
    {
        $className = get_class($this->e);
        $content = config('notify.slack.mention') . " *{$className}* in `{$this->e->getFile()}` line: {$this->e->getLine()}";
        $trace = $this->e->getTraceAsString();
        if(strlen($trace) > 1000) {
            $trace = substr($trace, 0, 1000);
            $trace = $trace . " ... ----- TRACE IS LIMITED TO 1000 CHARS -----";
        }

        $slackMessage = (new SlackMessage)->error()
            ->to($this->options['to'])
            ->linkNames()
            ->content($content)
            ->attachment(function ($attachment)
            {
                $attachment->markdown(['text', 'fields']);

                $trace = str_replace(base_path(), '', $this->e->getTraceAsString());
                $trace = substr($trace, 0, 2000);
                $attachment->title($this->e->getMessage())->content($trace);

                if (!app()->runningInConsole()) {
                    $attachment->field(function ($field) {
                        $field->title("REQUEST_URI")->content(request()->fullUrl())->long();
                    });
                    $attachment->field(function ($field) {
                        $field->title("HTTP_USER_AGENT")->content(request()->userAgent())->long();
                    });
                    $attachment->field(function ($field) {
                        $field->title("IP_ADDRESS")->content(request()->ip())->long();
                    });
                }
            });
        if (isset($this->options['from'])) {
            if (isset($this->options['icon'])) {
                $slackMessage->from($this->options['from'], $this->options['icon']);
            } else {
                $slackMessage->from($this->options['from']);
            }
        }

        return $slackMessage;
    }
}
