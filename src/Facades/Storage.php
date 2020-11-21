<?php


namespace ITLeague\Microservice\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * @method static void confirm(string $fileId, array|null $permission = [], array|null $sizes = [])
 * @method static void permission(string $fileId, array|null $permission = [])
 * @method static void delete(string $fileId)
 * @method static array info(string $fileId)
 * @method static array upload(resource|string $file, string|null $filename = null, string|null $path = 'upload')
 * @method static array uploadForce(resource|string $file, string|null $filename = null, string|null $path = 'upload', array|null $permission = [], array|null $sizes = [])
 */
class Storage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'storage.service';
    }
}
