<?php
// +----------------------------------------------------------------------
// | ApePHP [ a lightweight php framework ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.xiaocp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Ape <me@xiaocp.com>
// +----------------------------------------------------------------------

namespace ape\db;

use ape\Db;
use PDO;

class MySql{

    // 数据库PDO对象实例
    private $connection;
    // 当前数据表名称（含前缀）
    protected $table = '';
    // 当前数据表名称（不含前缀）
    protected $name = '';
    // 当前数据表主键
    protected $pk;
    // 当前数据表前缀
    protected $prefix = '';
    // 查询参数
    protected $options = [
        'where' => [],  // and条件
        'order' => [],  // order排序
        'group' => [],  // 分组
        'limit' => [],  // limit
        'having' => [], // HAVING
        'left_join' => [],  // 左连接
        'right_join' => [],  // 右连接
        'inner_join' => [], // 内连接
        'join' => [],  // 连接
        'as' => '',  // 当前表别名 as
    ];
    // 过滤字段
    protected $field = '*';
    // join 连接查询表是否启用前缀
    protected $joinPrefix = true;
    // 当前执行的sql语句
    protected $sql = '';
    // 是否生成sql语句
    protected $buildSql = false;
    // 要操作的数据
    protected $data = [];
    // 数据表信息
    protected static $info = [];
    // 回调事件
    private static $event = [];

    /**
     * 初始化属性
     */
    private function init(){
        // 当前数据表名称（含前缀）
        $this->table = '';
        // 当前数据表名称（不含前缀）
        $this->name = '';
        // 当前数据表主键
        $this->pk = '';
        // 查询参数
        $this->options = [
            'where' => [],  // and条件
            'order' => [],  // order排序
            'group' => [],  // 分组
            'limit' => [],  // limit
            'having' => [], // HAVING
            'left_join' => [],  // 左连接
            'right_join' => [],  // 右连接
            'inner_join' => [], // 内连接
            'join' => [],  // 连接
            'as' => '',  // 当前表别名 as
        ];
        // 过滤字段
        $this->field = '*';
        // join 连接查询表是否启用前缀
        $this->joinPrefix = true;
        // 当前执行的sql语句
        $this->sql = '';
        // 是否生成sql语句
        $this->buildSql = false;
        // 要操作的数据
        $this->data = [];
        // 数据表信息
        self::$info = [];
    }

    /**
     * 构造函数
     * @access public
     * @param PDO $connection 数据库对象实例
     * @param array $config
     */
    public function __construct(PDO $connection = null, $config = [])
    {
        if($config){
            $host = $config['hostname'];
            $username = $config['username'];
            $password = $config['password'];
            $database = $config['database'];
            isset($config['charset']) ? $charset = $config['charset'] : $charset = 'utf8';
            $this->prefix     = $config['prefix'];
        }else{
            $host = DB::$config['hostname'];
            $username = DB::$config['username'];
            $password = DB::$config['password'];
            $database = DB::$config['database'];
            $charset = DB::$config['charset'];
            $this->prefix     = DB::$config['prefix'];
        }
        $this->connection = $connection ?: Db::connect($host, $username, $password, $database, $charset);
    }

    /**
     * 获取当前的数据库Connection对象
     * @access public
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * 切换当前的数据库连接
     * @access public
     * @param mixed $config
     * @return $this
     */
    public function connect($config)
    {
        $host = $config['host'];
        $username = $config['username'];
        $password = $config['password'];
        $database = $config['database'];
        $charset = $config['charset'];
        $this->connection = Db::connect($host, $username, $password, $database, $charset);
        return $this;
    }

    /**
     * beginTransaction 事务开始
     * @return void
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    /**
     * commit 事务提交
     * @return void
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * rollback 事务回滚
     * @return void
     */
    public function rollback()
    {
        $this->connection->rollback();
    }

    /**
     * transaction 通过事务处理多条SQL语句
     * 调用前需通过getTableEngine判断表引擎是否支持事务
     *
     * @param array $arraySql
     * @return Boolean
     */
    public function execTransaction($arraySql)
    {
        $ret = 1;
        $this->beginTransaction();
        foreach ($arraySql as $strSql) {
            if ($this->exec($strSql) == 0)
                $ret = 0;
        }
        if ($ret == 0) {
            $this->rollback();
            return false;
        } else {
            $this->commit();
            return true;
        }
    }

    /**
     * 执行查询 主要针对 SELECT, SHOW 等指令
     * @param string $sql sql指令
     * @param bool $all
     * @return array|string
     */
    public function query($sql = '', $all = true) {
        $this->sql = $sql;
        // 生成sql语句 不执行
        if($this->buildSql){
            $this->init();
            return $sql;
        }
        $pdo_stmt = $this->connection->prepare($this->sql); //prepare或者query 返回一个PDOStatement
        $pdo_stmt->execute();
        $all ? $result = $pdo_stmt->fetchAll(PDO::FETCH_ASSOC) : $result = $pdo_stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * 执行语句 针对 INSERT, UPDATE 以及DELETE,exec结果返回受影响的行数
     * @param string $sql sql指令
     * @return integer
     */
    public function exec($sql = '') {
        $this->sql = $sql;
        // 生成sql语句 不执行
        if($this->buildSql){
            $this->init();
            return $sql;
        }
        return $this->connection->exec($this->sql);
    }

    /**
     * 执行sql语句，自动判断进行查询或者执行操作
     * @param string $sql SQL指令
     * @return mixed
     */
    public function doSql($sql = '') {
        $queryIps = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT .* INTO|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $queryIps . ')\s+/i', $sql)) {
            return $this->exec($sql);
        } else {
            //查询操作
            return $this->query($sql);
        }
    }

    /**
     * 生成sql语句 不执行
     * @return $this
     */
    public function buildSql(){
        $this->buildSql = true;
        return $this;
    }

    /**
     * 获取最近一次查询的sql语句
     * @return String 执行的SQL
     */
    public function getLastSql() {
        return $this->sql;
    }

    /**
     * getFields 获取指定数据表中的全部字段名
     * @param String $table 表名
     * @return array
     */
    public function getFields($table = '')
    {
        if(!$table){
            !$this->name ?: $table = $this->prefix . $this->name;
            !$this->table ?: $table = $this->table;
        }
        if(isset(self::$info[$table]['fields']))
            return self::$info[$table]['fields'];
        $fields = [];
        $record = $this->connection->query("SHOW COLUMNS FROM $table");
        $this->_getPDOError();
        $record->setFetchMode(PDO::FETCH_ASSOC);
        $result = $record->fetchAll();
        foreach ($result as $rows) {
            $fields[] = $rows['Field'];
        }
        self::$info[$table]['info'] = $result;
        self::$info[$table]['fields'] = $fields;
        return $fields;
    }

    /**
     * 获取表的主键
     * @param string $table
     * @return mixed
     */
    public function getKey($table = ''){
        if($this->pk)
            return $this->pk;
        if(!$table){
            !$this->name ?: $table = $this->prefix . $this->name;
            !$this->table ?: $table = $this->table;
        }
        if(isset(self::$info[$table]['pk']))
            return self::$info[$table]['pk'];
        $record = $this->connection->query("SHOW COLUMNS FROM $table");
        $this->_getPDOError();
        $record->setFetchMode(PDO::FETCH_ASSOC);
        $result = $record->fetchAll();
        foreach ($result as $rows) {
            if($rows['Key'] == 'PRI'){
                $this->pk = $rows['Field'];
                self::$info[$table]['pk'] = $this->pk;
                return $this->pk;
            }
        }
        return '';
    }

    /**
     * 过滤并格式化数据表字段
     * @param array $data 要操作的数据
     * @return array $ret
     */
    private function _validData($data) {
        if (!is_array($data)) return [];
        $fields = $this->getFields();
        $ret = [];
        if (count($data) == count($data, 1)) {
            $t = [];
            foreach ($data as $key => $val) {
                if (!is_scalar($val)) continue; // 值不是标量则跳过
                if (in_array($key, $fields)) {
                    if (is_int($val)) {
                        $val = intval($val);
                    } elseif (is_float($val)) {
                        $val = floatval($val);
                    } elseif (is_string($val)) {
                        $val = '"'.addslashes($val).'"';
                    }
                    $t[$key] = $val;
                }
            }
            $ret[] = $t;
        } else {
            foreach ($data as $item){
                $t = [];
                foreach ($item as $key => $val) {
                    if (!is_scalar($val)) continue; // 值不是标量则跳过
                    if (in_array($key, $fields)) {
                        if (is_int($val)) {
                            $val = intval($val);
                        } elseif (is_float($val)) {
                            $val = floatval($val);
                        } elseif (is_string($val)) {
                            $val = '"'.addslashes($val).'"';
                        }
                        $t[$key] = $val;
                    }
                }
                $ret[] = $t;
            }
        }
        return $ret;
    }

    /**
     * checkFields 检查指定字段是否在指定数据表中存在
     * @param String $table
     * @param $arrayFields
     * @internal param array $arrayField
     */
    private function _checkFields($table, $arrayFields)
    {
        $fields = $this->getFields($table);
        foreach ($arrayFields as $key => $value) {
            if (!in_array($key, $fields)) {
                // todo 错误
                exit("Unknown column `$key` in field list.");
            }
        }
    }

    /**
     * 指定默认的数据表名（不含前缀）
     * @access public
     * @param string $name
     * @return $this
     */
    public function name($name){
        $this->name = $name;
        return $this;
    }

    /**
     * 指定默认的数据表名（含前缀）
     * @access public
     * @param string $table
     * @return $this
     */
    public function table($table){
        $this->table = $table;
        return $this;
    }

    /**
     * 获取完整表名称
     * @return string
     */
    public function getTable(){
        if($this->name)
            return $this->prefix . $this->name;
        if($this->table)
            return $this->table;
        // todo 异常
        exit('not table');
    }

    /**
     * 设置查询字段
     * @param mixed $field 字段数组
     * @return $this
     */
    public function field($field = '*'){
        $this->field = $field;
        return $this;
    }

    /**
     * 当前表别名 as
     * @param string $as
     * @return $this
     */
    public function alias($as = ''){
        $as && $this->options['as'] = $as;
        return $this;
    }

    /**
     * where And 语句
     * @param string $where
     * @return $this
     */
    public function where($where = ''){
        $where && array_push($this->options['where'], $where);
        return $this;
    }

    /**
     * order by 排序
     * @param string $order
     * @return $this
     */
    public function order($order = ''){
        $order && array_push($this->options['order'], $order);
        return $this;
    }

    /**
     * limit 获取数据
     * @param int $a
     * @param int $b
     * @return $this
     */
    public function limit($a = -1, $b = -1){
        $a != -1 && $b == -1 && $this->options['limit'] = $a;
        $a != -1 && $b != -1 && $this->options['limit'] = $a . ',' . $b;
        return $this;
    }

    /**
     * 分组
     * @param string $group
     * @return $this
     */
    public function group($group = ''){
        $group && array_push($this->options['group'], $group);
        return $this;
    }

    /**
     * having
     * @param string $having
     * @return $this
     */
    public function having($having = ''){
        $having && array_push($this->options['having'], $having);
        return $this;
    }

    /**
     * 连接查询是否启用配置前缀
     * @param bool $flag
     * @return $this
     */
    public function joinPrefix($flag = true){
        $this->joinPrefix = $flag;
        return $this;
    }

    /**
     * 左连接
     * @param string $leftJoin
     * @return $this
     */
    public function leftJoin($leftJoin = ''){
        $leftJoin && array_push($this->options['left_join'], $leftJoin);
        return $this;
    }

    /**
     * 右连接
     * @param string $rightJoin
     * @return $this
     */
    public function rightJoin($rightJoin = ''){
        $rightJoin && array_push($this->options['right_join'], $rightJoin);
        return $this;
    }

    /**
     * 内连接
     * @param string $innerJoin
     * @return $this
     */
    public function innerJoin($innerJoin = ''){
        $innerJoin && array_push($this->options['inner_join'], $innerJoin);
        return $this;
    }

    /**
     * 连接
     * @param string $join
     * @return $this
     */
    public function join($join = ''){
        $join && array_push($this->options['join'], $join);
        return $this;
    }

    /**
     * 设置数据
     * @param array $data
     * @return $this
     */
    public function data($data = []){
        $data = $this->_validData($data);
        $this->data = $data;
        return $this;
    }

    /**
     * 获取数据
     * @param string $id
     * @return mixed
     */
    public function get($id = ''){
        if(!$id){
            $sql = $this->_build('select');
            return $this->query($sql);
        }else{
            array_push($this->options['where'], "{$this->getKey()} = '{$id}'");
            $this->options['limit'] = 1;
            $sql = $this->_build('select');
            return $this->query($sql, false);
        }
    }

    /**
     * 插入数据操作
     * @param array $data
     * @return int
     */
    public function insert($data = []){
        if($data){
            $data = $this->_validData($data);
            $this->data = $data;
        }
        $sql = $this->_build('insert');
        return $this->exec($sql);
    }

    /**
     * 更新数据操作
     * @param array $data
     * @return int
     */
    public function update($data = []){
        if($data){
            $data = $this->_validData($data);
            $this->data = $data;
        }
        $sql = $this->_build('update');
        return $this->exec($sql);
    }

    /**
     *  删除操作
     * @param string $id
     * @return int|mixed
     */
    public function delete($id = ''){
        $id && array_push($this->options['where'], "{$this->getKey()} = '{$id}'");
        $sql = $this->_build('delete');
        return $this->exec($sql);
    }

    /**
     * 查询操作
     * @return mixed
     */
    public function select(){
        $sql = $this->_build('select');
        return $this->query($sql);
    }

    /**
     * 获取字段最大值
     * @param string $field
     * @return mixed
     */
    public function max($field = ''){
        $field && $this->field = $field;
        $sql = $this->_build('max', 'ape_max');
        return $this->query($sql, false)['ape_max'];
    }

    /**
     * 获取字段最小值
     * @param string $field
     * @return mixed
     */
    public function min($field = ''){
        $field && $this->field = $field;
        $sql = $this->_build('min', 'ape_min');
        return $this->query($sql, false)['ape_min'];
    }

    /**
     * 统计
     * @param string $field
     * @return mixed
     */
    public function count($field = ''){
        $field && $this->field = $field;
        $sql = $this->_build('count', 'ape_count');
        return $this->query($sql, false)['ape_count'];
    }

    /**
     * 统计字段
     * @param string $field
     * @return mixed
     */
    public function sum($field = ''){
        $field && $this->field = $field;
        $sql = $this->_build('sum', 'ape_sum');
        return $this->query($sql, false)['ape_sum'];
    }

    /**
     * 生成sql语句
     * @param string $type
     * @param string $filedAs 字段别名
     * @return mixed|string
     */
    private function _build($type = '', $filedAs = ''){
        $join = '';
        $leftJoin = '';
        $rightJoin = '';
        $innerJoin = '';
        $where = '';
        $order = '';
        $limit = '';
        $group = '';
        $having = '';
        $as = '';
        // 连接查询表前缀
        $joinPrefix = $this->joinPrefix ? $this->prefix : '';

        if($this->options['join'])
            foreach ($this->options['join'] as $i)
                $join .= ' join ' . $joinPrefix.$i .' ';
        if($this->options['left_join'])
            foreach ($this->options['left_join'] as $i)
                $leftJoin .= ' left join ' . $joinPrefix.$i .' ';
        if($this->options['right_join'])
            foreach ($this->options['right_join'] as $i)
                $rightJoin .= ' right join ' . $joinPrefix.$i .' ';
        if($this->options['inner_join'])
            foreach ($this->options['inner_join'] as $i)
                $innerJoin .= ' inner join ' . $joinPrefix.$i .' ';
        if($this->options['where']){
            $where = ' where 1 = 1 ';
            foreach ($this->options['where'] as $i)
                $where .= ' and ' . $i .' ';
        }
        if($this->options['order']){
            $order = ' order by ';
            foreach ($this->options['order'] as $i)
                $order .= ' ' . $i .' ';
        }
        if($this->options['group']) {
            $group = ' group by ';
            foreach ($this->options['group'] as $i)
                $group .= ' ' . $i .' ';
        }
        if($this->options['having']){
            $having = ' having ';
            foreach ($this->options['having'] as $i)
                $having .= ' ' . $i .' ';
        }
        if($this->options['limit']){
            $limit = ' limit ';
            $limit .= $this->options['limit'];
        }
        if($this->options['as'])
            $as = $this->options['as'];

        $common = "{$this->getTable()} {$as} 
                        {$leftJoin} 
                        {$rightJoin} 
                        {$join} 
                        {$where} 
                        {$group} 
                        {$having} 
                        {$order} 
                        {$limit}";

        $sql = '';
        switch ($type){
            case 'insert':
                if (count($this->data) == count($this->data, 1)) {
                    $keys = array_keys($this->data);
                } else {
                    $keys = array_keys($this->data[0]);
                }
                $keys_string = implode(',', $keys);
                $values = '';
                foreach ($this->data as $key => $value){
                    $values .= "(";
                    foreach ($keys as $k => $v){
                        $values .= "{$value[$v]},";
                    }
                    $values = substr($values, 0, -1);
                    $values .= "),";
                }
                $values = substr($values, 0, -1);
                $sql = "insert into {$this->getTable()} ($keys_string) values {$values}";
                exit_dump($sql);
                break;
            case 'update':
                $exp = '';
                foreach ($this->data[0] as $key => $value){
                    $exp .= $key . '=' . $value .',';
                }
                $exp = substr($exp, 0, -1);
                $sql = "update {$this->getTable()} {$as} 
                        {$leftJoin} 
                        {$rightJoin} 
                        {$join} 
                        set {$exp}
                        {$where} 
                        {$group} 
                        {$having} 
                        {$order} 
                        {$limit} ";
                break;
            case 'delete':
                // todo 异常处理
                if ($where == '')
                    exit('Where is null');
                $sql = "delete from {$common}";
                break;
            case 'select':
                $sql = "select {$this->field} from {$common}";
                break;
            case 'max':
                // todo 错误处理，只能有一个字段
                if(strpos($this->field, ','))
                    exit('jjj');
                $sql = "select max({$this->field}) {$filedAs} from {$common}";
                break;
            case 'min':
                // todo 错误处理，只能有一个字段
                if(strpos($this->field, ','))
                    exit('jjj');
                $sql = "select min({$this->field}) {$filedAs} from {$common}";
                break;
            case 'sum':
                // todo 错误处理，只能有一个字段
                if(strpos($this->field, ','))
                    exit('jjj');
                $sql = "select sum({$this->field}) {$filedAs} from {$common}";
                break;
            case 'count':
                // todo 错误处理，只能有一个字段
                if(strpos($this->field, ','))
                    exit('jjj');
                $sql = "select count({$this->field}) {$filedAs} from {$common}";
                break;
        }
        $sql = preg_replace("/(\s+)/",' ',$sql);
        return $sql;
    }

    /**
     * getPDOError 捕获PDO错误信息
     */
    private function _getPDOError(){
        if ($this->connection->errorCode() != '00000') {
            $error = $this->connection->errorInfo();
            // todo PDO异常
            exit($error[2]);
        }
    }

    private function __clone() {}

    /**
     * 关闭数据库连接
     */
    public function destruct(){
        $this->connection = null;
    }

}