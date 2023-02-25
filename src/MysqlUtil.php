<?php

namespace Ajian\Helper;
/**
 * Mysql类
 */
class MysqlUtil
{

    /**
     * @var false|\mysqli|null 数据库连接对象
     */
    public $link = null;

    /**
     * 构造方法 - 初始化单例数据库连接
     */
    public function __construct($hostname,$username,$password,$database)
    {
        if (!$this->link){
            $this->link = mysqli_connect($hostname,$username,$password,$database);
            if ($this->link->connect_error){
                throw new \Exception('数据库连接失败');
            }
            $this->link->set_charset('utf-8');
        }
    }

    /**
     * 执行一条sql语句
     * @param str $sql 查询语句
     * @return obj      结果集对象
     */
    public function query($sql)
    {
        return $this->link->query($sql);
    }

    /**
     * 获取多行数据
     * @param str $sql 查询语句
     * @return arr      多行数据
     */
    public function getAll($sql)
    {
        $data = [];
        $res = $this->query($sql);
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 获取一行数据
     * @param str $row 查询语句
     * @return arr      单行数据
     */
    public function getRow($sql)
    {
        $res = $this->query($sql);
        return $res->fetch_assoc();
    }

    /**
     * 获取单个结果
     * @param str $sql 查询语句
     * @return str      单个结果
     */
    public function getOne($sql)
    {
        $res = $this->query($sql);
        $data = $res->fetch_row();
        return $data[0];
    }

    /**
     * 插入/更新数据
     * @param str $table 表名
     * @param arr $data 插入/更新的数据
     * @param str $act insert/update
     * @param str $where 更新条件
     * @return bool 插入/更新是否成功
     */
    public function exec($table, $data, $act = 'insert', $where = '0')
    {
        //插入操作
        if ($act == 'insert') {
            $sql = 'insert into ' . $table;
            $sql .= ' (' . implode(',', array_keys($data)) . ')';
            $sql .= " values ('" . implode("','", array_values($data)) . "')";
        } else if ($act == 'update') {
            $sql = 'update ' . $table . ' set ';
            foreach ($data as $k => $v) {
                $sql .= $k . '=' . "'$v',";
            }
            $sql = rtrim($sql, ',');
            $sql .= ' where 1 and ' . $where;
        }
        return $this->query($sql);
    }

    /**
     * 获取最近一次插入的主键值
     * @return int 主键
     */
    public function getLastId()
    {
        return $this->link->insert_id;
    }

    /**
     * 获取最近一次操作影响的行数
     * @return int 影响的行数
     */
    public function getAffectedRows()
    {
        return $this->link->affected_rows;
    }

    /**
     * 关闭数据库连接
     * @return bool 是否关闭
     */
    public function __destruct()
    {
        $this->link->close();
        $this->link = null;
    }

}

?>