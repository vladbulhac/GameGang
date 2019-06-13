<?php
class database
{
    public $conn;
    public function __construct($dbhost = 'localhost', $dbuser = 'root', $dbpass = 'root', $dbname = 'gamegang ', $charset = 'utf8')
    {
        $this->conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
        if ($this->conn->connect_error) {
            die('Could not connect to MySql: ' . $this->conn->connect_error);
        }
        $this->conn->set_charset($charset);
    }
}
