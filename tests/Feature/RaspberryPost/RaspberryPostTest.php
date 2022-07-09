<?php

namespace Tests\Feature\RaspberryPost;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Raspberry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RaspberryPostTest extends TestCase
{
    use RefreshDatabase, DisplayTestsTrait, PostTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
        $this->display = $this->_createDisplay();
    }

    /** @test */
    public function fetch_all_display_posts()
    {
        $media = Media::factory()->create();
        $posts = Post::factory(2)->create(['media_id' => $media->id]);
        $raspberry = Raspberry::factory()
            ->create(['display_id' => $this->display->id]);


        foreach ($posts as $post) {
            $post->displays()->attach($this->display->id);
        }

        $response = $this->getJson(route('raspberry.posts.index',
                ['raspberry' => $raspberry->id])
        )->assertOk();
        foreach ($posts as $key => $post) {
            $response->assertJsonFragment(['id' => $post->id]);
        }
    }

    /** @test */
    public function ensure_json_structure_is_clean_and_correct()
    {
        $media = Media::factory()->create();
        $posts = Post::factory(2)->create(['media_id' => $media->id]);
        $raspberry = Raspberry::factory()
            ->create(['display_id' => $this->display->id]);

        $json_structure = [];
        foreach ($posts as $post) {
            $post->displays()->attach($this->display->id);

            $json_structure[] = [
                'id'          => $post->id,
                'start_date'  => $post->start_date,
                'end_date'    => $post->end_date,
                'start_time'  => $post->start_time,
                'end_time'    => $post->end_time,
                'expose_time' => $post->expose_time,
                'media'       => [
                    'id'       => $post->media->id,
                    'path'     => $post->media->path,
                    'type'     => $post->media->type,
                    'filename' => $post->media->filename
                ]
            ];
        }
        $complete_json = ['data' => $json_structure];

        $response = $this->getJson(route('raspberry.posts.index',
            ['raspberry' => $raspberry->id]))->assertOk();
        $response->assertExactJson($complete_json);
    }

    /** @test */
    public function ensure_only_posts_from_sent_display_id_are_returned()
    {
        $media = Media::factory()->create();
        $raspberry = Raspberry::factory()
            ->create(['display_id' => $this->display->id]);
        $posts = Post::factory(2)->create(['media_id' => $media->id]);
        foreach ($posts as $post) {
            $post->displays()->attach($this->display->id);
        }

        $display_two = Display::factory()->create();
        $post_for_display_two = Post::factory()
            ->create(['media_id' => $media->id]);
        $post_for_display_two->displays()->attach($display_two);

        $response = $this->getJson(route('raspberry.posts.index',
            ['raspberry' => $raspberry->id]))->assertOk();

        foreach ($posts as $key => $post) {
            $response->assertJsonFragment(['id' => $post->id]);
        }

        $response->assertJsonMissing(['id' => $post_for_display_two->id]);
    }

}
