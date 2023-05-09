<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ClassesFactory extends Factory
{
    protected $model = Classes::class;
    private static $counter = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     //! sırayla sınıf ve id atamak istersen.

    public function definition(): array
    {
        $classNames = ['Math', 'Science', 'History', 'English', 'Sport'];

        return [
                'user_id' => User::factory()->create()->id,
                'class_name' => $classNames[self::$counter++ % count($classNames)],
        ];
    }
}


//! rastgele sınıf ve id atamak istersen.

// public function definition(): array
//     {
//         return [
//             'user_id' => rand(1, 10), // rastgele bir kullanıcı id'si atıyoruz
//             'class_name' => $this->faker->unique()->sentence(2), // rastgele bir sınıf ismi atıyoruz
//         ];
//     }



