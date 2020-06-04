# im
## 启动
```
php public/ImServer.php
```
## 登录

```
http://127.0.0.1:9501/login?uid=1&token=123456&signature=3c7423d34253c2af542b000dae36e138
```
## Websocket测试

```
ws://127.0.0.1:9501?token=1.123456
```

## 发送数据格式

```
{"from":1,"to":2,"type":"text","time":"2020-06-04 13:31:39","message":"test"}
```