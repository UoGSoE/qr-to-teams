<div wire:poll.1m>
    <div class="field is-expanded">
        <div class="control">
            <input class="input" type="text" placeholder="Search" wire:model="filter" autofocus>
        </div>
    </div>
    <div class="columns">
        <div class="column">
            <ul>
                @foreach ($servers as $server)
                    <li id="server-row-{{ $server->id }}">
                        <div class="level">
                            <div class="level-left">
                                <div class="level-item">
                                    <h2 class="title is-4">
                                        <a href="{{ $server->wiki_link }}">{{ $server->name }}</a>
                                    </h2>
                                </div>
                                <div class="level-item">
                                    @if ($server->hasNotes())
                                        <button wire:click.prevent="setCurrentNotesServer({{ $server->id }})" class="button is-small">Notes</button>
                                    @endif
                                </div>
                            </div>
                            <div class="level-right">
                                <div class="level-item">
                                    <button wire:click.prevent="deleteServer({{ $server->id }})" class="button is-danger is-outlined is-small">Delete</button>
                                </div>
                            </div>
                        </div>
                        <table class="table is-fullwidth is-hoverable" style="margin-bottom: 1rem;">
                            <tbody>
                                @foreach ($server->guests as $guest)
                                    <tr id="guest-row-{{ $guest->id }}">
                                        <td width="50%">
                                            <a href="{{ $guest->wiki_link }}">{{ $guest->name }}</a>
                                        </td>
                                        <td class="@if ($guest->updated_at->isBefore(now()->subWeek())) has-text-danger @endif">
                                            {{ $guest->updated_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            @if ($guest->hasNotes())
                                                <button wire:click.prevent="setCurrentNotes({{ $guest->id }})" class="button is-small @if ($guest->notes_filter_match) is-success @endif">Notes</button>
                                            @endif
                                        </td>
                                        <td>
                                            <button wire:click.prevent="deleteGuest({{ $guest->id }})" class="button is-danger is-outlined is-small">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="column">
            @if ($currentNotes)
                <div class="box" style="position: sticky; top: 1rem;">
                    <h4 class="title is-4">Notes for {{ $currentName }}</h4>
                    <p class="is-family-monospace">{!! $currentNotes !!}</p>
                </div>
            @endif
        </div>
    </div>
</div>
