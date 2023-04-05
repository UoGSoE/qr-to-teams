<?php

namespace App\Http\Livewire;

use App\Models\Webhook;
use Illuminate\Support\Str;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WebhookEditor extends Component
{
    public $url = '';

    public $name = '';

    public $isDefault = false;

    public $newMessage = '';

    public $newUrl = '';

    public $newForm = false;

    public $svgData = '';

    public $createUrlShortcode = '';

    public $newWebhookUrl = '';

    public $newWebhookName = '';

    public $newWebhookDefault = false;

    public $showCreateForm = false;

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
        $this->newUrl = $this->generateWebhookUrl();
        $this->showCreateForm = false;
    }

    public function updatedShowCreateForm()
    {
        $this->newMessage = '';
        $this->newForm = false;
        $this->createUrlShortcode = '';
    }

    public function updatedNewMessage($value)
    {
        $this->newUrl = $this->generateWebhookUrl();
    }

    public function updatedNewForm($value)
    {
        $this->newUrl = $this->generateWebhookUrl();
    }

    protected function generateWebhookUrl(): string
    {
        $maxUrlLength = 2000;
        $url = route('api.help').'?c='.$this->createUrlShortcode.'&etext='.encrypt($this->newMessage);
        if (strlen($url) > $maxUrlLength) {
            $url = route('api.help').'?c='.$this->createUrlShortcode.'&btext='.base64_encode($this->newMessage);
        }
        if (strlen($url) > $maxUrlLength) {
            $url = route('api.help').'?c='.$this->createUrlShortcode.'&text='.urlencode($this->newMessage);
        }
        if (strlen($url) > $maxUrlLength) {
            return 'The URL will be too long';
        }

        if ($this->newForm) {
            $url = $url.'&form=1';
        }

        $this->resetValidation('url_length');

        return $url;
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
        }, Str::snake($this->createUrlShortcode.$this->newMessage).'.svg');
    }

    public function createWebhook()
    {
        $this->validate([
            'newWebhookUrl' => 'required|url',
            'newWebhookName' => 'required|string|max:255|unique:webhooks,name',
            'newWebhookDefault' => 'required|boolean',
        ]);

        Webhook::createNew($this->newWebhookUrl, $this->newWebhookName, $this->newWebhookDefault);

        $this->reset();
    }
}
