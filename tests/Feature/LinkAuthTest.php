<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_links(): void
    {
        $response = $this->getJson('/api/links');

        $response->assertStatus(401);
    }

    public function test_authenticated_users_only_see_their_own_links(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Link::create(['slug' => 'abc123', 'original_url' => 'https://example.com', 'user_id' => $user->id]);
        Link::create(['slug' => 'xyz789', 'original_url' => 'https://example.org', 'user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/links');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['slug' => 'abc123']);
    }

    public function test_guests_cannot_delete_links(): void
    {
        $owner = User::factory()->create();
        $link = Link::create(['slug' => 'abc123', 'original_url' => 'https://example.com', 'user_id' => $owner->id]);

        $response = $this->deleteJson("/api/links/{$link->slug}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('links', ['slug' => 'abc123']);
    }

    public function test_owners_can_delete_their_own_links(): void
    {
        $user = User::factory()->create();
        $link = Link::create(['slug' => 'abc123', 'original_url' => 'https://example.com', 'user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/links/{$link->slug}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('links', ['slug' => 'abc123']);
    }

    public function test_users_cannot_delete_links_they_do_not_own(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $link = Link::create(['slug' => 'abc123', 'original_url' => 'https://example.com', 'user_id' => $owner->id]);

        $response = $this->actingAs($otherUser, 'sanctum')->deleteJson("/api/links/{$link->slug}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('links', ['slug' => 'abc123']);
    }

    public function test_redirect_by_slug_works_without_auth(): void
    {
        $link = Link::create(['slug' => 'abc123', 'original_url' => 'https://example.com']);

        $response = $this->get("/{$link->slug}");

        $response->assertStatus(302);
        $response->assertRedirect('https://example.com');
        $this->assertDatabaseCount('clicks', 1);
    }
}
