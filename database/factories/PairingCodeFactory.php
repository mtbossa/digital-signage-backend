<?php

namespace Database\Factories;

use App\Services\PairingCodeGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PairingCode>
 */
class PairingCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    
    public function definition()
    {
        $generator = new PairingCodeGeneratorService();
        $generated = $generator->generate();
        // This could lead to problems if it generates duplicates...
        return [
            'code' => $generated['code'],
            'expires_at' => $generated['expires_at']->format('Y-m-d H:i:s')
        ];
    }
}
