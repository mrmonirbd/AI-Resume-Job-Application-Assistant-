<?php

namespace Tests\Feature;

use App\Actions\Resumes\DuplicateResume;
use App\Livewire\ResumeBuilder;
use App\Models\ResumeTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ResumePersonalInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_contact_details_and_photo_for_only_one_resume(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile-photos/old.png', 'old photo');
        $user = User::factory()->create(['name' => 'Account Name', 'profile_photo_path' => 'profile-photos/old.png']);
        $resume = $user->resumes()->create(['title' => 'Master Resume', 'type' => 'master']);

        $otherResume = $user->resumes()->create(['title' => 'Another Resume', 'type' => 'general']);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->assertSee('Full name')
            ->assertSee('Profile picture')
            ->set('fullName', 'Taylor Morgan')
            ->set('contactEmail', 'taylor@example.com')
            ->set('profilePhoto', UploadedFile::fake()->image('portrait.png'))
            ->call('savePersonalInformation')
            ->assertHasNoErrors()
            ->assertSee('Taylor Morgan')
            ->assertSee('taylor@example.com')
            ->assertSet('profilePhoto', null);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Account Name', 'email' => $user->email, 'profile_photo_path' => 'profile-photos/old.png']);
        $resume->refresh();
        $this->assertSame('Taylor Morgan', $resume->contactName());
        $this->assertSame('taylor@example.com', $resume->contactEmail());
        Storage::disk('public')->assertExists($resume->profilePhotoPath());
        Storage::disk('public')->assertExists('profile-photos/old.png');
        $this->assertSame($user->email, $otherResume->fresh()->contactEmail());
        $this->assertSame('profile-photos/old.png', $otherResume->fresh()->profilePhotoPath());
        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume->fresh()])
            ->assertSet('fullName', 'Taylor Morgan')
            ->assertSet('contactEmail', 'taylor@example.com');
    }

    public function test_invalid_personal_information_does_not_change_saved_profile(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'Original Name']);
        $resume = $user->resumes()->create(['title' => 'Master Resume', 'type' => 'master']);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('fullName', '')
            ->set('contactEmail', 'invalid-email')
            ->set('profilePhoto', UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'))
            ->call('savePersonalInformation')
            ->assertHasErrors(['fullName', 'contactEmail', 'profilePhoto']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Original Name', 'profile_photo_path' => null]);
        Storage::disk('public')->assertDirectoryEmpty('resume-photos');
        $this->assertNull($resume->refresh()->settings);
    }

    public function test_saved_resume_keeps_its_photo_after_account_photo_is_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile-photos/account.png', 'account photo');
        $user = User::factory()->create(['profile_photo_path' => 'profile-photos/account.png']);
        $resume = $user->resumes()->create(['title' => 'Resume', 'type' => 'general', 'settings' => ['existing' => true]]);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->call('savePersonalInformation')
            ->assertHasNoErrors();
        Storage::disk('public')->delete('profile-photos/account.png');

        $resume->refresh();
        Storage::disk('public')->assertExists($resume->profilePhotoPath());
        $this->assertTrue($resume->settings['existing']);
    }

    public function test_duplicate_photo_survives_replacement_in_original_resume(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'Resume', 'type' => 'general']);
        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('profilePhoto', UploadedFile::fake()->image('first.png'))
            ->call('savePersonalInformation')
            ->assertHasNoErrors();
        $resume->refresh();
        $copy = app(DuplicateResume::class)->handle($resume);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('profilePhoto', UploadedFile::fake()->image('second.png'))
            ->call('savePersonalInformation')
            ->assertHasNoErrors();

        Storage::disk('public')->assertExists($copy->profilePhotoPath());
        $this->assertNotSame($copy->profilePhotoPath(), $resume->refresh()->profilePhotoPath());
    }

    public function test_pdf_output_uses_resume_contact_details_and_photo(): void
    {
        Storage::fake('public');
        $photo = UploadedFile::fake()->image('portrait.png');
        $photoPath = $photo->store('resume-photos', 'public');
        $user = User::factory()->create(['name' => 'Account Name', 'email' => 'account@example.com']);
        $template = ResumeTemplate::create([
            'name' => 'Photo Template', 'slug' => 'photo-test', 'is_active' => true,
            'category' => 'Professional', 'layout' => 'single-column',
            'settings' => ['style' => 'dark-header', 'photo' => true],
        ]);
        $resume = $user->resumes()->create([
            'title' => 'Resume', 'type' => 'general', 'template_id' => $template->id,
            'settings' => ['personal_information' => [
                'name' => 'Resume Name', 'email' => 'resume@example.com', 'profile_photo_path' => $photoPath,
            ]],
        ]);

        $html = view('pdf.resume', ['resume' => $resume])->render();

        $this->assertStringContainsString('Resume Name', $html);
        $this->assertStringContainsString('resume@example.com', $html);
        $this->assertStringNotContainsString('account@example.com', $html);
        $this->assertStringContainsString(base64_encode(Storage::disk('public')->get($photoPath)), $html);
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
