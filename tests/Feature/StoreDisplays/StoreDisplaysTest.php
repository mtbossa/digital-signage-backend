<?php

namespace Tests\Feature\StoreDisplays;

use App\Models\Display;
use App\Models\Media;
use App\Models\Raspberry;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class StoreDisplaysTest extends TestCase
{
    use RefreshDatabase, AuthUserTrait;

    private Display $display;

    private Raspberry $raspberry;

    private Media $media;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
        $this->display = Display::factory()->create();
        $this->raspberry
            = Raspberry::factory()
            ->create(['display_id' => $this->display->id]);
        $this->media = Media::factory()->create();
        $this->store = Store::factory()->create();
    }

    /** @test */
    public function fetch_all_current_store_displays()
    {
        $this->withoutExceptionHandling();
        // Added a second display and raspberry because this route could
        // return the posts of the incorrect raspberry (the ones from $this->raspberry)
        // so need to make sure it's returning only the ones from the passed
        // so creates two to compare
        $secondStore = Store::factory()->create();
        $secondDisplay = Display::factory()
            ->create(['store_id' => $secondStore->id]);

        $displays = Display::factory(2)
            ->create(['store_id' => $this->store->id]);

        $response = $this->getJson(route('stores.displays.index',
            ['store' => $this->store->id])
        )->assertOk();

        foreach ($displays as $key => $display) {
            $response->assertJsonFragment(['id' => $display->id]);
        }
    }
}
