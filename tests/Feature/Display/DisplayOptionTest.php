<?php

namespace Display;

use App\Models\Display;
use App\Models\Raspberry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Media\Traits\MediaTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayOptionTest extends TestCase
{
    use RefreshDatabase, MediaTestsTrait, WithFaker, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
    }

    /** @test */
    public function ensure_post_display_options_is_returning_correct_amount()
    {
        $amount = 10;
        Display::factory($amount)->create();

        $this->getJson(route('displays.options'))->assertOk()->assertJsonCount($amount);
    }

    /** @test */
    public function ensure_post_media_options_structure_is_correct()
    {
        $displays = Display::factory(2)->create();

        $correctStructure = $displays->map(function (Display $display) {
            return ['id' => $display->id, 'name' => $display->name];
        });

        $this->getJson(route('displays.options'))->assertExactJson($correctStructure->toArray());
    }

    /** @test */
    public function when_request_has_whereDoesntHaveRaspberry_should_return_only_displays_without_raspberries()
    {
        $displayWithRaspberry = Display::factory()->create();
        Raspberry::factory()->create(['display_id' => $displayWithRaspberry->id]);

        $displaysWithoutRaspberry = Display::factory(2)->create();
        $correctStructure = $displaysWithoutRaspberry->map(function (Display $display) {
            return ['id' => $display->id, 'name' => $display->name];
        });

        $this->getJson(route('displays.options',
            ['whereDoesntHaveRaspberry' => true]))->assertExactJson($correctStructure->toArray());
    }

    /** @test */
    public function when_request_has_withIds_should_return_other_options_and_the_requested_ids()
    {
        $displays = Display::factory(4)->create();
        $correctStructure = $displays->map(function (Display $display) {
            return ['id' => $display->id, 'name' => $display->name];
        });

        $this->getJson(route('displays.options',
            ['withIds' => $displays->pluck('id')]))->assertExactJson($correctStructure->toArray());
    }

    /** @test */
    public function when_request_has_withIds_and_whereDoesntHaveRaspberry_should_return_only_displays_without_rasperry_but_all_withIds_displays_as_well(
  ) {
        $unwatedDisplay = Display::factory()->create();
        Raspberry::factory()->create(['display_id' => $unwatedDisplay->id]);

        $wantedDisplays = Display::factory(3)->create();

        foreach ($wantedDisplays as $withIdsDisplay) {
            Raspberry::factory()->create(['display_id' => $withIdsDisplay->id]);
        }

        $displaysWithoutRaspberry = Display::factory(2)->create();

        $correctDisplays = collect([...$wantedDisplays, ...$displaysWithoutRaspberry]);

        $correctStructure = $correctDisplays->map(function (Display $display) {
            return ['id' => $display->id, 'name' => $display->name];
        });

        $withIds = $correctDisplays->pluck('id')->toArray();

        $this->getJson(route('displays.options',
            [
                'withIds' => json_encode($withIds), 'whereDoesntHaveRaspberry' => true,
            ]))->assertExactJson($correctStructure->toArray());
    }
}
