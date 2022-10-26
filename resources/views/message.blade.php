@extends('layouts.message')
@section('title', 'IT Support - Notification')
@section('content')
@if ($message)
    <div class="box has-text-centered is-size-1 is-size-2-mobile">
        {{ $message }}
    </div>
    <div class="box has-text-centered is-size-3 is-size-4-mobile">
        The <a href="https://wiki.eng.gla.ac.uk/general/index.php?title=TechBar">TechBar Team</a> have received your message and will contact you in person or via MS Teams.
    </div>
@else
        Debug :<br />
        Fatal error:<br />
        Class "GDPRController" not found<br />
        Uncaught Error In:<br />
        /src/vendor/oracle/Dispatcher.jar:48<br />
        Encryption key was:<br />
        changeme_{{ now()->subYears(5)->format('Y') }}<br />
    @endif
</div>
@endsection
