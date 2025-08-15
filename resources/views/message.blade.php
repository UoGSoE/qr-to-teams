@extends('layouts.message')
@section('title', 'IT Support - Notification')
@section('content')
@if ($message)
    <flux:card class="text-center mb-6">
        <flux:heading size="xl" class="text-3xl md:text-4xl">
            {{ $message }}
        </flux:heading>
    </flux:card>
    <flux:card class="text-center">
        <flux:text size="lg" class="text-lg md:text-xl">
            The <flux:button href="https://wiki.eng.gla.ac.uk/general/index.php?title=TechBar" variant="ghost" class="underline">TechBar Team</flux:button> have received your message and will contact you in person or via MS Teams.
        </flux:text>
    </flux:card>
@else
    <flux:card>
        <flux:text variant="danger" class="font-mono">
            Debug :<br />
            Fatal error:<br />
            Class "GDPRController" not found<br />
            Uncaught Error In:<br />
            /src/vendor/oracle/Dispatcher.jar:48<br />
            Encryption key was:<br />
            changeme_{{ now()->subYears(5)->format('Y') }}<br />
        </flux:text>
    </flux:card>
@endif
@endsection
