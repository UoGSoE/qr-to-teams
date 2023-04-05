<?php

namespace App\Console\Commands;

use App\Models\Webhook;
use Illuminate\Console\Command;

class DeleteWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:delete {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a webhook';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $id = $this->argument('id')) {
            $name = $this->choice(
                'Which Webhook?',
                Webhook::pluck('name', 'id')->toArray()
            );
            $id = Webhook::where('name', $name)->first()->id;
        }

        $hook = Webhook::findOrFail($id);
        $hook->delete();

        $this->info('Deleted webhook '.$hook->id.' : '.$hook->name);

        return 0;
    }
}
