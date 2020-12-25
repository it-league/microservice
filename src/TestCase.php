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

        $this->user = new User(
            [
                'id' => $this->faker->uuid,
                'scope' => 'user'
            ]
        );
        $this->admin = new User(
            [
                'id' => $this->faker->uuid,
                'scope' => 'admin'
            ]
        );
        $this->superAdmin = new User(
            [
                'id' => $this->faker->uuid,
                'scope' => 'super-admin'
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

    public function user(): array
    {
        return [
            'x-authenticated-userid' => $this->user->id,
            'x-authenticated-scope' => $this->user->scope
        ];
    }

    public function admin(): array
    {
        return [
            'x-authenticated-userid' => $this->admin->id,
            'x-authenticated-scope' => $this->admin->scope
        ];
    }

    public function superAdmin(): array
    {
        return [
            'x-authenticated-userid' => $this->superAdmin->id,
            'x-authenticated-scope' => $this->superAdmin->scope
        ];
    }
}
