<?php

use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Ohffs\Ldap\FakeLdapConnection;
use Ohffs\Ldap\LdapConnectionInterface;
use Ohffs\Ldap\LdapUser;
use Ohffs\MSTeamsAlerts\Jobs\SendToMSTeamsChannelJob;
use Tests\TestCase;


test('an incoming request with base64 encoded text is resent as an ms teams webhook post', function () {
    Bus::fake();
    $base64Text = base64_encode('test base64');
    $webhook = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234']);

    $response = $this->get('/api/help?btext='.$base64Text.'&c='.$webhook->shortcode);

    $response->assertRedirect(route('message').'?message='.base64_encode('Notification sent.'));
    Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
        return $job->webhookUrl === $webhook->url && $job->text === 'test base64';
    });
});

test('an incoming request with encrypted text is resent as an ms teams webhook post', function () {
    Bus::fake();
    $encryptedText = encrypt('test encrypted');
    $webhook = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234']);

    $response = $this->get('/api/help?etext='.$encryptedText.'&c='.$webhook->shortcode);

    $response->assertRedirect(route('message').'?message='.base64_encode('Notification sent.'));
    Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
        return $job->webhookUrl === $webhook->url && $job->text === 'test encrypted';
    });
});

test('an incoming request is resent to the default ms teams webhook if a specific one isnt specified in the query params', function () {
    Bus::fake();
    $webhook1 = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234', 'is_default' => false]);
    $webhook2 = Webhook::factory()->create(['url' => 'https://example.com/webhook/5678', 'is_default' => true]);

    $response = $this->get('/api/help?btext='.base64_encode('test'));

    $response->assertRedirect(route('message').'?message='.base64_encode('Notification sent.'));
    Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook2) {
        return $job->webhookUrl === $webhook2->url && $job->text === 'test';
    });
});

test('a webhook can direct the user to a form for entering their details', function () {
    Bus::fake();
    $this->freezeTime();
    $webhook = Webhook::factory()->create([
        'url' => 'https://example.com/webhook/1234',
    ]);

    $response = $this->get('/api/help?form=1&btext='.base64_encode('test').'&c='.$webhook->shortcode);

    $response->assertRedirect(route('form').'?btext='.urlencode(base64_encode('test')).'&c='.$webhook->shortcode);
});

test('submitting the form with valid details redirects to the webhook endpoint', function () {
    Bus::fake();
    $this->freezeTime();
    fakeLdapConnection();
    \Ldap::shouldReceive('authenticate')->with('validuser', 'validpassword')->andReturn(true);
    \Ldap::shouldReceive('findUser')->with('validuser')->andReturn(new LdapUser([
        [
            'uid' => ['test1x'],
            'mail' => ['testy@example.com'],
            'sn' => ['mctesty'],
            'givenname' => ['testy'],
            'telephonenumber' => ['12345'],
        ],
    ]));
    $webhook = Webhook::factory()->create([
        'url' => 'https://example.com/webhook/1234',
    ]);

    $response = $this->post(route('form.submit', [
        'username' => 'validuser',
        'password' => 'validpassword',
        'message' => 'test message',
        'c' => $webhook->shortcode,
    ]));

    $response->assertRedirectQueryParamNotNull('etext');
    $response->assertRedirectContains(route('api.help', [
        'c' => $webhook->shortcode,
        'etext' => '', // Note: as the text is encrypted it changes randomly every run, so just checking the param is in the query string
    ]));
});

test('incoming webhooks update the timestamp on the record and update the stats', function () {
    Bus::fake();
    $this->freezeTime();
    $webhook = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234', 'updated_at' => now()->subDays(3), 'called_count' => 0]);

    expect($webhook->fresh()->called_count)->toEqual(0);

    $response = $this->get('/api/help?btext='.base64_encode('test').'&c='.$webhook->shortcode);

    $response->assertRedirect(route('message').'?message='.base64_encode('Notification sent.'));
    Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
        return $job->webhookUrl === $webhook->url && $job->text === 'test';
    });
    expect($webhook->fresh()->updated_at->format('Y-m-d'))->toEqual(now()->format('Y-m-d'));
    expect($webhook->fresh()->called_count)->toEqual(1);

    $response = $this->get('/api/help?btext='.base64_encode('test').'&c='.$webhook->shortcode);

    $response->assertRedirect(route('message').'?message='.base64_encode('Notification sent.'));
    Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
        return $job->webhookUrl === $webhook->url && $job->text === 'test';
    });
    expect($webhook->fresh()->updated_at->format('Y-m-d'))->toEqual(now()->format('Y-m-d'));
    expect($webhook->fresh()->called_count)->toEqual(2);
});

test('if the querystring is missing text we return an error', function () {
    Bus::fake();

    $response = $this->get('/api/help?sdfsdfsfd');

    $response->assertRedirect(route('message').'?message='.urlencode(base64_encode('No message specified - no notification sent.')));
    Bus::assertNotDispatched(SendToMSTeamsChannelJob::class);
});

test('if the querystring has an invalid webhook code we return an error', function () {
    Bus::fake();

    $response = $this->get('/api/help?text=sdfsdfsfd?c=blah');

    $response->assertRedirect(route('message').'?message='.urlencode(base64_encode('Invalid channel - no notification sent.')));
    Bus::assertNotDispatched(SendToMSTeamsChannelJob::class);
});

// Helpers
function fakeLdapConnection()
{
    test()->instance(
        LdapConnectionInterface::class,
        new FakeLdapConnection('up', 'whatever')
    );
}
