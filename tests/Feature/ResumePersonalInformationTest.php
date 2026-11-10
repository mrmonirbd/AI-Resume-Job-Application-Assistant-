<?php

namespace Tests\Feature;

use App\Livewire\ResumeBuilder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ResumePersonalInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_change_name_and_replace_photo_from_resume_editor(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile-photos/old.png', 'old photo');
        $user = User::factory()->create(['profile_photo_path' => 'profile-photos/old.png']);
        $resume = $user->resumes()->create(['title' => 'Master Resume', 'type' => 'master']);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->assertSee('Full name')
            ->assertSee('Profile picture')
            ->set('fullName', 'Taylor Morgan')
            ->set('profilePhoto', UploadedFile::fake()->image('portrait.png'))
            ->call('savePersonalInformation')
            ->assertHasNoErrors()
            ->assertSee('Taylor Morgan')
            ->assertSet('profilePhoto', null);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Taylor Morgan']);
        Storage::disk('public')->assertExists($user->refresh()->profile_photo_path);
        Storage::disk('public')->assertMissing('profile-photos/old.png');
    }

    public function test_invalid_personal_information_does_not_change_saved_profile(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'Original Name']);
        $resume = $user->resumes()->create(['title' => 'Master Resume', 'type' => 'master']);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('fullName', '')
            ->set('profilePhoto', UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'))
            ->call('savePersonalInformation')
            ->assertHasErrors(['fullName', 'profilePhoto']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Original Name', 'profile_photo_path' => null]);
        Storage::disk('public')->assertDirectoryEmpty('profile-photos');
    }

    public function test_another_user_cannot_access_resume_profile_editor(): void
    {
        $owner = User::factory()->create();
        $resume = $owner->resumes()->create(['title' => 'Master Resume', 'type' => 'master']);
        $otherUser = User::factory()->create();

        Livewire::actingAs($otherUser)->test(ResumeBuilder::class, ['resume' => $resume])
            ->assertForbidden();
    }
}
