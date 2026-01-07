<?php
// reservaciones/config/database.php

class Database {
    private $host;
    private $db_name = 'postgres'; // Default Supabase DB name
    private $username = 'postgres'; // Default Supabase User
    private $password;
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        // Load from environment or hardcoded for testing (prefer environment)
        $this->host = getenv('SUPABASE_DB_HOST') ?: 'db.your-supabase-project.supabase.co';
        $this->password = getenv('SUPABASE_DB_PASSWORD') ?: '';
        
        // Supabase usually provides a connection string, but we can decompose it
        // Or use the standard PostgreSQL port 5432
        
        try {
            $dsn = "pgsql:host=" . $this->host . ";port=5432;dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
