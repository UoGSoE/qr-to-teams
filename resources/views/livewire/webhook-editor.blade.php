<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <flux:heading size="lg">QR to Webhook</flux:heading>
            <flux:button
                wire:click.prevent="$toggle('showCreateForm')"
                variant="ghost"
                size="sm"
                title="@if ($showCreateForm) Hide @else Show @endif New Webhook Form"
                aria-label="@if ($showCreateForm) Hide @else Show @endif New Webhook Form"
            >
                @if ($showCreateForm) - @else + @endif
            </flux:button>
        </div>
        <div class="flex items-center gap-4">
            <flux:button href="{{ route('user.index') }}" variant="subtle">Manage Users</flux:button>
            @auth
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <flux:button type="submit" variant="ghost">Logout</flux:button>
                </form>
            @endauth
        </div>
    </div>

    <flux:separator class="mb-6" />

    @if ($createUrlShortcode)
        <flux:card class="mb-6">
            <div class="flex justify-between items-start mb-4">
                <flux:heading size="md">Create URL</flux:heading>
                <flux:button
                    wire:click="$set('createUrlShortcode', '')"
                    icon="x-mark"
                    variant="ghost"
                    size="sm"
                    inset="top bottom"
                />
            </div>
            <form action="" class="space-y-4">
                @csrf
                <flux:input
                    wire:model.live.debounce.500ms="newMessage"
                    label="Text of message to send"
                    name="new_message"
                />

                <flux:checkbox
                    wire:model.live="newForm"
                    name="new_form"
                    label="Redirect this via a form so users can edit the message?"
                />
            </form>
            @if ($newMessage)
                <div class="mt-6" id="new_url_display_box">
                    <div class="flex flex-wrap items-center gap-4 mb-4">
                        <flux:heading size="sm">Generated URL</flux:heading>
                        <flux:button href="{{ $newUrl }}" target="_blank" size="sm" variant="subtle">Send a test</flux:button>
                        <flux:button wire:click.prevent="downloadSvg" size="sm" variant="subtle">Download QR code</flux:button>
                        <flux:button
                            x-data="{ url: @entangle('newUrl').live }"
                            @click="navigator.clipboard.writeText(url).then(() => console.log('worked'), () => console.log('failed'))"
                            size="sm"
                            variant="subtle"
                        >
                            Copy to Clipboard
                        </flux:button>
                    </div>
                    <flux:input
                        value="{{ $newUrl }}"
                        name="new_url_webhook"
                        readonly
                    />
                </div>
            @endif
        </flux:card>
    @endif

    @if ($showCreateForm)
        <flux:card wire:key="create-webhook-form" class="mb-6">
            <flux:heading size="md" class="mb-4">Create New Webhook</flux:heading>
            <form action="" class="space-y-4">
                @csrf
                <flux:input
                    wire:model.live="newWebhookUrl"
                    label="Webhook URL"
                    type="text"
                />
                @error('newWebhookUrl')
                    <flux:text variant="danger" size="sm">{{ $message }}</flux:text>
                @enderror

                <flux:input
                    wire:model.live="newWebhookName"
                    label="Webhook Name"
                    type="text"
                />
                @error('newWebhookName')
                    <flux:text variant="danger" size="sm">{{ $message }}</flux:text>
                @enderror

                <flux:checkbox
                    wire:model.live="newWebhookDefault"
                    label="Make this the default webhook?"
                    value="1"
                />

                <flux:button wire:click.prevent="createWebhook" variant="primary">Add Webhook</flux:button>
            </form>
        </flux:card>
    @endif

    <flux:table>
        <flux:table.columns>
            <flux:table.column>ID</flux:table.column>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Shortcode</flux:table.column>
            <flux:table.column>URL</flux:table.column>
            <flux:table.column>Created</flux:table.column>
            <flux:table.column>Last Called</flux:table.column>
            <flux:table.column>Calls</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows x-data="{ hoveringId: null }">
            @foreach ($webhooks as $hook)
                <flux:table.row id="webhook-row-{{ $hook->id }}">
                    <flux:table.cell :class="$hook->is_default ? 'bg-green-50 text-green-800' : ''" :title="$hook->is_default ? 'Default' : ''">{{ $hook->id }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button
                            wire:click.prevent="$set('createUrlShortcode', '{{ $hook->shortcode }}')"
                            variant="ghost"
                            size="sm"
                        >
                            {{ $hook->name }}
                        </flux:button>
                    </flux:table.cell>
                    <flux:table.cell>{{ $hook->shortcode }}</flux:table.cell>
                    <flux:table.cell title="{{ $hook->url }}">
                        <div x-data="{ expanded: false }">
                            <flux:button @click="expanded = ! expanded" variant="ghost" size="sm">
                                <span x-show="expanded">{{ $hook->url }}</span>
                                <span x-show="! expanded">...{{ Str::substr($hook->url, -30, 30) }}</span>
                            </flux:button>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $hook->created_at->format('d/m/y H:i') }}</flux:table.cell>
                    <flux:table.cell>{{ $hook->updated_at->format('d/m/y H:i') }}</flux:table.cell>
                    <flux:table.cell>{{ $hook->called_count }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button
                            wire:click="deleteWebhook({{ $hook->id }})"
                            icon="trash"
                            variant="danger"
                            size="sm"
                            inset="top bottom"
                            @mouseenter="hoveringId = {{ $hook->id }}"
                            @mouseleave="hoveringId = null"
                        />
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
