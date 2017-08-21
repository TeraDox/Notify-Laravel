<?php
namespace Notify\Laravel;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client as Guzzle;
use Maknz\Slack\Client;

class SlackServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {}

    /**
     * Register the application services.
     * @return void
     */
    public function register()
    {
    	if ( file_exists(config_path('slack.php'))) {
		    $this->mergeConfigFrom( config_path( 'slack.php' ), 'slack' );

            $this->app->singleton('maknz.slack', function ($app) {
                return new Client(
                    $app['config']->get('slack.endpoint'),
                    [
                        'channel' => $app['config']->get('slack.channel'),
                        'username' => $app['config']->get('slack.username'),
                        'icon' => $app['config']->get('slack.icon'),
                        'link_names' => $app['config']->get('slack.link_names'),
                        'unfurl_links' => $app['config']->get('slack.unfurl_links'),
                        'unfurl_media' => $app['config']->get('slack.unfurl_media'),
                        'allow_markdown' => $app['config']->get('slack.allow_markdown'),
                        'markdown_in_attachments' => $app['config']->get('slack.markdown_in_attachments')
                    ],
                    new Guzzle
                );
            });
	    } else {
            $provider = new \Maknz\Slack\SlackServiceProvider($this->app);
            $provider->boot();
        }


        $this->app->bind(\Maknz\Slack\Client::class, 'maknz.slack');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['maknz.slack'];
    }
}
