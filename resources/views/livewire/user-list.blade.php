<div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Username</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($users as $user)
                        <flux:table.row id="user-row-{{ $user->id }}">
                            <flux:table.cell>{{ $user->username }}</flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell>
                                @if (auth()->id() != $user->id)
                                    <flux:button
                                        wire:click="deleteUser({{ $user->id }})"
                                        icon="trash"
                                        variant="danger"
                                        size="sm"
                                        inset="top bottom"
                                    />
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
        <div>
            <flux:card>
                <flux:heading size="md" class="mb-4">Add New User</flux:heading>
                <form action="" method="post" class="space-y-4">
                    @csrf
                    <div class="max-w-md">
                        <flux:input
                            wire:model.live="username"
                            label="Username"
                            name="username"
                            placeholder="Username"
                        >
                            <x-slot name="iconTrailing">
                                <flux:button
                                    wire:click="lookupUser"
                                    variant="primary"
                                    size="sm"
                                    inset="right"
                                >
                                    Lookup
                                </flux:button>
                            </x-slot>
                        </flux:input>
                    </div>
                    @if ($error)
                        <flux:text variant="danger" size="sm">{{ $error }}</flux:text>
                    @endif
                    
                    <flux:input
                        wire:model.live="email"
                        label="Email"
                        name="email"
                        placeholder="Email"
                        disabled
                    />
                    
                    <flux:input
                        wire:model.live="forenames"
                        label="Forenames"
                        name="forenames"
                        disabled
                    />
                    
                    <flux:input
                        wire:model.live="surname"
                        label="Surname"
                        name="surname"
                        disabled
                    />
                    
                    <flux:button
                        wire:click="createUser"
                        variant="primary"
                        :disabled="!$email"
                    >
                        Add User
                    </flux:button>
                </form>
            </flux:card>
        </div>
    </div>
</div>
