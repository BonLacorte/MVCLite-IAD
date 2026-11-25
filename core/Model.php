<?php
class Model
{
    protected $db;

    // Optional $dbName parameter to select a database config
    public function __construct($dbName = 'default')
    {
        $configs = require '../config/config.php';

        if (!isset($configs[$dbName])) {
            die("DB Error: Database configuration '$dbName' not found.");
        }

        $config = $configs[$dbName];

        try {
            $this->db = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}",
                $config['user'],
                $config['pass']
            );
            // Optional: set error mode
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("DB Error: " . $e->getMessage());
        }
    }
}