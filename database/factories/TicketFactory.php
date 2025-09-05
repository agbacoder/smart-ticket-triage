<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    
    protected $model = Ticket::class;

    public function definition(): array
    {
        $subjects = [
            'Payment not going through',
            'App keeps crashing',
            'Need help with my account',
            'Refund request',
            'Login issues',
            'Error message when uploading file',
            'Question about subscription plan',
            'Invoice not received',
            'Password reset not working',
            'Feature request'
        ];

        $statuses = ['open', 'in_progress', 'closed'];
        $categories = ['billing', 'technical', 'general', null]; 

        return [
            'id'          => Str::ulid(),
            'subject'     => $this->faker->randomElement($subjects),
            'body'        => $this->faker->paragraphs(2, true),
            'status'      => 'open',
            'category'    => null,
            'note'        => null, 
            'explanation' => null,
            'confidence'  => null,
            'created_at'  => now()->subDays(rand(0, 14)),
            'updated_at'  => now(),

        ];
    }
}

