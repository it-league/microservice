<?php


namespace ITLeague\Microservice;

use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Support\Facades\Http;
use ITLeague\Microservice\Models\User;

abstract class TestCase extends \Laravel\Lumen\Testing\TestCase
{
    protected const errorStructure = [
        'error' => [
            'status',
            'title',
            'detail',
        ],
    ];

    protected Generator $faker;
    protected User $user;
    protected User $admin;
    protected User $superAdmin;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();

        $this->user = new User([
            'id' => $this->faker->uuid,
            'scope' => 'user'
        ]);
        $this->admin = new User([
            'id' => $this->faker->uuid,
            'scope' => 'admin'
        ]);
        $this->superAdmin = new User([
            'id' => $this->faker->uuid,
            'scope' => 'super-admin'
        ]);

        Http::fake([config('microservice.storage_uri') . '*' => Http::response(null, 204)]);
    }
}
