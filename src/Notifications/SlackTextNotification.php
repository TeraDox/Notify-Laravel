<?php
namespace Notify\Laravel\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class SlackTextNotification extends Notification
{
    use Queueable;

    /** @var string */
    private $content;

    /** @var array */
    private $options;

    /**
     * @param string $content
     */
    public function __construct(string $content, array $options = [])
    {
        $this->content = $content;
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
        $content = $this->content;
        if(strlen($content) > 3000) {
            $content = substr($content, 0, 3000);
            $content = $content . " ... ----- TEXT IS LIMITED TO 3000 CHARS-----";
        }
        if (isset($this->options['mention'])) {
            $mention = $this->options['mention'] . " ";
        }

        $content = (isset($this->options['raw']) && $this->options['raw']) ? $content : "```" . $content . "```";
        $content = $mention . $content;

        $slackMessage = (new SlackMessage)->linkNames()->content($content);
        if ($this->options['icon']) {
            $slackMessage->from($this->options['from'], $this->options['icon']);
        } else {
            $slackMessage->from($this->options['from']);
        }

        return $slackMessage;
    }
}
