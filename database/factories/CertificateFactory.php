<?php

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        $categories = ['SiKecil', 'Siaga', 'Penggalang', 'Penegak'];
        $awards = ['Participant', 'Winner'];

        return [
            'bebras_username' => 'del_' . $this->faker->unique()->userName(),
            'participant_name' => $this->faker->name(),
            'school_name' => 'SMP/SMA ' . $this->faker->city(),
            'category' => $this->faker->randomElement($categories),
            'award_type' => $this->faker->randomElement($awards),
            'year' => 2024,
            'file_path' => 'certificates/sample.pdf',
            'original_filename' => 'sample.pdf',
            'file_size' => 102400,
            'file_sha256' => hash('sha256', 'sample'),
        ];
    }
}
