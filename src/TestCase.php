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

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();

        $this->user = new User(
            [
                'id' => $this->faker->uuid,
                'roles' => ['user']
            ]
        );

        $this->admin = new User(
            [
                'id' => $this->faker->uuid,
                'roles' => ['admin']
            ]
        );

        $storageBaseUri = config('microservice.services.storage.base_uri') . '/' . config('microservice.storage.prefix') . '/';
        Http::fake(
            [
                $storageBaseUri . 'upload' => Http::response([], 200),
                $storageBaseUri . 'upload/force' => Http::response([], 200),
                $storageBaseUri . 'info/*' => Http::response([], 200),
                $storageBaseUri . 'confirm/*' => Http::response(null, 204),
                $storageBaseUri . 'delete/*' => Http::response(null, 204),
                $storageBaseUri . 'permission/*' => Http::response(null, 204),
            ]
        );
    }
}
