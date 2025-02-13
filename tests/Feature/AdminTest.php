<?php

use App\Models\User;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('non users cant see the homepage dashboard', function () {
    $user = User::factory()->create();
    $hook1 = Webhook::factory()->create();
    $hook2 = Webhook::factory()->create();

    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('auth.login'));
});

test('users can see see the homepage dashboard', function () {
    $user = User::factory()->create();
    $hook1 = Webhook::factory()->create();
    $hook2 = Webhook::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('QR to Webhook');
    $response->assertSeeLivewire('webhook-editor');
    $response->assertSee($hook1->url);
    $response->assertSee($hook2->url);
});

test('users can add a new webhook', function () {
    $user = User::factory()->create();
    $hook1 = Webhook::factory()->create();
    $hook2 = Webhook::factory()->create();

    Livewire::actingAs($user)->test('webhook-editor')
        ->assertSee($hook1->url)
        ->assertSee($hook2->url)
        ->set('url', 'https://example.com/new-hook')
        ->set('name', 'New Hook')
        ->call('addWebhook')
        ->assertSee($hook1->url)
        ->assertSee($hook2->url)
        ->assertSee('https://example.com/new-hook');

    $this->assertDatabaseHas('webhooks', [
        'url' => 'https://example.com/new-hook',
        'name' => 'New Hook',
        'is_default' => false,
    ]);
});

test('users can delete an existing webhook', function () {
    $user = User::factory()->create();
    $hook1 = Webhook::factory()->create();
    $hook2 = Webhook::factory()->create();
    $hook3 = Webhook::factory()->create();

    Livewire::actingAs($user)->test('webhook-editor')
        ->assertSee($hook1->url)
        ->assertSee($hook2->url)
        ->assertSee($hook3->url)
        ->call('deleteWebhook', $hook2->id)
        ->assertSee($hook1->url)
        ->assertDontSee($hook2->url)
        ->assertSee($hook3->url);

    $this->assertDatabaseMissing('webhooks', [
        'id' => $hook2->id,
    ]);
});

test('users can create a new callable url to hit a specific webhook', function () {
    $hook = Webhook::factory()->create(['url' => 'https://example.com/new-hook', 'shortcode' => 'abc123']);
    $user = User::factory()->create();

    Livewire::actingAs($user)->test('webhook-editor')
        ->assertSee($hook->url)
        ->assertDontSee('Text of message to send')
        ->set('createUrlShortcode', 'abc123')
        ->assertSee('Text of message to send')
        ->set('newMessage', 'Hello World')
        ->assertSee(route('api.help', [
            'c' => 'abc123',
            'etext' => '', // etext is encrypted so value will be random(ish) - just check it's in the query string
        ]))
        ->assertDontSee('&form=1')
        ->set('newForm', 1)
        ->assertSee('&form=1');
});
