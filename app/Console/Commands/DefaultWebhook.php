<?php

namespace App\Console\Commands;

use App\Models\Webhook;
use Illuminate\Console\Command;

class DefaultWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:default {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a given webhook the default one';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (! $id = $this->argument('id')) {
            $name = $this->choice(
                'Which Webhook?',
                Webhook::pluck('name', 'id')->toArray()
            );
            $id = Webhook::where('name', $name)->first()->id;
        }

        $hook = Webhook::findOrFail($id);
        Webhook::where('is_default', true)->update(['is_default' => false]);
        $hook->update(['is_default' => true]);

        $this->info('Set default webhook to '.$hook->id.' : '.$hook->name);

        return 0;
    }
}
