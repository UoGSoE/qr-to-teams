<?php

use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('there is an artisan command to make a new webhook', function () {
    $this->artisan('webhook:create', [
        'name' => 'Test Webhook',
        'url' => 'https://example.com/webhook/1234',
    ])->assertExitCode(0);

    $this->assertEquals(1, Webhook::count());
    tap(Webhook::first(), function ($hook) {
        $this->assertEquals('Test Webhook', $hook->name);
        $this->assertEquals('https://example.com/webhook/1234', $hook->url);
        $this->assertNotNull($hook->shortcode);
        $this->assertFalse($hook->is_default);
    });
});

test('we can optionally pass a flag to make the new webhook the default', function () {
    $existingDefault = Webhook::factory()->create(['is_default' => true]);

    $this->artisan('webhook:create', [
        'name' => 'Test Webhook',
        'url' => 'https://example.com/webhook/1234',
        'default' => 'true',
    ])->assertExitCode(0);

    $this->assertEquals(2, Webhook::count());
    tap(Webhook::where('name', 'Test Webhook')->first(), function ($hook) {
        $this->assertEquals('Test Webhook', $hook->name);
        $this->assertEquals('https://example.com/webhook/1234', $hook->url);
        $this->assertNotNull($hook->shortcode);
        $this->assertTrue($hook->is_default);
    });
    $this->assertFalse($existingDefault->fresh()->is_default);
});

test('if we dont pass options we are asked for input', function () {
    $this->artisan('webhook:create')
        ->expectsQuestion('Name?', 'Test Webhook')
        ->expectsQuestion('URL?', 'https://example.com/webhook/1234')
        ->assertExitCode(0);

    $this->assertEquals(1, Webhook::count());
    tap(Webhook::where('name', 'Test Webhook')->first(), function ($hook) {
        $this->assertEquals('Test Webhook', $hook->name);
        $this->assertEquals('https://example.com/webhook/1234', $hook->url);
        $this->assertNotNull($hook->shortcode);
        $this->assertFalse($hook->is_default);
    });
});

test('there is an artisan command to list webhooks', function () {
    $hook1 = Webhook::factory()->create(['name' => 'Test Webhook One', 'url' => 'https://example.com/webhook/1234']);
    $hook2 = Webhook::factory()->create(['name' => 'Test Webhook Two', 'url' => 'https://example.com/webhook/5678', 'is_default' => true]);

    $this->artisan('webhook:list')
        ->expectsTable(
            ['ID', 'Name', 'Shortcode', 'URL', 'Default?'],
            [[
                $hook1->id,
                $hook1->name,
                $hook1->shortcode,
                $hook1->url,
                'No',
            ], [
                $hook2->id,
                $hook2->name,
                $hook2->shortcode,
                $hook2->url,
                'Yes',
            ]]
        )
        ->assertExitCode(0);
});

test('there is an artisan command to delete a webhook', function () {
    $hook1 = Webhook::factory()->create(['name' => 'Test Webhook One', 'url' => 'https://example.com/webhook/1234']);
    $hook2 = Webhook::factory()->create(['name' => 'Test Webhook Two', 'url' => 'https://example.com/webhook/5678']);
    $hook3 = Webhook::factory()->create(['name' => 'Test Webhook Three', 'url' => 'https://example.com/webhook/9012']);

    $this->artisan('webhook:delete', [
        'id' => $hook2->id,
    ])->expectsOutput('Deleted webhook '.$hook2->id.' : Test Webhook Two')
        ->assertExitCode(0);
});

test('if we dont supply an id we are asked to choose one', function () {
    $hook1 = Webhook::factory()->create(['name' => 'Test Webhook One', 'url' => 'https://example.com/webhook/1234']);
    $hook2 = Webhook::factory()->create(['name' => 'Test Webhook Two', 'url' => 'https://example.com/webhook/5678']);
    $hook3 = Webhook::factory()->create(['name' => 'Test Webhook Three', 'url' => 'https://example.com/webhook/9012']);

    $this->artisan('webhook:delete', [
    ])->expectsQuestion('Which Webhook?', $hook2->name)
        ->expectsOutput('Deleted webhook '.$hook2->id.' : Test Webhook Two')
        ->assertExitCode(0);
});

test('there is an artisan command to set the default webhook', function () {
    $hook1 = Webhook::factory()->create(['name' => 'Test Webhook One', 'url' => 'https://example.com/webhook/1234']);
    $hook2 = Webhook::factory()->create(['name' => 'Test Webhook Two', 'url' => 'https://example.com/webhook/5678', 'is_default' => true]);
    $hook3 = Webhook::factory()->create(['name' => 'Test Webhook Three', 'url' => 'https://example.com/webhook/9012']);

    $this->artisan('webhook:default', [
        'id' => $hook3->id,
    ])->expectsOutput('Set default webhook to '.$hook3->id.' : Test Webhook Three')
        ->assertExitCode(0);
    $this->assertFalse($hook1->fresh()->is_default);
    $this->assertFalse($hook2->fresh()->is_default);
    $this->assertTrue($hook3->fresh()->is_default);
});

test('if we dont supply the id we are asked for it', function () {
    $hook1 = Webhook::factory()->create(['name' => 'Test Webhook One', 'url' => 'https://example.com/webhook/1234']);
    $hook2 = Webhook::factory()->create(['name' => 'Test Webhook Two', 'url' => 'https://example.com/webhook/5678', 'is_default' => true]);
    $hook3 = Webhook::factory()->create(['name' => 'Test Webhook Three', 'url' => 'https://example.com/webhook/9012']);

    $this->artisan('webhook:default', [
    ])->expectsQuestion('Which Webhook?', $hook1->name)
        ->expectsOutput('Set default webhook to '.$hook1->id.' : Test Webhook One')
        ->assertExitCode(0);
    $this->assertTrue($hook1->fresh()->is_default);
    $this->assertFalse($hook2->fresh()->is_default);
    $this->assertFalse($hook3->fresh()->is_default);
});
