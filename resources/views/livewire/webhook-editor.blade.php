<div>
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title is-3">QR to Webhook</h1>
            </div>
            <div class="level-item">
                <button
                    class="button"
                    wire:click.prevent="$toggle('showCreateForm')"
                    title="@if ($showCreateForm) Hide @else Show @endif New Webhook Form"
                    aria-label="@if ($showCreateForm) Hide @else Show @endif New Webhook Form"
                >
                @if ($showCreateForm) - @else + @endif
                </button>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="{{ route('user.index') }}" class="button">Manage Users</a>
            </div>
            <div class="level-item">
                @auth
                    <form method="POST" action="{{ route('auth.logout') }}" class="is-pulled-right">
                        @csrf
                        <button class="button">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </div>

    <hr>

    @if ($createUrlShortcode)
        <div class="box">
            <button class="delete is-pulled-right is-small" wire:click="$set('createUrlShortcode', '')"></button>
            <form action="" class="pb-4">
                @csrf
                <label class="label">Text of message to send</label>
                <div class="field">
                    <div class="control">
                        <input wire:model.debounce.500ms="newMessage" type="text" class="input" name="new_message">
                    </div>
                </div>
            </form>
            @if ($newMessage)
                <div class="level" id="new_url_display_box">
                    <div class="level-left">
                        <div class="level-item">
                            <h4 class="title is-5">
                                    URL
                            </h4>
                        </div>
                        <div class="level-item">
                            <a href="{{ $newUrl }}" target="_blank" class="button is-small">Send a test</a>
                        </div>
                        <div class="level-item">
                            <button wire:click.prevent="downloadSvg" class="button is-small">Download QR code</button>
                        </div>
                        <div class="level-item" x-data="{ url: @entangle('newUrl') }">
                            <button @click="navigator.clipboard.writeText(url).then(() => console.log('worked'), () => console.log('failed'))" class="button is-small">Copy to Clipboard</button>
                        </div>
                    </div>
                </div>
                </h4>
                <p class="subtitle">
                    <div class="field">
                        <div class="control">
                            <input type="text" class="input" name="new_url_webhook" value="{{ $newUrl }}">
                        </div>
                    </div>
                </p>
            @endif
        </div>
    @endif

    @if ($showCreateForm)
        <div class="box" wire:key="create-webhook-form">
            <form action="">
                @csrf
                <div class="field">
                    <label class="label">Webhook URL</label>
                    <div class="control">
                    <input wire:model="newWebhookUrl" class="input" type="text">
                    </div>
                    @error('newWebhookUrl')
                        <p class="help is-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label class="label">Webhook Name</label>
                    <div class="control">
                    <input wire:model="newWebhookName" class="input" type="text">
                    </div>
                    @error('newWebhookName')
                        <p class="help is-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <div class="control">
                      <label class="checkbox">
                        <input type="checkbox" wire:model="newWebhookDefault" value="1">
                        Make this the default webhook?
                      </label>
                    </div>
                </div>
                <div class="field">
                    <div class="control">
                    <button wire:click.prevent="createWebhook" class="button">Add</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <table class="table is-fullwidth is-striped is-hoverable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Shortcode</th>
                <th>URL</th>
                <th>Created</th>
                <th>Last Called</th>
                <th>Calls</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody x-data="{ hoveringId: null }">
            @foreach ($webhooks as $hook)
                <tr id="webhook-row-{{ $hook->id }}">
                    <td @if ($hook->is_default) class="is-success" title="Default" @endif :class="hoveringId == {{ $hook->id }} ? 'is-danger' : ''">{{ $hook->id }}</td>
                    <td>
                        <a href="#"  wire:click.prevent="$set('createUrlShortcode', '{{ $hook->shortcode }}')">
                            {{ $hook->name }}
                        </a>
                    </td>
                    <td>{{ $hook->shortcode }}</td>
                    <td title="{{ $hook->url }}">
                        <a href="#" x-data="{ expanded: false }" @click="expanded = ! expanded">
                            <span x-show="expanded">
                                {{ $hook->url }}
                            </span>
                            <span x-show="! expanded">
                                ...{{ Str::substr($hook->url, -30, 30) }}
                            </span>
                        </a>
                    </td>
                    <td>{{ $hook->created_at->format('d/m/y H:i') }}</td>
                    <td>{{ $hook->updated_at->format('d/m/y H:i') }}</td>
                    <td>{{ $hook->called_count }}</td>
                    <td>
                        <button wire:click="deleteWebhook({{ $hook->id }})" class="button is-danger is-small is-outlined" @mouseenter="hoveringId = {{ $hook->id }}" @mouseleave="hoveringId = null">
                            &times;
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
