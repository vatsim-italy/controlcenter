<?php

namespace Tests\Feature;

use App\Models\Evaluation;
use App\Models\EvaluationItem;
use App\Models\Training;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class TrainingEvaluationsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private Training $training;

    private Collection $evaluationItems;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a trainee with a training
        $this->trainee = User::factory()->create();
        $this->training = Training::factory()->create([
            'user_id' => $this->trainee->id,
        ]);
        $this->training->ratings()->attach(Rating::find(4));

        $this->evaluationItems = EvaluationItem::factory()->count(5)->create();

    }


    public function test_mentor_can_view_evaluation()
    {
        $mentor = User::factory()->create();
        $this->training->mentors()->attach($mentor, ['expire_at' => now()->addYear()]);

        $evaluation = Evaluation::factory()->create([
            'training_id' => $this->training->id,
            'examiner_id' => $mentor->id,
        ]);

        $this->actingAs($mentor)
            ->assertTrue(auth()->user()->can('view', $evaluation));
    }

    public function test_trainee_can_view_own_evaluation()
    {
        $evaluation = Evaluation::factory()->create([
            'training_id' => $this->training->id,
            'examiner_id' => User::factory()->create()->id,
        ]);

        $this->actingAs($this->training->user)
            ->assertTrue(auth()->user()->can('view', $evaluation));
    }

    public function test_regular_user_cannot_view_evaluation()
    {
        $evaluation = Evaluation::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->assertFalse(auth()->user()->can('view', $evaluation));
    }

    public function test_mentor_can_create_evaluation()
    {
        $mentor = User::factory()->create();
        $this->training->mentors()->attach($mentor, ['expire_at' => now()->addYear()]);

        $data = [
            'report_date' => now()->format('d/m/Y'),
            'startTime' => '10:00',
            'endTime' => '11:00',
            'sessionPerformed' => 'Training session',
            'complexity' => 'Medium',
            'workload' => 'High',
            'trafficLoad' => 50,
            'trainingPhase' => 'Initial',
            'finalReview' => 'Good',
            'position' => '1234',
            'results' => [
                1 => ['vote' => 'I', 'comment' => 'Excellent'],
                2 => ['vote' => 'S', 'comment' => 'Satisfactory'],
            ],
        ];

        $this->actingAs($mentor)
            ->post(route('training.report.store', ['training' => $this->training->id]), $data)
            ->assertRedirect(route('training.show', $this->training->id));

        $this->assertDatabaseHas('evaluations', [
            'training_id' => $this->training->id,
            'examiner_id' => $mentor->id,
        ]);

        $this->assertDatabaseHas('evaluation_results', [
            'vote' => 'I',
            'comment' => 'Excellent',
        ]);
    }

    public function test_regular_user_cannot_create_evaluation()
    {
        $data = Evaluation::factory()->make([
            'training_id' => $this->training->id,
        ])->getAttributes();

        $this->actingAs(User::factory()->create())
            ->post(route('training.report.store', ['training' => $this->training->id]), $data)
            ->assertStatus(403);
    }

    public function test_mentor_can_update_evaluation()
    {
        $mentor = User::factory()->create();
        $this->training->mentors()->attach($mentor, ['expire_at' => now()->addYear()]);
        $evaluation = Evaluation::factory()->create(['training_id' => $this->training->id, 'examiner_id' => $mentor->id]);

        $updateData = [
            'report_date' => now()->addDay()->format('d/m/Y'),
            'startTime' => '09:00',
            'endTime' => '10:00',
            'sessionPerformed' => 'Updated session',
            'complexity' => 'Low',
            'workload' => 'Medium',
            'trafficLoad' => 20,
            'trainingPhase' => 'Intermediate',
            'finalReview' => 'Updated',
            'position' => $evaluation->position,
            'results' => [
                1 => ['vote' => 'G', 'comment' => 'Good update'],
            ],
        ];

        $this->actingAs($mentor)
            ->patch(route('training.report.update', $evaluation), $updateData)
            ->assertRedirect(route('training.show', $evaluation->training_id));

        $this->assertDatabaseHas('evaluations', ['eval_id' => $evaluation->eval_id, 'finalReview' => 'Updated']);
    }

    public function test_regular_user_cannot_update_evaluation()
    {
        $evaluation = Evaluation::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->patch(route('training.report.update', $evaluation), ['finalReview' => 'Hacked'])
            ->assertStatus(403);
    }

}
