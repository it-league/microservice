<?php


namespace ITLeague\Microservice\Http\Helpers;


class DB
{
    public static function dropOnUpdateFunction()
    {
        \DB::statement('DROP FUNCTION IF EXISTS on_update() CASCADE;');
    }

    public static function dropOnInsertFunction()
    {
        \DB::statement('DROP FUNCTION IF EXISTS on_insert() CASCADE;');
    }

    public static function dropOnDeleteFunction()
    {
        \DB::statement('DROP FUNCTION IF EXISTS on_delete() CASCADE;');
    }

    public static function dropOnUpdateOrInsertRelationshipFunction(string $relationshipTableName)
    {
        \DB::statement('DROP FUNCTION IF EXISTS on_' . $relationshipTableName . '_update_or_insert() CASCADE;');
    }

    public static function dropOnUpdateTrigger(string $tableName)
    {
        \DB::statement('DROP TRIGGER IF EXISTS on_update_table ON ' . $tableName . ';');
    }

    public static function dropOnInsertTrigger(string $tableName)
    {
        \DB::statement('DROP TRIGGER IF EXISTS on_insert_table ON ' . $tableName . ';');
    }

    public static function dropOnDeleteTrigger(string $tableName)
    {
        \DB::statement('DROP TRIGGER IF EXISTS on_delete_table ON ' . $tableName . ';');
    }

    public static function dropOnUpdateRelationshipTrigger(string $relationshipTableName)
    {
        \DB::statement('DROP TRIGGER IF EXISTS on_update_or_insert_table ON ' . $relationshipTableName . ';');
    }
}
