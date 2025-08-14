<div wire:poll.1m>
    <div class="max-w-md mb-6">
        <flux:input
            type="text"
            placeholder="Search"
            wire:model.live="filter"
            icon="magnifying-glass"
            autofocus
        />
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <div class="space-y-6">
                @foreach ($servers as $server)
                    <div id="server-row-{{ $server->id }}">
                        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                            <div class="flex items-center gap-4">
                                <flux:heading size="md">
                                    <flux:button href="{{ $server->wiki_link }}" variant="ghost" size="sm">{{ $server->name }}</flux:button>
                                </flux:heading>
                                @if ($server->hasNotes())
                                    <flux:button
                                        wire:click="setCurrentNotesServer({{ $server->id }})"
                                        variant="subtle"
                                        size="sm"
                                    >
                                        Notes
                                    </flux:button>
                                @endif
                            </div>
                            <flux:button
                                wire:click="deleteServer({{ $server->id }})"
                                icon="trash"
                                variant="danger"
                                size="sm"
                                inset="top bottom"
                            />
                        </div>
                        <flux:table class="mb-4">
                            <flux:table.rows>
                                @foreach ($server->guests as $guest)
                                    <flux:table.row id="guest-row-{{ $guest->id }}">
                                        <flux:table.cell class="w-1/2">
                                            <flux:button href="{{ $guest->wiki_link }}" variant="ghost" size="sm">{{ $guest->name }}</flux:button>
                                        </flux:table.cell>
                                        <flux:table.cell class="@if ($guest->updated_at->isBefore(now()->subWeek())) text-red-600 @endif">
                                            {{ $guest->updated_at->format('d/m/Y H:i') }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            @if ($guest->hasNotes())
                                                <flux:button
                                                    wire:click="setCurrentNotes({{ $guest->id }})"
                                                    variant="@if ($guest->notes_filter_match) primary @else subtle @endif"
                                                    size="sm"
                                                >
                                                    Notes
                                                </flux:button>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:button
                                                wire:click="deleteGuest({{ $guest->id }})"
                                                icon="trash"
                                                variant="danger"
                                                size="sm"
                                                inset="top bottom"
                                            />
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @endforeach
            </div>
        </div>
        <div>
            @if ($currentNotes)
                <flux:card class="sticky top-4">
                    <flux:heading size="md" class="mb-4">Notes for {{ $currentName }}</flux:heading>
                    <flux:text class="font-mono whitespace-pre-wrap">{!! $currentNotes !!}</flux:text>
                </flux:card>
            @endif
        </div>
    </div>
</div>
