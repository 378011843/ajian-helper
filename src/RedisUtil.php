<?php

namespace Ajian\Helper;
class RedisUtil
{

    private $redis; //redis对象
    private $config = ['host' => '127.0.0.1', 'port' => '6379', 'auth' => 'jiaogepengyou2021', 'timeout' => 0, 'select' => 0];

    /**
     * 初始化Redis
     * $config = array(
     * @param string    host           服务器
     * @param int       port           端口号
     * @param int       timeout        过期时间
     * @param int       select         库
     * @param string    auth           密码
     * )
     * @param array $config
     */
    public function __construct($config)
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('没有开启redis扩展');
        }
        if ($config && is_array($config)) {
            array_merge($this->config, $config);
        }
        $this->redis = new \Redis;
        $this->redis->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
        $this->config['auth'] ? $this->redis->auth($this->config['auth']) : '';
        $this->redis->select($this->config['select']);

    }

    /**
     * 设置值
     * @param string $key KEY名称
     * @param string|array $value 获取得到的数据
     * @param int $timeOut 时间
     */
    public function set($key, $value, $timeOut = 0)
    {
        $value = json_encode($value, TRUE);
        $retRes = $this->redis->set($key, $value);
        if ($timeOut > 0) $this->redis->setTimeout($key, $timeOut);
        return $retRes;
    }

    /**
     * 通过KEY获取数据
     * @param string $key KEY名称
     */
    public function get($key)
    {
        $result = $this->redis->get($key);
        return json_decode($result, TRUE);
    }

    /**
     * 删除一条数据
     * @param string $key KEY名称
     */
    public function delete($key)
    {
        return $this->redis->del($key);
    }

    /**
     * 数据入队列
     * @param string $key KEY名称
     * @param string|array $value 获取得到的数据
     * @param bool $right 是否从右边开始入
     */
    public function push($key, $value, $right = true)
    {
        $value = json_encode($value);
        return $right ? $this->redis->rPush($key, $value) : $this->redis->lPush($key, $value);
    }

    /**
     * 数据出队列
     * @param string $key KEY名称
     * @param bool $left 是否从左边开始出数据
     */
    public function pop($key, $left = true)
    {
        $val = $left ? $this->redis->lPop($key) : $this->redis->rPop($key);
        return json_decode($val);
    }

    /**
     * 数据自增
     * @param string $key KEY名称
     */
    public function increment($key)
    {
        return $this->redis->incr($key);
    }

    /**
     * 数据自减
     * @param string $key KEY名称
     */
    public function decrement($key)
    {
        return $this->redis->decr($key);
    }

    /**
     * key是否存在，存在返回ture
     * @param string $key KEY名称
     */
    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * 返回redis对象
     * redis有非常多的操作方法，我们只封装了一部分
     * 拿着这个对象就可以直接调用redis自身方法
     */
    public function redis()
    {
        return $this->redis;
    }

    public function size($key)
    {
        return $this->redis->lSize($key);
    }

    public function add($set, $value)
    {
        return $this->redis->sAdd($set, $value);
    }

    public function members($set)
    {
        return $this->redis->sMembers($set);
    }


}

?>