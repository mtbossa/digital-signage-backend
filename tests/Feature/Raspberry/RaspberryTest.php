<?php

namespace Tests\Feature\Raspberry;

use App\Mail\InstallationLink;
use App\Models\Raspberry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Raspberry\Traits\RaspberryTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RaspberryTest extends TestCase
{
    use RefreshDatabase, RaspberryTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
        $this->raspberry = $this->_createRaspberry();
    }

    /** @test */
    public function create_raspberry()
    {
        $raspberry_data = $this->_makeRaspberry()->toArray();

        $response = $this->postJson(route('raspberries.store'), $raspberry_data);

        $this->assertDatabaseHas('raspberries', $raspberry_data);

        $response->assertCreated()->assertJson($raspberry_data);
    }

    /** @test */
    public function update_raspberry()
    {
        $update_values = $this->_makeRaspberry()->toArray();

        $response = $this->putJson(route('raspberries.update', $this->raspberry->id), $update_values);

        $this->assertDatabaseHas('raspberries', $response->json());
        $response->assertJson($update_values)->assertOk();
    }

    /** @test */
    public function delete_raspberry()
    {
        $response = $this->deleteJson(route('raspberries.destroy', $this->raspberry->id));
        $this->assertDatabaseMissing('raspberries', ['id' => $this->raspberry->id]);
        $response->assertOk();
    }

    /** @test */
    public function fetch_single_raspberry()
    {
        $this->getJson(route('raspberries.show',
            $this->raspberry->id))->assertOk()->assertJson(['id' => $this->raspberry->id]);
    }

    /** @test */
    public function fetch_all_raspberries()
    {
        $second_raspberry = $this->_createRaspberry();

        $this->getJson(route('raspberries.index'))->assertOk()->assertJsonCount(2,
            'data')->assertJsonFragment(['id' => $this->raspberry->id]);
    }

    /** @test */
    public function when_search_params_is_set_but_searchColumn_not_should_throw_BadRequestException()
    {
        $search = 'Test';

        $this->getJson(route('raspberries.index', ['search' => $search]))->assertStatus(400)->assertJson([
            'message' => 'searchColumn parameter must be specified when search parameter is not empty.',
        ]);
    }

    /** @test */
    public function search_should_find_by_short_name()
    {
        $findOne = Raspberry::factory()->create(['short_name' => 'Raspberry 1']);
        $findTwo = Raspberry::factory()->create(['short_name' => 'Raspberry 2']);
        $notFind = Raspberry::factory()->create(['short_name' => 'Test']);

        $response = $this->getJson(route('raspberries.index', [
            'search' => 'Rasp', 'searchColumn' => 'short_name',
        ]))->assertOk();

        foreach ([$findOne, $findTwo] as $item) {
            $response->assertJsonFragment(['id' => $item->id]);
        }
        $response->assertJsonMissing(['id' => $notFind->id]);
    }

    /** @test */
    public function search_should_find_by_mac_address()
    {
        $find = Raspberry::factory()->create(['mac_address' => '04:BA:73:55:C8:F3']);

        $response = $this->getJson(route('raspberries.index', [
            'search' => '04:BA:73:55:C8:F3', 'searchColumn' => 'mac_address',
        ]))->assertOk();
        $response->assertJsonFragment(['id' => $find->id]);

        $notFind = Raspberry::factory()->create(['mac_address' => '01:CA:73:55:C8:F3']);
        $response = $this->getJson(route('raspberries.index', [
            'search' => '04:BA', 'searchColumn' => 'mac_address',
        ]))->assertOk();

        $response->assertJsonFragment(['id' => $find->id]);
        $response->assertJsonMissing(['id' => $notFind->id]);
    }

    /** @test */
    public function search_should_find_by_serial_number()
    {
        $find = Raspberry::factory()->create(['serial_number' => '12345']);

        $response = $this->getJson(route('raspberries.index', [
            'search' => '12345', 'searchColumn' => 'serial_number',
        ]))->assertOk();
        $response->assertJsonFragment(['id' => $find->id]);

        $notFind = Raspberry::factory()->create(['serial_number' => '6789']);
        $response = $this->getJson(route('raspberries.index', [
            'search' => '123', 'searchColumn' => 'serial_number',
        ]))->assertOk();

        $response->assertJsonFragment(['id' => $find->id]);
        $response->assertJsonMissing(['id' => $notFind->id]);
    }

    /** @test */
    public function after_raspberry_created_should_send_email_with_installation_link_to_raspberry_creator()
    {
        Mail::fake();

        $raspberry_data = Raspberry::factory()->make()->toArray();
        $response = $this->postJson(route('raspberries.store'), $raspberry_data);
        $response->assertCreated();

        Mail::assertQueued(InstallationLink::class);
    }

    /** @test */
    public function installer_email_should_have_correct_installer_download_url()
    {
        $raspberry = Raspberry::factory()->create();

        $mailable = new InstallationLink($raspberry);
        $apiUrl = env('APP_URL');
        $correctUrl = url("{$apiUrl}/api/raspberry/installer/download");

        $mailable->assertSeeInHtml($correctUrl);
    }
}
