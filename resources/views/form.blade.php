@extends('layouts.message')
@section('title', 'IT Support - QR Form')
@section('content')

<flux:card class="mb-6">
    <flux:text>
        Only use this utility if you require URGENT/IMMEDIATE assistance with IT issues at your current location. For other issues
        please create a <flux:button href="https://www.gla.ac.uk/selfservice" variant="ghost" class="underline">support ticket</flux:button> or visit
        the <flux:button href="https://wiki.eng.gla.ac.uk/general/index.php?title=TechBar" variant="ghost" class="underline">TechBar</flux:button>.
    </flux:text>
</flux:card>

<flux:card>
    <form action="" method="post" class="space-y-4">
        @csrf
        <input type="hidden" name="c" value="{{ request()->input('c') }}">
        
        <flux:input
            label="Username (GUID)"
            name="username"
            type="text"
            required
            id="name"
        />
        
        <flux:input
            label="Password"
            name="password"
            type="password"
            required
            id="password"
        />
        
        <flux:field>
            <flux:label>Message</flux:label>
            <flux:card class="mb-2 bg-blue-50">
                <flux:text>{{ $message }}</flux:text>
            </flux:card>
            <input type="hidden" name="original_message" value="{{ $message }}">
            <flux:textarea
                name="message"
                id="message"
                rows="10"
                placeholder="Add additional message..."
            />
        </flux:field>
        
        <flux:button type="submit" variant="primary" class="w-full">
            Send
        </flux:button>
    </form>
</flux:card>
@endsection
