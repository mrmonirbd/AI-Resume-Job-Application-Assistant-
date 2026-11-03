<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_only_contains_the_current_users_applications(): void
    {
        $application = $this->applicationFor(User::factory()->create());
        $other = $this->applicationFor(User::factory()->create());

        $this->actingAs($application->user)->get(route('applications.index'))
            ->assertOk()
            ->assertSee('Application pipeline')
            ->assertViewHas('applications', fn ($applications): bool => $applications->pluck('id')->all() === [$application->id])
            ->assertDontSee($other->job->title);
    }

    public function test_ajax_move_updates_the_application_job_and_timeline(): void
    {
        $application = $this->applicationFor(User::factory()->create());
        $this->actingAs($application->user)
            ->patchJson(route('applications.stage', $application), ['status' => 'applied'])
            ->assertOk()->assertExactJson(['id' => $application->id, 'status' => 'applied']);

        $application->refresh();
        $this->assertSame('applied', $application->status);
        $this->assertNotNull($application->applied_at);
        $this->assertSame('Moved to Applied', $application->activity_timeline[0]['label']);
        $this->assertDatabaseHas('jobs', ['id' => $application->job_id, 'stage' => 'applied', 'status' => 'applied']);

        $appliedAt = $application->applied_at->toDateTimeString();
        $this->actingAs($application->user)
            ->patchJson(route('applications.stage', $application), ['status' => 'applied'])->assertOk();
        $this->assertCount(1, $application->refresh()->activity_timeline);
        $this->assertSame($appliedAt, $application->applied_at->toDateTimeString());
    }

    public function test_invalid_moves_leave_the_application_unchanged(): void
    {
        $application = $this->applicationFor(User::factory()->create());
        $this->actingAs($application->user)
            ->patchJson(route('applications.stage', $application), ['status' => 'unknown'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');

        $this->assertSame('saved', $application->refresh()->status);
        $this->assertSame('saved', $application->job->stage);
        $this->assertEmpty($application->activity_timeline);
    }

    public function test_users_cannot_move_another_users_application(): void
    {
        $application = $this->applicationFor(User::factory()->create());
        $this->actingAs(User::factory()->create())
            ->patchJson(route('applications.stage', $application), ['status' => 'offer'])
            ->assertForbidden();

        $this->assertSame('saved', $application->refresh()->status);
        $this->assertSame('saved', $application->job->stage);
    }

    private function applicationFor(User $user): JobApplication
    {
        $job = $user->jobs()->create([
            'title' => fake()->unique()->sentence(3),
            'company' => 'Acme',
            'description' => 'Build useful products.',
            'status' => 'saved',
            'stage' => 'saved',
        ]);

        return $user->applications()->create(['job_id' => $job->id, 'status' => 'saved']);
    }
}
