<?php
/**
 * FluentPDO is simple and smart SQL query builder for PDO
 * @modify 2013-12-13 添加读写分离
 */

include_once 'FluentStructure.php';
include_once 'FluentUtils.php';
include_once 'FluentLiteral.php';
include_once 'BaseQuery.php';
include_once 'CommonQuery.php';
include_once 'SelectQuery.php';
include_once 'InsertQuery.php';
include_once 'UpdateQuery.php';
include_once 'DeleteQuery.php';

/**
 * Class FluentPDO
 */
class FluentPDO
{

    /**
     * @var array
     */
    private $_dbConnectPool = Array();

    /**
     * @var FluentStructure
     */
    private $pdo, $structure, $dbConfigs;

    /** @var boolean|callback */
    public $debug;

    /**
     * @param $db_configs
     * @param FluentStructure $structure
     */
    function __construct($db_configs, FluentStructure $structure = null)
    {
        $this->setConfigs($db_configs);
        if (!$structure) {
            $structure = new FluentStructure;
        }
        $this->structure = $structure;
    }

    /**Create SELECT query from $table use master linkpool
     * @param $table
     * @param $id
     * @return SelectQuery
     */
    public function mfrom($table, $id = null)
    {
        return $this->from($table, $id, true);
    }

    /** Create SELECT query from $table
     * @param string $table db table name
     * @param integer $id return one row by primary key
     * @param bool $isMaster set linkpool  master or slave
     * @return \SelectQuery
     */
    public function from($table, $id = null, $isMaster = false)
    {
        $this->setPdo($isMaster ? 'master' : 'slave');
        $query = new SelectQuery($this, $table);
        if ($id) {
            $tableTable = $query->getFromTable();
            $tableAlias = $query->getFromAlias();
            $primary = $this->structure->getPrimaryKey($tableTable);
            $query = $query->where("$tableAlias.$primary = ?", $id);
        }
        return $query;
    }

    /** Create INSERT INTO query
     *
     * @param string $table
     * @param array $values you can add one or multi rows array @see docs
     * @return \InsertQuery
     */
    public function insertInto($table, $values = array())
    {
        $this->setPdo('master');
        $query = new InsertQuery($this, $table, $values);
        return $query;
    }

    /** Create UPDATE query
     *
     * @param string $table
     * @param array|string $set
     * @param string $where
     * @param string $whereParams one or more params for where
     *
     * @return \UpdateQuery
     */
    public function update($table, $set = array(), $where = '', $whereParams = '')
    {
        $this->setPdo('master');
        $query = new UpdateQuery($this, $table, $set, $where);
        $query->set($set);
        $args = func_get_args();
        if (count($args) > 2) {
            array_shift($args);
            array_shift($args);
            if (is_null($args)) {
                $args = array();
            }
            $query = call_user_func_array(array($query, 'where'), $args);
        }
        return $query;
    }

    /** Create DELETE query
     *
     * @param string $tables
     * @param string $where
     * @param string $whereParams one or more params for where
     * @return \DeleteQuery
     */
    public function delete($tables, $where = '', $whereParams = '')
    {
        $this->setPdo('master');
        $query = new DeleteQuery($this, $tables);
        $args = func_get_args();
        if (count($args) > 1) {
            array_shift($args);
            if (is_null($args)) {
                $args = array();
            }
            $query = call_user_func_array(array($query, 'where'), $args);
        }
        return $query;
    }

    /** Create DELETE FROM query
     *
     * @param string $table
     * @param string $where
     * @param string $whereParams one or more params for where
     * @return \DeleteQuery
     */
    public function deleteFrom($table, $where = '', $whereParams = '')
    {
        $this->setPdo('master');
        $args = func_get_args();
        return call_user_func_array(array($this, 'delete'), $args);
    }

    /**
     *\Pdo beginTransaction
     */
    public function beginTransaction()
    {
        $this->setPdo('master');
        $this->pdo->beginTransaction();
    }

    /**
     *\Pdo beginTransaction
     */
    public function commit()
    {
        $this->setPdo('master');
        $this->pdo->commit();
    }

    /**
     *
     */
    public function rollBack()
    {
        $this->setPdo('master');
        $this->pdo->rollBack();
    }

    /**
     *
     * @param $db_configs
     */
    public function setConfigs($db_configs)
    {
        if (!isset($db_configs['slave'])) {
            $db_configs['slave'] = $db_configs['master'];
        }
        $this->dbConfigs = $db_configs;
    }

    /**
     * create dbConnectPool
     * @param $dbtype
     */
    public function setPdo($dbtype)
    {
        if (!isset($this->_dbConnectPool[$dbtype])) {
            $config = $this->dbConfigs[$dbtype][array_rand($this->dbConfigs[$dbtype])];
            $pdo = new PDO($config['dsn'], $config['username'], $config['password'], array(PDO::ATTR_TIMEOUT => (isset($config['timeout']) ? $config['timeout'] : 30)));
            $pdo->query("set names " . $config['charset']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
            $this->_dbConnectPool[$dbtype] = $pdo;
        }
        $this->pdo = $this->_dbConnectPool[$dbtype];
    }

    /** @return \PDO
     */
    public function getPdo()
    {
        if (!$this->pdo) $this->setPdo('master');
        return $this->pdo;
    }


    /** @return \FluentStructure
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
