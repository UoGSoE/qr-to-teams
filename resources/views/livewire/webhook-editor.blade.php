<div>
    @if ($createUrlShortcode)
        <div class="box">
            <button class="delete is-pulled-right is-small" wire:click="$set('createUrlShortcode', '')"></button>
            <form action="" class="pb-2">
                @csrf
                <label class="label">Text of message to send</label>
                <div class="field">
                    <div class="control">
                        <input wire:model.debounce.500ms="newMessage" type="text" class="input" name="new_message">
                    </div>
                </div>
            </form>
            <h4 class="title is-5">URL</h4>
            <p class="subtitle">
                {{ $newUrl }}
                <a href="{{ $newUrl }}" target="_blank" class="button is-small">Send a test</a>
            </p>
            <div wire:click="downloadSvg" class="is-clickable" title="Download an SVG" aria-label="Download an SVG of this QR code">
                {!! QrCode::generate($newUrl) !!}
            </div>
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
                    <td :class="hoveringId == {{ $hook->id }} ? 'is-danger' : ''">{{ $hook->id }}</td>
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
