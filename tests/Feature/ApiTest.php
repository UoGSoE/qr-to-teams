<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Webhook;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ohffs\MSTeamsAlerts\Jobs\SendToMSTeamsChannelJob;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_incoming_request_is_resent_as_an_ms_teams_webhook_post()
    {
        Bus::fake();
        $webhook = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234']);

        $response = $this->get('/api/help?text=test&c=' . $webhook->shortcode);

        $response->assertOk();
        Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
            return $job->webhookUrl === $webhook->url && $job->text === 'test';
        });
    }

    /** @test */
    public function an_incoming_request_with_base64_encoded_text_is_resent_as_an_ms_teams_webhook_post()
    {
        Bus::fake();
        $base64Text = base64_encode('test base64');
        $webhook = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234']);

        $response = $this->get('/api/help?btext=' . $base64Text . '&c=' . $webhook->shortcode);

        $response->assertOk();
        Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
            return $job->webhookUrl === $webhook->url && $job->text === 'test base64';
        });
    }

    /** @test */
    public function an_incoming_request_is_resent_to_the_default_ms_teams_webhook_if_a_specific_one_isnt_specified_in_the_query_params()
    {
        Bus::fake();
        $webhook1 = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234', 'is_default' => false]);
        $webhook2 = Webhook::factory()->create(['url' => 'https://example.com/webhook/5678', 'is_default' => true]);

        $response = $this->get('/api/help?text=test');

        $response->assertOk();
        Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook2) {
            return $job->webhookUrl === $webhook2->url && $job->text === 'test';
        });
    }

    /** @test */
    public function incoming_webhooks_update_the_timestamp_on_the_record_and_update_the_stats()
    {
        Bus::fake();
        $this->freezeTime();
        $webhook = Webhook::factory()->create(['url' => 'https://example.com/webhook/1234', 'updated_at' => now()->subDays(3), 'called_count' => 0]);

        $this->assertEquals(0, $webhook->fresh()->called_count);

        $response = $this->get('/api/help?text=test&c=' . $webhook->shortcode);

        $response->assertOk();
        Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
            return $job->webhookUrl === $webhook->url && $job->text === 'test';
        });
        $this->assertEquals(now()->format('Y-m-d'), $webhook->fresh()->updated_at->format('Y-m-d'));
        $this->assertEquals(1, $webhook->fresh()->called_count);

        $response = $this->get('/api/help?text=test&c=' . $webhook->shortcode);

        $response->assertOk();
        Bus::assertDispatched(SendToMSTeamsChannelJob::class, function ($job) use ($webhook) {
            return $job->webhookUrl === $webhook->url && $job->text === 'test';
        });
        $this->assertEquals(now()->format('Y-m-d'), $webhook->fresh()->updated_at->format('Y-m-d'));
        $this->assertEquals(2, $webhook->fresh()->called_count);
    }

    /** @test */
    public function if_the_querystring_is_missing_text_we_return_an_error()
    {
        Bus::fake();

        $response = $this->get('/api/help?sdfsdfsfd');

        $response->assertStatus(422);
        $response->assertSee('No message specified - no notification sent');
        Bus::assertNotDispatched(SendToMSTeamsChannelJob::class);
    }

    /** @test */
    public function if_the_querystring_has_an_invalid_webhook_code_we_return_an_error()
    {
        Bus::fake();

        $response = $this->get('/api/help?text=sdfsdfsfd?c=blah');

        $response->assertStatus(422);
        $response->assertSee('Invalid channel - no notification sent');
        Bus::assertNotDispatched(SendToMSTeamsChannelJob::class);
    }
}
