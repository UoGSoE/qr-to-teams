<x-layouts.app>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <flux:heading size="lg">QR To Webhook User Management</flux:heading>
    <div class="flex items-center gap-4">
        <flux:button href="{{ route('dashboard') }}" variant="subtle">Manage Webhooks</flux:button>
        @auth
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <flux:button type="submit" variant="ghost">Logout</flux:button>
            </form>
        @endauth
    </div>
</div>

<flux:separator class="mb-6" />
@livewire('user-list')

</x-layouts.app>
