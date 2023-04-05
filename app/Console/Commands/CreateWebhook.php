<?php

namespace App\Console\Commands;

use App\Models\Webhook;
use Illuminate\Console\Command;

class CreateWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:create {name?} {url?} {default?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new webhook';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (! $name = $this->argument('name')) {
            $name = $this->ask('Name?');
        }
        if (! $url = $this->argument('url')) {
            $url = $this->ask('URL?');
        }
        $default = (bool) $this->argument('default');

        $existing = Webhook::where('url', $url)->first();
        if ($existing) {
            $this->error('Webhook URL already exists.');

            return 1;
        }
        $existing = Webhook::where('name', $name)->first();
        if ($existing) {
            $this->error('Webhook name already exists.');

            return 1;
        }

        $webhook = Webhook::createNew($url, $name, $default);

        $this->info('Webhook created - shortcode is '.$webhook->shortcode);

        return 0;
    }
}
