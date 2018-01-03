<?php
 
/**
 * Handling database connection
 *
 * @author Ravi Tamada
 */
class DbConnect {
 
    private $conn;
    private $db_name;
 
    function __construct($region_code = null) {
        $this->db_name = DB_NAME;        
    }
 
    /**
     * Establishing database connection
     * @return database connection handler
     */
    function connect() {
        include_once dirname(__FILE__) . '/config.php';
 
        // Connecting to mysql database
        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, $this->db_name);
 
        // Check for database connection error
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
 
        // returing connection resource
        return $this->conn;
    }
 
}
 
?>