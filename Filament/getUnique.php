<?php

function getUniqueKeys(string $connectionName, string $table): array
{
    try {
        $db = DB::connection($connectionName);
        $database = $db->getDatabaseName();

        $query = "
            SELECT COLUMN_NAME
            FROM information_schema.statistics
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND NON_UNIQUE = 0
            AND INDEX_NAME <> 'PRIMARY'
        ";

        $results = $db->select($query, [$database, $table]);

        $keys = collect($results)->pluck('COLUMN_NAME')->unique()->values()->toArray();

        return !empty($keys) ? $keys : ['id'];
    } catch (\Throwable $e) {
        Log::error("Failed to get unique keys for table {$table}", [
            'connection' => $connectionName,
            'error' => $e->getMessage(),
        ]);
        return ['id'];
    }
}
