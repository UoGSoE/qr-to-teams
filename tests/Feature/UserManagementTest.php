<?php

namespace Tests\Feature;

use App\Http\Livewire\UserList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Ohffs\Ldap\FakeLdapConnection;
use Ohffs\Ldap\LdapConnectionInterface;
use Ohffs\Ldap\LdapUser;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_see_the_user_management_page()
    {
        $response = $this->get(route('user.index'));

        $response->assertRedirect(route('auth.login'));
    }

    /** @test */
    public function existing_users_can_see_the_user_management_page()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1)->get(route('user.index'));

        $response->assertOk();
        $response->assertSee('User Management');
        $response->assertSee($user1->username);
        $response->assertSee($user2->username);
        $response->assertSeeLivewire('user-list');
    }

    /** @test */
    public function users_can_delete_an_existing_user_but_not_themselves()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Livewire::actingAs($user1)
            ->test(UserList::class)
            ->assertSee($user1->username)
            ->assertSee($user2->username)
            ->call('deleteUser', $user2->id)
            ->assertSee($user1->username)
            ->assertDontSee($user2->username)
            ->call('deleteUser', $user1->id)
            ->assertSee($user1->username)
            ->assertDontSee($user2->username);
    }

    /** @test */
    public function users_can_add_a_new_ldap_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->fakeLdapConnection();
        \Ldap::shouldReceive('findUser')->with('abc1x')->andReturn(new LdapUser([
            [
                'uid' => ['abc1x'],
                'mail' => ['abc1x@example.com'],
                'sn' => ['smith'],
                'givenname' => ['jenny'],
                'telephonenumber' => ['12345'],
            ],
        ]));

        Livewire::actingAs($user1)
            ->test(UserList::class)
            ->assertSee($user1->username)
            ->assertSee($user2->username)
            ->assertDontSee('abc1x@example.com')
            ->set('username', 'abc1x')
            ->call('lookupUser')
            ->assertSet('email', 'abc1x@example.com')
            ->call('createUser')
            ->assertSee('abc1x@example.com')
            ->assertSet('email', '');

        $this->assertDatabaseHas('users', [
            'username' => 'abc1x',
            'surname' => 'smith',
            'forenames' => 'jenny',
            'email' => 'abc1x@example.com',
        ]);
    }

    /** @test */
    public function users_cant_add_a_user_that_doesnt_exist_in_ldap()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->fakeLdapConnection();
        \Ldap::shouldReceive('findUser')->with('abc1x')->andReturn(false);

        Livewire::actingAs($user1)
            ->test(UserList::class)
            ->assertSee($user1->username)
            ->assertSee($user2->username)
            ->assertDontSee('abc1x@example.com')
            ->set('username', 'abc1x')
            ->call('lookupUser')
            ->assertSet('email', '')
            ->assertSet('error', 'User not found')
            ->call('createUser');

        $this->assertDatabaseMissing('users', [
            'username' => 'abc1x',
        ]);
    }

    /** @test */
    public function users_cant_add_the_same_user_twice()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create(['username' => 'abc1x']);
        $this->fakeLdapConnection();
        \Ldap::shouldReceive('findUser')->with('abc1x')->andReturn(new LdapUser([
            [
                'uid' => ['abc1x'],
                'mail' => ['abc1x@example.com'],
                'sn' => ['smith'],
                'givenname' => ['jenny'],
                'telephonenumber' => ['12345'],
            ],
        ]));

        Livewire::actingAs($user1)
            ->test(UserList::class)
            ->assertSee($user1->username)
            ->assertSee($user2->username)
            ->set('username', 'abc1x')
            ->call('lookupUser')
            ->assertSet('email', '')
            ->assertSet('error', 'User already exists')
            ->call('createUser');

        $this->assertEquals(1, User::where('username', '=', 'abc1x')->count());
    }

    private function fakeLdapConnection()
    {
        $this->instance(
            LdapConnectionInterface::class,
            new FakeLdapConnection('up', 'whatever')
        );
    }
}
