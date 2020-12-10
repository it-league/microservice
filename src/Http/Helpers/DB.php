<?php


namespace ITLeague\Microservice\Http\Helpers;


class DB
{
    public static function createJsonToArrayFunction()
    {
        \DB::statement(
            'CREATE OR REPLACE FUNCTION json_to_array(_js json)
                    RETURNS text[] LANGUAGE sql IMMUTABLE PARALLEL SAFE AS
                \'SELECT ARRAY(SELECT json_array_elements_text(_js))\''
        );
    }

    public static function dropJsonToArrayFunction()
    {
        \DB::statement('DROP FUNCTION IF EXISTS json_to_array(json) CASCADE');
    }

    public static function createOnUpdateFunction()
    {
        \DB::statement(
            'CREATE OR REPLACE FUNCTION on_update() RETURNS TRIGGER AS $$
            BEGIN
                IF row (NEW.*) IS DISTINCT FROM row (OLD.*) THEN
                    NEW.created_at = OLD.created_at;
                    NEW.created_by = OLD.created_by;
                    NEW.updated_at = LOCALTIMESTAMP;

                    IF (NEW.deleted_at IS NOT NULL) THEN
                        IF (OLD.deleted_at IS NULL) THEN

                            IF (NEW.deleted_by IS NULL) THEN
                                RAISE EXCEPTION \'null value in column "deleted_by" violates not-null constraint\' USING ERRCODE = \'23502\';
                            END IF;
                            NEW.deleted_at = LOCALTIMESTAMP;

                        ELSE
                            RAISE EXCEPTION \'Can`t change deleted row!\' USING ERRCODE = \'23001\';
                        END IF;

                    ELSE
                        NEW.deleted_by = NULL;
                    END IF;

                    RETURN NEW;
                ELSE
                    RETURN OLD;
                END IF;
            END;
            $$ language \'plpgsql\';'
        );
    }

    public static function createImmutablePrimaryFunction(string $tableName, string $primaryKey)
    {
        \DB::statement(
            'CREATE OR REPLACE FUNCTION immutable_primary_on_' . $tableName . '_update()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.' . $primaryKey . ' = OLD.' . $primaryKey . ';
                RETURN NEW;
            END;
            $$ language \'plpgsql\''
        );
    }

    public static function setImmutablePrimary(string $tableName, string $primaryKey = 'id')
    {
        self::createImmutablePrimaryFunction($tableName, $primaryKey);
        self::createImmutablePrimaryTrigger($tableName);
    }

    public static function createImmutablePrimaryTrigger(string $tableName)
    {
        self::dropImmutablePrimaryTrigger($tableName);
        \DB::statement(
            'CREATE TRIGGER immutable_primary BEFORE UPDATE ON ' . $tableName . ' FOR EACH ROW EXECUTE FUNCTION immutable_primary_on_' . $tableName . '_update()'
        );
    }

    public static function dropImmutablePrimaryTrigger(string $tableName)
    {
        \DB::statement('DROP TRIGGER IF EXISTS immutable_primary ON ' . $tableName);
    }

    public static function dropImmutablePrimaryFunction(string $tableName)
    {
        \DB::statement('DROP FUNCTION IF EXISTS immutable_primary_on_' . $tableName . '_update() CASCADE');
    }

    public static function createOnInsertFunction()
    {
        \DB::statement(
            'CREATE OR REPLACE FUNCTION on_insert()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.created_at = LOCALTIMESTAMP;
                NEW.updated_at = LOCALTIMESTAMP;
                IF (NEW.deleted_at IS NOT NULL) THEN
                    RAISE EXCEPTION \'cant`t create deleted row\' USING ERRCODE = \'23001\';
                END IF;
                NEW.deleted_by = NULL;
                RETURN NEW;
            END;
            $$ language \'plpgsql\';'
        );
    }

    public static function createOnDeleteFunction()
    {
        \DB::statement(
            'CREATE OR REPLACE FUNCTION on_delete()
            RETURNS TRIGGER AS $$
            BEGIN
                RAISE EXCEPTION \'Impossible to delete row! Use soft delete.\' USING ERRCODE = \'23001\';
            END;
            $$ language \'plpgsql\';'
        );
    }

    public static function createOnUpdateOrInsertRelationshipFunction(string $dataTableName, string $relationshipTableName, string $dataKey, string $foreignKey)
    {
        \DB::statement(
            'CREATE OR REPLACE FUNCTION on_' . $relationshipTableName . '_update_or_insert()
                RETURNS TRIGGER AS $$
                BEGIN
                   IF row(NEW.*) IS DISTINCT FROM row(OLD.*) THEN
                      UPDATE ' . $dataTableName . ' d SET updated_at = LOCALTIMESTAMP WHERE d.' . $dataKey . ' = NEW.' . $foreignKey . ';
                   END IF;
                   RETURN NEW;
                END;
                $$ language \'plpgsql\';'
        );
    }

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

    public static function createOnUpdateTrigger(string $tableName)
    {
        self::dropOnUpdateTrigger($tableName);
        \DB::statement('CREATE TRIGGER on_update_table BEFORE UPDATE ON ' . $tableName . ' FOR EACH ROW EXECUTE FUNCTION on_update();');
    }

    public static function createOnInsertTrigger(string $tableName)
    {
        self::dropOnInsertTrigger($tableName);
        \DB::statement('CREATE TRIGGER on_insert_table BEFORE INSERT ON ' . $tableName . ' FOR EACH ROW EXECUTE FUNCTION on_insert();');
    }

    public static function createOnDeleteTrigger(string $tableName)
    {
        self::dropOnDeleteTrigger($tableName);
        \DB::statement('CREATE TRIGGER on_delete_table BEFORE DELETE ON ' . $tableName . ' FOR EACH ROW EXECUTE FUNCTION on_delete();');
    }

    public static function createOnUpdateOrInsertRelationshipTrigger(string $relationshipTableName)
    {
        self::dropOnUpdateRelationshipTrigger($relationshipTableName);
        \DB::statement(
            'CREATE TRIGGER on_update_or_insert_table AFTER UPDATE OR INSERT ON ' . $relationshipTableName . ' FOR EACH ROW EXECUTE FUNCTION on_' . $relationshipTableName . '_update_or_insert();'
        );
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
