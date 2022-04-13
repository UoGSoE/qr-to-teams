<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_users_cant_see_the_homepage_dashboard()
    {
        $user = User::factory()->create();
        $hook1 = Webhook::factory()->create();
        $hook2 = Webhook::factory()->create();

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('auth.login'));
    }

    /** @test */
    public function users_can_see_see_the_homepage_dashboard()
    {
        $user = User::factory()->create();
        $hook1 = Webhook::factory()->create();
        $hook2 = Webhook::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('QR to Webhook');
        $response->assertSeeLivewire('webhook-editor');
        $response->assertSee($hook1->url);
        $response->assertSee($hook2->url);
    }

    /** @test */
    public function users_can_add_a_new_webhook()
    {
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
    }

    /** @test */
    public function users_can_delete_an_existing_webhook()
    {
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
    }
}
