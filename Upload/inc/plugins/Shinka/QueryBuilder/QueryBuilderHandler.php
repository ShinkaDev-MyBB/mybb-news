<?php

namespace Shinka\QueryBuilder;

// use Shinka\QueryBuilder\Adapter;

require_once "../Adapter.php";
require_once "../JoinBuilder.php";

class Shinka_QueryBuilder_QueryBuilderHandler
{

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var array
     */
    protected $statements = array();

    /**
     * @var null|string
     */
    protected $tablePrefix = null;

    /**
     * @var \Shinka\QueryBuilder\Adapters\BaseAdapter
     */
    protected $adapterInstance;

    /**
     * @param null|\Shinka\Pixie\Connection $connection
     *
     * @param int $fetchMode
     * @throws \Exception
     */
    public function __construct($db = null, $tablePrefix = null)
    {
        // if (is_null($db)) {
        //     throw new \Exception('No database connection found.', 1);
        // }

        $this->db = $db;
        $this->tablePrefix = $tablePrefix;
        $this->adapterInstance = new Shinka_QueryBuilder_Adapter;
    }

    /**
     * @param null|DB $db
     * @return QueryBuilderHandler
     * @throws \Exception
     */
    public function newQuery(DB $db = null)
    {
        if (is_null($db)) {
            $db = $this->db;
        }

        return new static($db);
    }

    /**
     * @param       $sql
     * @param array $bindings
     *
     * @return $this
     */
    public function query($sql, $bindings = array())
    {
        $this->statement($sql, $bindings);

        return $this;
    }

    /**
     * @param       $sql
     * @param array $bindings
     *
     * @return array PDOStatement and execution time as float
     */
    public function statement($sql, $bindings = array())
    {
        // $start = microtime(true);
        // $pdoStatement = $this->pdo->prepare($sql);
        // foreach ($bindings as $key => $value) {
        //     $pdoStatement->bindValue(
        //         is_int($key) ? $key + 1 : $key,
        //         $value,
        //         is_int($value) || is_bool($value) ? PDO::PARAM_INT : PDO::PARAM_STR
        //     );
        // }
        // $pdoStatement->execute();
        // return array($pdoStatement, microtime(true) - $start);
    }

    /**
     * Get all rows
     *
     * @return \stdClass|array
     * @throws \Exception
     */
    public function get()
    {
        // if (is_null($this->pdoStatement)) {
        $queryObject = $this->getQuery('select');
        // list($this->pdoStatement, $executionTime) = $this->statement(
        //     $queryObject->getSql(),
        //     $queryObject->getBindings()
        // );
        // }

        // $result = call_user_func_array(array($this->statement, 'fetchAll'));

        return $result;
    }

    /**
     * Get first row
     *
     * @return \stdClass|null
     */
    public function first()
    {
        $this->limit(1);
        $result = $this->get();
        return empty($result) ? null : $result[0];
    }

    /**
     * @param        $value
     * @param string $fieldName
     *
     * @return null|\stdClass
     */
    public function findAll($fieldName, $value)
    {
        $this->where($fieldName, '=', $value);
        return $this->get();
    }

    /**
     * @param        $value
     * @param string $fieldName
     *
     * @return null|\stdClass
     */
    public function find($value, $fieldName = 'id')
    {
        $this->where($fieldName, '=', $value);
        return $this->first();
    }

    /**
     * Get count of rows
     *
     * @return int
     */
    public function count()
    {
        // Get the current statements
        $originalStatements = $this->statements;

        unset($this->statements['orderBys']);
        unset($this->statements['limit']);
        unset($this->statements['offset']);

        $count = $this->aggregate('count');
        $this->statements = $originalStatements;

        return $count;
    }

    /**
     * @param $type
     *
     * @return int
     */
    protected function aggregate($type)
    {
        // Get the current selects
        $mainSelects = isset($this->statements['selects']) ? $this->statements['selects'] : null;
        // Replace select with a scalar value like `count`
        $this->statements['selects'] = array($this->raw($type . '(*) as field'));
        $row = $this->get();

        // Set the select as it was
        if ($mainSelects) {
            $this->statements['selects'] = $mainSelects;
        } else {
            unset($this->statements['selects']);
        }

        if (is_array($row[0])) {
            return (int) $row[0]['field'];
        } elseif (is_object($row[0])) {
            return (int) $row[0]->field;
        }

        return 0;
    }

    /**
     * @param string $type
     * @param array $dataToBePassed
     *
     * @return mixed
     * @throws \Exception
     */
    public function getQuery($type = 'select', $dataToBePassed = array())
    {
        $allowedTypes = array('select', 'insert', 'insertignore', 'replace', 'delete', 'update', 'criteriaonly');
        if (!in_array(strtolower($type), $allowedTypes)) {
            throw new \Exception($type . ' is not a known type.', 2);
        }

        $results = $this->adapterInstance->$type($this->statements, $dataToBePassed);
        $bindings = $results['bindings'];
        $sql = $results['sql'];
        foreach ($results['bindings'] as $binding) {
            $sql = preg_replace("/\?/", $binding, $sql, 1);
        }
        return $sql;
    }

    /**
     * @param $data
     *
     * @return array|string
     */
    private function doInsert($data, $type)
    {
        // $eventResult = $this->fireEvents('before-insert');
        // if (!is_null($eventResult)) {
        //     return $eventResult;
        // }

        // // If first value is not an array
        // // Its not a batch insert
        // if (!is_array(current($data))) {
        //     $queryObject = $this->getQuery($type, $data);

        //     list($result, $executionTime) = $this->statement($queryObject->getSql(), $queryObject->getBindings());

        //     $return = $result->rowCount() === 1 ? $this->pdo->lastInsertId() : null;
        // } else {
        //     // Its a batch insert
        //     $return = array();
        //     $executionTime = 0;
        //     foreach ($data as $subData) {
        //         $queryObject = $this->getQuery($type, $subData);

        //         list($result, $time) = $this->statement($queryObject->getSql(), $queryObject->getBindings());
        //         $executionTime += $time;

        //         if ($result->rowCount() === 1) {
        //             $return[] = $this->pdo->lastInsertId();
        //         }
        //     }
        // }

        // $this->fireEvents('after-insert', $return, $executionTime);

        // return $return;
    }

    /**
     * @param $data
     *
     * @return array|string
     */
    public function insert($data)
    {
        return $this->doInsert($data, 'insert');
    }

    /**
     * @param $data
     *
     * @return array|string
     */
    public function insertIgnore($data)
    {
        return $this->doInsert($data, 'insertignore');
    }

    /**
     * @param $data
     *
     * @return array|string
     */
    public function replace($data)
    {
        return $this->doInsert($data, 'replace');
    }

    /**
     * @param $data
     *
     * @return $this
     */
    public function update($data)
    {
        // $eventResult = $this->fireEvents('before-update');
        // if (!is_null($eventResult)) {
        //     return $eventResult;
        // }

        // $queryObject = $this->getQuery('update', $data);

        // list($response, $executionTime) = $this->statement($queryObject->getSql(), $queryObject->getBindings());
        // $this->fireEvents('after-update', $queryObject, $executionTime);

        // return $response;
    }

    /**
     * @param $data
     *
     * @return array|string
     */
    public function updateOrInsert($data)
    {
        if ($this->first()) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * @param $data
     *
     * @return $this
     */
    public function onDuplicateKeyUpdate($data)
    {
        // $this->addStatement('onduplicate', $data);
        // return $this;
    }

    /**
     *
     */
    public function delete()
    {
        // $eventResult = $this->fireEvents('before-delete');
        // if (!is_null($eventResult)) {
        //     return $eventResult;
        // }

        // $queryObject = $this->getQuery('delete');

        // list($response, $executionTime) = $this->statement($queryObject->getSql(), $queryObject->getBindings());
        // $this->fireEvents('after-delete', $queryObject, $executionTime);

        // return $response;
    }

    /**
     * @param string|array $tables Single table or array of tables
     *
     * @return QueryBuilderHandler
     * @throws \Exception
     */
    public function table($tables)
    {
        $instance = new static($this->db);
        $tables = $this->addTablePrefix($tables, false);
        $instance->addStatement('tables', $tables);
        return $instance;
    }

    /**
     * @param $fields
     *
     * @return $this
     */
    public function select($fields)
    {
        if (!is_array($fields)) {
            $fields = func_get_args();
        }

        $fields = $this->addTablePrefix($fields);
        $this->addStatement('selects', $fields);
        return $this;
    }

    /**
     * @param $fields
     *
     * @return $this
     */
    public function selectDistinct($fields)
    {
        $this->select($fields);
        $this->addStatement('distinct', true);
        return $this;
    }

    /**
     * @param $field
     *
     * @return $this
     */
    public function groupBy($field)
    {
        $field = $this->addTablePrefix($field);
        $this->addStatement('groupBys', $field);
        return $this;
    }

    /**
     * @param        $fields
     * @param string $defaultDirection
     *
     * @return $this
     */
    public function orderBy($fields, $defaultDirection = 'ASC')
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }

        foreach ($fields as $key => $value) {
            $field = $key;
            $type = $value;
            if (is_int($key)) {
                $field = $value;
                $type = $defaultDirection;
            }
            if (!$field instanceof Shinka_QueryBuilder_Raw) {
                $field = $this->addTablePrefix($field);
            }
            $this->statements['orderBys'][] = compact('field', 'type');
        }

        return $this;
    }

    /**
     * @param $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->statements['limit'] = $limit;
        return $this;
    }

    /**
     * @param $offset
     *
     * @return $this
     */
    public function offset($offset)
    {
        $this->statements['offset'] = $offset;
        return $this;
    }

    /**
     * @param $key
     * @param $operator
     * @param $value
     *
     * @return $this
     */
    public function where($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }
        return $this->whereHandler($key, $operator, $value);
    }

    /**
     * @param $key
     * @param $operator
     * @param $value
     *
     * @return $this
     */
    public function orWhere($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->whereHandler($key, $operator, $value, 'OR');
    }

    /**
     * @param $key
     * @param $operator
     * @param $value
     *
     * @return $this
     */
    public function whereNot($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }
        return $this->whereHandler($key, $operator, $value, 'AND NOT');
    }

    /**
     * @param $key
     * @param $operator
     * @param $value
     *
     * @return $this
     */
    public function orWhereNot($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }
        return $this->whereHandler($key, $operator, $value, 'OR NOT');
    }

    /**
     * @param       $key
     * @param array $values
     *
     * @return $this
     */
    public function whereIn($key, $values)
    {
        return $this->whereHandler($key, 'IN', $values, 'AND');
    }

    /**
     * @param       $key
     * @param array $values
     *
     * @return $this
     */
    public function whereNotIn($key, $values)
    {
        return $this->whereHandler($key, 'NOT IN', $values, 'AND');
    }

    /**
     * @param       $key
     * @param array $values
     *
     * @return $this
     */
    public function orWhereIn($key, $values)
    {
        return $this->whereHandler($key, 'IN', $values, 'OR');
    }

    /**
     * @param       $key
     * @param array $values
     *
     * @return $this
     */
    public function orWhereNotIn($key, $values)
    {
        return $this->whereHandler($key, 'NOT IN', $values, 'OR');
    }

    /**
     * @param $key
     * @param $valueFrom
     * @param $valueTo
     *
     * @return $this
     */
    public function whereBetween($key, $valueFrom, $valueTo)
    {
        return $this->whereHandler($key, 'BETWEEN', array($valueFrom, $valueTo), 'AND');
    }

    /**
     * @param $key
     * @param $valueFrom
     * @param $valueTo
     *
     * @return $this
     */
    public function orWhereBetween($key, $valueFrom, $valueTo)
    {
        return $this->whereHandler($key, 'BETWEEN', array($valueFrom, $valueTo), 'OR');
    }

    /**
     * @param $key
     * @return QueryBuilderHandler
     */
    public function whereNull($key)
    {
        return $this->whereNullHandler($key);
    }

    /**
     * @param $key
     * @return QueryBuilderHandler
     */
    public function whereNotNull($key)
    {
        return $this->whereNullHandler($key, 'NOT');
    }

    /**
     * @param $key
     * @return QueryBuilderHandler
     */
    public function orWhereNull($key)
    {
        return $this->whereNullHandler($key, '', 'or');
    }

    /**
     * @param $key
     * @return QueryBuilderHandler
     */
    public function orWhereNotNull($key)
    {
        return $this->whereNullHandler($key, 'NOT', 'or');
    }

    protected function whereNullHandler($key, $prefix = '', $operator = '')
    {
        // $key = $this->adapterInstance->wrapSanitizer($this->addTablePrefix($key));
        // return $this->{$operator . 'Where'}($this->raw("{$key} IS {$prefix} NULL"));
    }

    /**
     * @param        $table
     * @param        $key
     * @param        $operator
     * @param        $value
     * @param string $type
     *
     * @return $this
     */
    public function join($table, $key, $operator = null, $value = null, $type = 'inner')
    {
        if (!$key instanceof \Closure) {
            $key = function ($joinBuilder) use ($key, $operator, $value) {
                $joinBuilder->on($key, $operator, $value);
            };
        }

        // Build a new JoinBuilder class, keep it by reference so any changes made
        // in the closure should reflect here
        $joinBuilder = new Shinka_QueryBuilder_JoinBuilder();
        $joinBuilder = &$joinBuilder;
        // Call the closure with our new joinBuilder object
        $key($joinBuilder);
        $table = $this->addTablePrefix($table, false);
        // Get the criteria only query from the joinBuilder object
        $this->statements['joins'][] = compact('type', 'table', 'joinBuilder');

        return $this;
    }

    /**
     * @param      $table
     * @param      $key
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function leftJoin($table, $key, $operator = null, $value = null)
    {
        return $this->join($table, $key, $operator, $value, 'left');
    }

    /**
     * @param      $table
     * @param      $key
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function rightJoin($table, $key, $operator = null, $value = null)
    {
        return $this->join($table, $key, $operator, $value, 'right');
    }

    /**
     * @param      $table
     * @param      $key
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function innerJoin($table, $key, $operator = null, $value = null)
    {
        return $this->join($table, $key, $operator, $value, 'inner');
    }

    /**
     * @param        $key
     * @param        $operator
     * @param        $value
     * @param string $joiner
     *
     * @return $this
     */
    protected function whereHandler($key, $operator = null, $value = null, $joiner = 'AND')
    {
        $this->statements['wheres'][] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }

    /**
     * Add table prefix (if given) on given string.
     *
     * @param      $values
     * @param bool $tableFieldMix If we have mixes of field and table names with a "."
     *
     * @return array|mixed
     */
    public function addTablePrefix($values, $tableFieldMix = true)
    {
        if (is_null($this->tablePrefix)) {
            return $values;
        }

        // $value will be an array and we will add prefix to all table names

        // If supplied value is not an array then make it one
        $single = false;
        if (!is_array($values)) {
            $values = array($values);
            // We had single value, so should return a single value
            $single = true;
        }

        $return = array();

        foreach ($values as $key => $value) {
            // It's a raw query, just add it to our return array and continue next
            if ($value instanceof Shinka_QueryBuilder_Raw || $value instanceof \Closure) {
                $return[$key] = $value;
                continue;
            }

            // If key is not integer, it is likely a alias mapping,
            // so we need to change prefix target
            $target = &$value;
            if (!is_int($key)) {
                $target = &$key;
            }

            if (!$tableFieldMix || ($tableFieldMix && strpos($target, '.') !== false)) {
                $target = $this->tablePrefix . $target;
            }

            $return[$key] = $value;
        }

        // If we had single value then we should return a single value (end value of the array)
        return $single ? end($return) : $return;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function addStatement($key, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        if (!array_key_exists($key, $this->statements)) {
            $this->statements[$key] = $value;
        } else {
            $this->statements[$key] = array_merge($this->statements[$key], $value);
        }
    }

    public function getQueryWithData()
    {
        return $this->getQuery();
    }
}