<?php

namespace App\Http\Livewire;

use App\Models\Webhook;
use Livewire\Component;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WebhookEditor extends Component
{
    public $url = '';
    public $name = '';
    public $isDefault = false;
    public $newMessage = '';
    public $newUrl = '';
    public $svgData = '';
    public $createUrlShortcode = '';

    protected $rules = [
        'url' => 'required|url',
        'name' => 'required|string|max:255|unique:webhooks,name',
        'isDefault' => 'required|boolean',
    ];

    public function render()
    {
        return view('livewire.webhook-editor', [
            'webhooks' => Webhook::orderBy('name')->get(),
        ]);
    }

    public function updatedCreateUrlShortcode()
    {
        $this->newMessage = '';
        $this->newUrl = route('api.help') . '?c=' . $this->createUrlShortcode . '&btext=' . base64_encode($this->newMessage);
    }

    public function updatedNewMessage($value)
    {
        $this->newUrl = route('api.help') . '?c=' . $this->createUrlShortcode . '&btext=' . base64_encode($this->newMessage);
    }

    public function addWebhook()
    {
        $this->validate();

        Webhook::createNew($this->url, $this->name, $this->isDefault);

        $this->reset();
    }

    public function deleteWebhook($webhookId)
    {
        if (! is_numeric($webhookId)) {
            return;
        }

        $webhook = Webhook::findOrFail($webhookId);
        $webhook->delete();

        $this->reset();
    }

    public function downloadSvg()
    {
        return response()->streamDownload(function () {
            echo QrCode::size(1024)->generate($this->newUrl);
        }, Str::snake($this->createUrlShortcode . $this->newMessage) . '.svg');
    }
}
