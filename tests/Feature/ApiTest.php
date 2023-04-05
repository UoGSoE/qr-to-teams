<?php

namespace Tests\Feature;

use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Ohffs\Ldap\FakeLdapConnection;
use Ohffs\Ldap\LdapConnectionInterface;
use Ohffs\Ldap\LdapUser;
use Ohffs\MSTeamsAlerts\Jobs\SendToMSTeamsChannelJob;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_incoming_request_with_base64_encoded_text_is_resent_as_an_ms_teams_webhook_post(): void
    {
        Bus::fake();
        $base64Text = base64_encode('test base64');
        $webhook = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234']);

        $response = $this->get('/api/help?btext='.$base64Text.'&c='.$webhook->shortcode);

        $response->assertRedirect(route('message').'?message='.base64_encode('Notification sent.'));
        Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
            return $job->webhookUrl === $webhook->url && $job->text === 'test base64';
        });
    }

    /** @test */
    public function an_incoming_request_with_encrypted_text_is_resent_as_an_ms_teams_webhook_post(): void
    {
        Bus::fake();
        $encryptedText = encrypt('test encrypted');
        $webhook = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234']);

        $response = $this->get('/api/help?etext='.$encryptedText.'&c='.$webhook->shortcode);

        $response->assertRedirect(route('message').'?message='.base64_encode('Notification sent.'));
        Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
            return $job->webhookUrl === $webhook->url && $job->text === 'test encrypted';
        });
    }

    /** @test */
    public function an_incoming_request_is_resent_to_the_default_ms_teams_webhook_if_a_specific_one_isnt_specified_in_the_query_params(): void
    {
        Bus::fake();
        $webhook1 = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234', 'is_default' => false]);
        $webhook2 = Webhook::factory()->create(['url' => 'https://example.com/webhook/5678', 'is_default' => true]);

        $response = $this->get('/api/help?btext='.base64_encode('test'));

        $response->assertRedirect(route('message').'?message='.base64_encode('Notification sent.'));
        Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook2) {
            return $job->webhookUrl === $webhook2->url && $job->text === 'test';
        });
    }

    /** @test */
    public function a_webhook_can_direct_the_user_to_a_form_for_entering_their_details(): void
    {
        Bus::fake();
        $this->freezeTime();
        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook/1234',
        ]);

        $response = $this->get('/api/help?form=1&btext='.base64_encode('test').'&c='.$webhook->shortcode);

        $response->assertRedirect(route('form').'?btext='.urlencode(base64_encode('test')).'&c='.$webhook->shortcode);
    }

    /** @test */
    public function submitting_the_form_with_valid_details_redirects_to_the_webhook_endpoint(): void
    {
        Bus::fake();
        $this->freezeTime();
        $this->fakeLdapConnection();
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
    }

    /** @test */
    public function incoming_webhooks_update_the_timestamp_on_the_record_and_update_the_stats(): void
    {
        Bus::fake();
        $this->freezeTime();
        $webhook = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234', 'updated_at' => now()->subDays(3), 'called_count' => 0]);

        $this->assertEquals(0, $webhook->fresh()->called_count);

        $response = $this->get('/api/help?btext='.base64_encode('test').'&c='.$webhook->shortcode);

        $response->assertRedirect(route('message').'?message='.base64_encode('Notification sent.'));
        Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
            return $job->webhookUrl === $webhook->url && $job->text === 'test';
        });
        $this->assertEquals(now()->format('Y-m-d'), $webhook->fresh()->updated_at->format('Y-m-d'));
        $this->assertEquals(1, $webhook->fresh()->called_count);

        $response = $this->get('/api/help?btext='.base64_encode('test').'&c='.$webhook->shortcode);

        $response->assertRedirect(route('message').'?message='.base64_encode('Notification sent.'));
        Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
            return $job->webhookUrl === $webhook->url && $job->text === 'test';
        });
        $this->assertEquals(now()->format('Y-m-d'), $webhook->fresh()->updated_at->format('Y-m-d'));
        $this->assertEquals(2, $webhook->fresh()->called_count);
    }

    /** @test */
    public function if_the_querystring_is_missing_text_we_return_an_error(): void
    {
        Bus::fake();

        $response = $this->get('/api/help?sdfsdfsfd');

        $response->assertRedirect(route('message').'?message='.urlencode(base64_encode('No message specified - no notification sent.')));
        Bus::assertNotDispatched(SendToMSTeamsChannelJob::class);
    }

    /** @test */
    public function if_the_querystring_has_an_invalid_webhook_code_we_return_an_error(): void
    {
        Bus::fake();

        $response = $this->get('/api/help?text=sdfsdfsfd?c=blah');

        $response->assertRedirect(route('message').'?message='.urlencode(base64_encode('Invalid channel - no notification sent.')));
        Bus::assertNotDispatched(SendToMSTeamsChannelJob::class);
    }

    private function fakeLdapConnection()
    {
        $this->instance(
            LdapConnectionInterface::class,
            new FakeLdapConnection('up', 'whatever')
        );
    }
}
