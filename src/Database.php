<?php
/**
 * This Software is part of aryelgois\Utils and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\Utils;

/**
 * Database wrapper for easier use to mysqli functions
 *
 * NOTES:
 * - You MUST extend this class to add some functionalities
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/Utils
 */
abstract class Database
{
    /**
     * The connection to the Database
     *
     * @access public
     * @var mysqli
     */
    public $connect;
    
    /**
     * Creates a new mysqli connection
     *
     * @param string $json   Path to a JSON file with the following structure.
     *                       An alias to the database is useful to keep the same
     *                       name in the code even if the actual database needs a
     *                       different name in another server.
     *     {
     *         "host": "DATABASE_HOST",
     *         "user": "DATABASE_USER",
     *         "password": "DATABASE_PASWORD",
     *         "databases": {
     *             "DATABASE_ALIAS_NAME": "DATABASE_NAME",
     *             ...
     *         }
     *     }
     * @param string $alias  Database alias name
     * @param string $encode Database encoding
     */
    public function __construct($json, $alias, $encode = 'utf8')
    {
        $db = json_decode(file_get_contents($json), true);
        $this->connect = new \mysqli($db['host'], $db['user'], $db['password'], $db['databases'][$alias]);
        $err = $this->connect->connect_error;
        if ($err) {
            $this->error($err);
        }
        $this->connect->query("SET NAMES '$encode'");
    }
    
    /**
     * Handles Database errors
     *
     * NOTES:
     * - This method must be changed by extensions to this class
     *
     * @param string $message The error message
     * @param mixed  $opt     Optional data for extending classes
     */
    public abstract function error($message, $opt = null);
    
    /**
     * Performs a query on the database and returns the fetched result.
     *
     * @param string $query The query string
     *
     * @return mixed
     *
     * @see http://php.net/manual/en/mysqli.query.php
     */
    public function query($query)
    {
        return $this->connect->query($query);
    }
    
    /**
     * Prepare an SQL statement for execution,
     * Binds variables to the prepared statement as parameters,
     * Executes the prepared Query,
     * Returns the fetched result.
     *
     * NOTES:
     * - The number of $arr values and the length of $types must match the '?'
     *   parameters in the $query
     *
     * @param string $query The query string
     * @param string $types A string which specify the types for the corresponding $arr values
     * @param array  $arr   Contains values to replace '?' in the query
     *
     * @return object(\mysqli_stmt)
     */
    public function prepare($query, $types, $arr)
    {
        $args = [$types];
        
        // Prepare the statement
        $stmt = $this->connect->prepare($query);
        
        // A hack to pass by reference
        foreach ($arr as &$v) {
            $args[] = &$v;
        }
        unset($v);
        
        // Bind values
        call_user_func_array([$stmt, 'bind_param'], $args);
        
        // Error handling
        if (!$stmt || !$stmt->execute() || $stmt->error !== '') {
            $this->error(!$stmt || $stmt->error == '' ? $this->connect->error : $stmt->error);
        }
        
        // Ok
        return $stmt;
    }
    
    /**
     * Fetches the data of a successful mysqli query
     *
     * @param object $mysqli A \mysqli_result object for query()
     *                       Or a \mysqli_stmt object for prepare()
     *
     * @return array Empty or with all fetched columns
     */
    public static function fetch($mysqli)
    {
        $array = [];
        if ($mysqli instanceof \mysqli_stmt)  {
            $mysqli->store_result();
            $variables = $data = [];
            $meta = $mysqli->result_metadata();
            while ($field = $meta->fetch_field()) {
                $variables[] = &$data[$field->name]; /* pass by reference */
            }
            call_user_func_array([$mysqli, 'bind_result'], $variables);
            $i = 0;
            while ($mysqli->fetch()) {
                $array[$i] = [];
                foreach ($data as $k => $v) {
                    $array[$i][$k] = $v;
                }
                $i++;
            }
        } elseif ($mysqli instanceof \mysqli_result) {
            while ($row = $mysqli->fetch_assoc()) {
                $array[] = $row;
            }
        } else {
            return false;
        }
        return $array;
    }
}
