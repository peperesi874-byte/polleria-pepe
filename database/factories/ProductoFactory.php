<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Producto> */
class ProductoFactory extends Factory
{
    protected $model = Producto::class; // 👈 MUY IMPORTANTE

    public function definition(): array
    {
        return [
            'nombre'      => 'Pollo ' . $this->faker->unique()->word(),
            'descripcion' => $this->faker->optional()->sentence(),
            'precio'      => $this->faker->randomFloat(2, 60, 200),
            'stock'       => $this->faker->numberBetween(0, 200),
            'activo'      => $this->faker->boolean(90),
            'imagen'      => null,
        ];
    }
}
