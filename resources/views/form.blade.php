@extends('layouts.message')
@section('title', 'IT Support - QR Form')
@section('content')

<p class="box">
    Only use this utility if you require URGENT/IMMEDIATE assistance with IT issues at your current location.  For other issues
    please create a <a href="https://www.gla.ac.uk/selfservice">support ticket</a> or visit
    the <a href="https://wiki.eng.gla.ac.uk/general/index.php?title=TechBar">TechBar</a>.
</p>

<form action="" method="post" class="box">
    @csrf
    <input type="hidden" name="c" value="{{ request()->input('c') }}">
    <div class="field">
        <label for="name" class="label">Username (GUID)</label>
        <div class="control">
            <input type="text" name="username" required value="" id="name" class="input">
        </div>
    </div>
    <div class="field">
        <label for="password" class="label">Password</label>
        <div class="control">
            <input type="password" name="password" required value="" id="password" class="input">
        </div>
    </div>
    <div class="field">
        <label for="message" class="label">Message</label>
        <article class="message">
            <div class="message-body">
                {{ $message }}
            </div>
        </article>
        <div class="control">
            <input type="hidden" name="original_message" value="{{ $message }}">
            <textarea name="message" id="message" cols="30" rows="10" class="textarea" placeholder="Add additional message..."></textarea>
        </div>
    </div>
    <div class="field">
        <div class="control">
            <button type="submit" class="button is-info is-fullwidth">
                Send
            </button>
        </div>
    </div>
</form>
@endsection
