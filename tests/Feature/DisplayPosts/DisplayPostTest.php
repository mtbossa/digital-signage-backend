<?php

namespace Tests\Feature\DisplayPosts;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Raspberry;
use App\Models\Recurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayPostTest extends TestCase
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
        $this->media = Media::factory()->create();
    }

    /** @test */
    public function fetch_all_current_raspberry_posts()
    {
        // Added a second display and raspberry because this route could
        // return the posts of the incorrect raspberry (the ones from $this->raspberry)
        // so need to make sure it's returning only the ones from the passed
        // so creates two to compare
        $secondDisplay = Display::factory()->create();
        $posts = Post::factory(2)->create(['media_id' => $this->media->id]);

        foreach ($posts as $post) {
            $post->displays()->attach($secondDisplay->id);
        }

        $response = $this->getJson(route('displays.posts.index',
                ['display' => $secondDisplay->id])
        )->assertOk();

        foreach ($posts as $key => $post) {
            $response->assertJsonFragment(['id' => $post->id]);
        }
    }

    /** @test */
    public function fetch_only_showing_posts()
    {
        $secondDisplay = Display::factory()->create();
        $showingPosts = Post::factory(2)->create([
            'media_id' => $this->media->id, 'showing' => true
        ]);
        $notShowingPosts = Post::factory(2)->create([
            'media_id' => $this->media->id, 'showing' => false
        ]);

        $allPosts = [...$showingPosts, ...$notShowingPosts];

        foreach ($allPosts as $post) {
            $post->displays()->attach($secondDisplay->id);
        }

        $response = $this->getJson(route('displays.posts.index',
                ['display' => $secondDisplay, 'showing' => true])
        )->assertOk();

        foreach ($showingPosts as $key => $post) {
            $response->assertJsonFragment(['id' => $post->id]);
        }

        foreach ($notShowingPosts as $key => $post) {
            $response->assertJsonMissing(['id' => $post->id]);
        }

    }

    /** @test */
    public function ensure_json_structure_is_clean_and_correct()
    {
        $recurrence = Recurrence::factory()->create();
        $posts = Post::factory(2)->create([
            'media_id'      => $this->media->id, 'showing' => true,
            'recurrence_id' => $recurrence->id
        ]);
        $nonRecurrentPost = Post::factory()->nonRecurrent()
            ->create(['media_id' => $this->media->id, 'showing' => true]);
        $posts = [...$posts, $nonRecurrentPost];

        $json_structure = [];
        foreach ($posts as $key => $post) {
            $post->displays()->attach($this->display->id);

            $structure = [
                'id'          => $post->id,
                'start_date'  => $post->start_date,
                'end_date'    => $post->end_date,
                'start_time'  => $post->start_time,
                'end_time'    => $post->end_time,
                'expose_time' => $post->expose_time,
                'showing'     => $post->showing,
                'media'       => [
                    'id'       => $post->media->id,
                    'path'     => $post->media->path,
                    'type'     => $post->media->type,
                    'filename' => $post->media->filename
                ],
            ];

            if ($post->recurrence) {
                $structure['recurrence'] = [
                    'isoweekday' => $post->recurrence->isoweekday,
                    'day'        => $post->recurrence->day,
                    'month'      => $post->recurrence->month,
                    'year'       => $post->recurrence->year
                ];
            }

            $json_structure[$key] = $structure;

        }
        $complete_json = ['data' => $json_structure];

        $response = $this->getJson(route('displays.posts.index',
            ['display' => $this->display->id]))->assertOk();
        $response->assertExactJson($complete_json);
    }

    /** @test */
    public function ensure_only_posts_from_sent_display_id_are_returned()
    {
        $posts = Post::factory(2)->create(['media_id' => $this->media->id]);

        foreach ($posts as $post) {
            $post->displays()->attach($this->display->id);
        }

        $display_two = Display::factory()->create();
        $post_for_display_two = Post::factory()
            ->create(['media_id' => $this->media->id]);
        $post_for_display_two->displays()->attach($display_two);

        $response = $this->getJson(route('displays.posts.index',
            ['display' => $this->display->id]))->assertOk();

        foreach ($posts as $key => $post) {
            $response->assertJsonFragment(['id' => $post->id]);
        }

        $response->assertJsonMissing(['id' => $post_for_display_two->id]);
    }

}
