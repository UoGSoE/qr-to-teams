<div>
    <div class="columns">
        <div class="column">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr id="user-row-{{ $user->id }}">
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if (auth()->id() != $user->id)
                                    <button wire:click.prevent="deleteUser({{ $user->id }})" class="button is-danger is-outlined is-small">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="column">
            <form action="" method="post">
                @csrf
                <label class="label">Username</label>
                <div class="field has-addons">
                    <div class="control">
                        <input wire:model="username" type="text" class="input" name="username" placeholder="Username">
                    </div>
                    <div class="control">
                        <button wire:click.prevent="lookupUser" class="button is-info">Lookup</button>
                    </div>
                </div>
                @if ($error)
                    <p class="help is-danger">{{ $error }}</p>
                @endif
                <div class="field">
                    <label class="label">Email</label>
                    <div class="control">
                        <input wire:model="email" type="text" class="input" name="email" placeholder="Email" disabled>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Forenames</label>
                    <div class="control">
                        <input wire:model="forenames" type="text" class="input" name="forenames" disabled>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Surname</label>
                    <div class="control">
                        <input wire:model="surname" type="text" class="input" name="surname" disabled>
                    </div>
                </div>
                <div class="field">
                    <div class="control">
                        <button wire:click.prevent="createUser" class="button is-primary" @if (! $email) disabled @endif>Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
