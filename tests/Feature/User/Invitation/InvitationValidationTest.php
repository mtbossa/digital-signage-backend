<?php

namespace Tests\Feature\User\Invitation;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\Feature\User\Invitation\Traits\InvitationTestsTrait;
use Tests\TestCase;

class InvitationValidationTest extends TestCase
{
    use RefreshDatabase, InvitationTestsTrait, AuthUserTrait, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
    }

    /** @test */
    public function email_must_be_unique_in_invitations()
    {
        $this->invitation = $this->_createwithTokenInvitation(['inviter' => $this->user->id]);

        $this->postJson(route('invitations.store'), ['email' => $this->invitation->email, 'is_admin' => $this->invitation->is_admin])
          ->assertJsonValidationErrorFor('email')
          ->assertUnprocessable();

        $this->assertDatabaseCount('invitations', 1); // only the one create for tests
    }

    /**
     * @test
     *
     * @dataProvider invalidInvitations
     */
    public function cant_store_invalid_invitation($invalidData, $invalidFields)
    {
        $store = Store::factory()->create();
        $response = $this->postJson(route('invitations.store'), [...$store->toArray(), ...$invalidData])
          ->assertJsonValidationErrors($invalidFields)
          ->assertUnprocessable();

        $this->assertDatabaseCount('invitations', 0); // only the one create for tests
    }

    public function invalidInvitations(): array
    {
        $email_data = ['email' => 'mateus.ebossa@hotmail.com'];

        return [
            'email as null' => [['email' => null], ['email']],
            'email as number' => [['email' => 1], ['email']],
            'email as not email' => [['email' => Str::random(20)], ['email']],
            'email greater than 255' => [['email' => Str::random(255).'@gmail.com'], ['email']],
            'store that doesn\'t exists' => [[...$email_data, 'store_id' => 100000], ['store_id']],
            'store as string' => [[...$email_data, 'store_id' => 'a'], ['store_id']],
            'is_admin as null' => [[...$email_data, 'is_admin' => null], ['is_admin']],
            'is_admin as number different from 0 or 1' => [[...$email_data, 'is_admin' => 3], ['is_admin']],
            'is_admin string different from true or false' => [[...$email_data, 'is_admin' => 'oi'], ['is_admin']],
        ];
    }
}
