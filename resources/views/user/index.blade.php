@extends('layouts.app')
@section('content')
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title is-3">
                QR To Webhook User Management
            </h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="{{ route('dashboard') }}" class="button">Manage Webhooks</a>
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
@livewire('user-list')

@endsection
