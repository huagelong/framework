<?php
/**
 * Class RedisConnection
 * @package Trendi\Foundation\Storage\Adapter\Redis
 */

namespace Trendi\Foundation\Storage\Adapter\Redis;

class RedisConnection
{
    private $client = null;
    public $fields = [];

    function __construct(RedisClient $redis)
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_SYNC);
        $client->connect($redis->host, $redis->port);
        $this->client = $client;
        $redis->pool[$client->sock] = $this;
        $this->redis = $redis;
    }

    /**
     * 清理数据
     */
    function clean()
    {
        $this->fields = array();
    }

    /**
     * 执行redis指令
     * @param $cmd
     */
    function command($cmd)
    {
        /**
         * 如果已经连接，直接发送数据
         */
        if ($this->client->isConnected())
        {
            $this->client->send($cmd);
        }
        /**
         * 未连接，等待连接成功后发送数据
         */
        else
        {
            $this->wait_send = $cmd;
        }
        //从空闲连接池中移除，避免被其他任务使用
        $this->redis->lockConnection($this->client->sock);
        $data = $this->client->recv(65535, true);
        $result = $this->receive($this->client, $data);
        var_dump($result);
    }

    function receive($cli, $data)
    {
        $success = true;
        if ($this->redis->debug)
        {
            $this->redis->trace($data);
        }

        $lines = explode("\r\n", $data, 2);
        $type = $lines[0][0];
        if ($type == '-')
        {
            $success = false;
            $result = substr($lines[0], 1);
        }
        elseif ($type == '+')
        {
            $result = substr($lines[0], 1);;
        }
        //只有一行数据
        elseif ($type == '$')
        {
            $len = intval(substr($lines[0], 1));
            if ($len > strlen($lines[1]))
            {
                return;
            }
            $result = $lines[1];
        }
        //多行数据
        elseif ($type == '*')
        {
            parse_multi_line:
            $data_line_num = intval(substr($lines[0], 1));
            $data_lines = explode("\r\n", $lines[1]);
            $require_line_n = $data_line_num * 2 - substr_count($data, "$-1\r\n");
            $lines_n = count($data_lines) - 1;

            if ($lines_n == $require_line_n)
            {
                $result = array();
                $key_n = 0;
                for ($i = 0; $i < $lines_n; $i++)
                {
                    //not exists
                    if (substr($data_lines[$i], 1, 2) === '-1')
                    {
                        $value = false;
                    }
                    else
                    {
                        $value = $data_lines[$i + 1];
                        $i++;
                    }
                    if ($this->fields)
                    {
                        $result[$this->fields[$key_n]] = $value;
                    }
                    else
                    {
                        $result[] = $value;
                    }
                    $key_n  ++;
                }
                goto ready;
            }
            //数据不足，需要缓存
            else
            {
                return;
            }
        }
        elseif ($type == ':')
        {
            $result = intval(substr($lines[0], 1));
            goto ready;
        }
        else
        {
            echo "Response is not a redis result. String:\n$data\n";
            return;
        }

        ready:
        $this->clean();
        $this->redis->freeConnection($cli->sock, $this);
        return [$result, $success];
    }

    function onClose(\swoole_client $cli)
    {
        if ($this->wait_send)
        {
            $this->redis->freeConnection($cli->sock, $this);
            call_user_func($this->callback, "timeout", false);
        }
    }
}