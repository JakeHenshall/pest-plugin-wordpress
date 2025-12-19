<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class DatabaseHelpers
{
    /**
     * Validate table name to prevent SQL injection.
     * Only allows alphanumeric characters and underscores.
     */
    private static function validateIdentifier(string $identifier, string $type = 'identifier'): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            throw new \InvalidArgumentException(
                "Invalid {$type} name '{$identifier}'. Only alphanumeric characters and underscores are allowed."
            );
        }
    }
    
    public static function truncate(string $table): void
    {
        global $wpdb;
        
        self::validateIdentifier($table, 'table');
        
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}{$table}");
    }
    
    public static function seed(string $table, array $data): void
    {
        global $wpdb;
        
        self::validateIdentifier($table, 'table');
        
        foreach ($data as $row) {
            // Validate all column names
            foreach (array_keys($row) as $column) {
                self::validateIdentifier($column, 'column');
            }
            
            $wpdb->insert($wpdb->prefix . $table, $row);
        }
    }
    
    public static function assertDatabaseHas(string $table, array $data): void
    {
        global $wpdb;
        
        self::validateIdentifier($table, 'table');
        
        if (empty($data)) {
            throw new \InvalidArgumentException('Data array cannot be empty for assertDatabaseHas');
        }
        
        $where = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            self::validateIdentifier($column, 'column');
            $where[] = "`{$column}` = %s";
            $values[] = $value;
        }
        
        $whereClause = implode(' AND ', $where);
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}{$table} WHERE {$whereClause}",
            ...$values
        );
        
        $count = (int) $wpdb->get_var($query);
        
        test()->assertGreaterThan(0, $count, "Failed asserting that table '{$table}' contains matching row");
    }
    
    public static function assertDatabaseMissing(string $table, array $data): void
    {
        global $wpdb;
        
        self::validateIdentifier($table, 'table');
        
        if (empty($data)) {
            throw new \InvalidArgumentException('Data array cannot be empty for assertDatabaseMissing');
        }
        
        $where = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            self::validateIdentifier($column, 'column');
            $where[] = "`{$column}` = %s";
            $values[] = $value;
        }
        
        $whereClause = implode(' AND ', $where);
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}{$table} WHERE {$whereClause}",
            ...$values
        );
        
        $count = (int) $wpdb->get_var($query);
        
        test()->assertEquals(0, $count, "Failed asserting that table '{$table}' does not contain matching row");
    }
    
    public static function assertDatabaseCount(string $table, int $count): void
    {
        global $wpdb;
        
        self::validateIdentifier($table, 'table');
        
        $actual = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}{$table}");
        
        test()->assertEquals($count, $actual, "Expected {$count} rows in table '{$table}', found {$actual}");
    }
}

function truncateTable(string $table): void
{
    DatabaseHelpers::truncate($table);
}

function seedTable(string $table, array $data): void
{
    DatabaseHelpers::seed($table, $data);
}

function assertDatabaseHas(string $table, array $data): void
{
    DatabaseHelpers::assertDatabaseHas($table, $data);
}

function assertDatabaseMissing(string $table, array $data): void
{
    DatabaseHelpers::assertDatabaseMissing($table, $data);
}

function assertDatabaseCount(string $table, int $count): void
{
    DatabaseHelpers::assertDatabaseCount($table, $count);
}

