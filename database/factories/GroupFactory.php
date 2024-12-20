<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    /**
     * O nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $model = Group::class;

    /**
     * Defina os dados do modelo para a fábrica.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            
        ];
    }
}

