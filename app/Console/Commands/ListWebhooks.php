<?php

namespace App\Console\Commands;

use App\Models\Webhook;
use Illuminate\Console\Command;

class ListWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List the current webhooks';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $webhooks = Webhook::orderBy('name')->get();
        $this->table(['ID', 'Name', 'Shortcode', 'URL', 'Default?'], $webhooks->map(function ($webhook) {
            return [
                $webhook->id,
                $webhook->name,
                $webhook->shortcode,
                $webhook->url,
                $webhook->is_default ? 'Yes' : 'No',
            ];
        }));

        return 0;
    }
}
