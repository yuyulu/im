<?php

namespace tong\im;

use Swoole\Table as SwooleTable;
use Swoole\WebSocket\Server as WebSocketServer;
use sethink\swooleOrm\Db;
use sethink\swooleOrm\MysqlPool;

class ImServer
{
    protected $WsServer;
    protected $UserTable;
    protected $MysqlConfig;
    protected $MysqlPool;
   

    public function __construct($MysqlConfig,$host = '0.0.0.0', $port = '9501', Array $setting = [])
    {
        $this->WsServer = new WebSocketServer($host, $port);
        $default_setting = [
            'worker_num' => 1,
            'task_worker_num' => 4,
            'log_file' => 'im.log',
            'task_enable_coroutine'    => true,
        ];
        $this->MysqlConfig = $MysqlConfig;

        $this->WsServer->set(array_merge($default_setting, $setting));

        $this->WsServer->on('handshake', [$this, 'onHandshake']);
        $this->WsServer->on('open', [$this, 'onOpen']);
        $this->WsServer->on('close', [$this, 'onClose']);
        $this->WsServer->on('message', [$this, 'onMessage']);
        $this->WsServer->on('request', [$this, 'onRequest']);
        $this->WsServer->on('task', [$this, 'onTask']);
        $this->WsServer->on('finish', [$this, 'onFinish']);
        $this->WsServer->on('workerstart', [$this, 'onWorkerStart']);
        $this->WsServer->on('request', [$this, 'onRequest']);

        $this->UserTable = new SwooleTable(65536);
        $this->UserTable->column('fd', SwooleTable::TYPE_INT, 8);
        $this->UserTable->column('token', SwooleTable::TYPE_STRING, 32);
        $this->UserTable->create();

    }

    public function onWorkerStart($server, $worker_id)
    {
        $this->MysqlPool = new MysqlPool($this->MysqlConfig);
        
        //执行定时器
        $this->MysqlPool->clearTimer($server);
    }

    public function onHandshake($request, $response)
    {
         if (!isset($request->get)) {
            $response->end();
             return false;
         }
         $get = $request->get;

         $token = isset($get['token']) ? $get['token'] : false;
         if ($token === false || strpos($token, '.' === false)) {
            $response->end();
             return false;
         }

         list($uid, $token) = explode('.', $token);
         //同一客户只能同时有一个连接
         if ($this->UserTable->exist($uid)) {
             $user_token = $this->UserTable->get($uid, 'token');
             if ($user_token != $token) {
                $response->end();
                 return false;
             }
         } else {
            $response->end();
            return false;
         }

        // websocket握手连接算法验证
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->end();
            return false;
        }
        

        $key = base64_encode(sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        ));

        $headers = [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $key,
            'Sec-WebSocket-Version' => '13',
        ];

        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }

        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }

        $response->status(101);
        $response->end();
        
        $this->WsServer->defer(function() use ($request, $uid) {
            $fd = $this->UserTable->get($uid, 'fd');
            $this->WsServer->close($fd);
            $this->UserTable->set($uid, ['fd' => $request->fd]);

            $this->WsServer->task(json_encode([
                'event' => 'getOfflineMessage',
                'uid' => $uid,
                'fd' => $request->fd,
            ]));
        });
        return true;
    }

    public function start()
    {
        $this->WsServer->start();
    }

    public function onOpen(WebSocketServer $server, $request)
    {
        $server->push($request->fd, "hello, welcome\n");
    }

    public function onMessage(WebSocketServer $server, $frame)
    {
        $data = json_decode($frame->data, true);

        $task_data = [
            'event' => 'saveMessage',
            'data' => $data
        ];
        $task_data = json_encode($task_data);
        $server->task($task_data);

        if ($this->UserTable->exist($data['to'])) {
            $target_fd = $this->UserTable->get($data['to'], 'fd');
            if ($target_fd == 0) {
                return;
            }
            $msg = [
                'message' => $data['message'],
                'time' => $data['time'],
                'type' => 'text',
                'uid' => $data['from'],
                ];
            $server->push($target_fd, json_encode([
                'event' => 'message',
                'msg' => $msg,
                ]));
        }
        return;
    }

    public function onRequest($request, $response)
    {
        $path = $request->server['path_info'];
        if ($path == '/login') {
            $uid = isset($request->get['uid']) ? $request->get['uid'] : false;
            $token = isset($request->get['token']) ? $request->get['token'] : false;
            $signature = isset($request->get['signature']) ? $request->get['signature'] : false;

            if (!$uid || !$token || !$signature) {
                $response->end('no access');
                return;
            }

            $right_signature = md5($uid . $token . '[32m5kAvDb7NVjoW0x5q[39m1');

            if ($right_signature != $signature) {
                $response->end('no access');
                return;
            }
            
            if ($this->UserTable->exist($uid)) {
                $fd = $this->UserTable->get($uid, 'fd');
                $this->WsServer->close($fd);
            }
            $this->UserTable->set($uid, ['token' => $token]);

            $response->end('success');
            return;
        }

        $response->end('no access');
    }

    public function onTask($server, $task)
    {
        $task_data = json_decode($task->data, true);
        switch ($task_data['event']) {
            case 'saveMessage':
                $data = [
                    'from' => $task_data['data']['from'],
                    'to' => $task_data['data']['to'],
                    'time'     => $task_data['data']['time'],
                    'type'     => $task_data['data']['type'],
                    'message'     => base64_encode($task_data['data']['message'])
                ];
                Db::init($this->MysqlPool)
                ->name('chat_message')
                ->insert($data);
                break;

            case 'getOfflineMessage':
                $uid = (int)$task_data['uid'];
                $fd = $task_data['fd'];

                $mysql = Db::init($this->MysqlPool)->instance();
                $statement = $mysql->prepare('select * from `chat_message` where `to` = ? or `from` = ?');
                $result = $statement->execute([$uid, $uid]);
                Db::init($this->MysqlPool)->put($mysql);
                foreach ($result as & $v) {
                    if ($v['type'] == 'text') {
                        $v['message'] = base64_decode($v['message']);
                    }
                }
                unset($v);

                $server->push($fd, json_encode([
                    'event' => 'getOfflineMessage',
                    'msgs' => $result,
                    ]));
                break;
            
            default:
                # code...
                break;
        }
    }

    public function onClose($server, $fd, $reactor_id)
    {
        foreach ($this->UserTable as $k => $v) {
            if ($fd == $v['fd']) {
                $this->UserTable->set($k, ['fd' => 0]);
                break;
            }
        }
    }

    public function onFinish($task_id, $data)
    {
        
    }
}
