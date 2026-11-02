<?php

namespace Tests\Feature;

use App\Actions\AI\RewriteResumeText;
use App\Livewire\ResumeBuilder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AiRewriteSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_nested_provider_response_can_be_displayed_accepted_and_saved(): void
    {
        config(['services.deepseek.key' => 'test-key', 'services.deepseek.base_url' => 'https://api.deepseek.com']);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.deepseek.com/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => json_encode([
                    'verified_user_information' => ['text' => 'Builds Laravel APIs.'],
                    'ai_rewrite' => ['text' => 'Develops reliable Laravel APIs.'],
                    'ai_recommendation' => [['text' => 'Check the facts.'], ['message' => 'Add verified metrics.']],
                ], JSON_THROW_ON_ERROR)]]],
                'model' => 'deepseek-chat',
            ]),
        ]);
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume', 'professional_summary' => 'Builds Laravel APIs.']);
        $key = 'resume-summary:'.$user->id;
        RateLimiter::clear($key);

        try {
            $component = Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
                ->call('improveSummary')
                ->assertHasNoErrors()
                ->assertSee('Develops reliable Laravel APIs.')
                ->assertSee('Check the facts.')
                ->assertSee('Add verified metrics.')
                ->call('acceptSuggestion')
                ->assertSet('overview.professional_summary', 'Develops reliable Laravel APIs.');
            $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'professional_summary' => 'Builds Laravel APIs.']);

            $component->call('saveOverview')->assertHasNoErrors();

            $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'professional_summary' => 'Develops reliable Laravel APIs.']);
            Http::assertSentCount(1);
        } finally {
            RateLimiter::clear($key);
        }
    }

    public function test_a_structured_suggestion_already_in_component_state_does_not_crash(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume', 'professional_summary' => 'Original summary']);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('aiSuggestion', [
                'ai_rewrite' => ['content' => ['text' => 'Structured draft summary']],
                'ai_recommendation' => ['message' => 'Review <script>alert(1)</script> before accepting.'],
            ])
            ->assertSee('Structured draft summary')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->call('acceptSuggestion')
            ->assertSet('overview.professional_summary', 'Structured draft summary');

        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'professional_summary' => 'Original summary']);
    }

    public function test_empty_or_invalid_rewrite_keeps_the_original_text(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume', 'professional_summary' => 'Keep this summary']);
        $component = Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume]);

        foreach ([null, [], false, 42, ['text' => null], ['metadata' => ['confidence' => 0.8]]] as $rewrite) {
            $component->set('aiSuggestion', ['ai_rewrite' => $rewrite, 'ai_recommendation' => null])
                ->call('acceptSuggestion')
                ->assertSet('overview.professional_summary', 'Keep this summary');
        }
        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'professional_summary' => 'Keep this summary']);
    }

    public function test_lists_of_rewrite_paragraphs_preserve_their_text(): void
    {
        $result = app(RewriteResumeText::class)->normalize([
            'ai_rewrite' => ['First paragraph.', ['text' => 'Second paragraph.']],
            'ai_recommendation' => false,
        ], 'Original');

        $this->assertSame("First paragraph.\nSecond paragraph.", $result['ai_rewrite']);
        $this->assertSame('Review this suggestion for factual accuracy before accepting it.', $result['ai_recommendation']);
    }
}
