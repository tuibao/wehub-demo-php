# WeHub PHP Demo 
把项目获取下来后，可以直接看一下demo.php。

使用Docker可以一键启动Demo。
```
docker-compose up
```

Docker下载：https://www.docker.com/products/docker-desktop

当启动服务后，在客户端配置 http://127.0.0.1/demo.php 
保存后，在控制台应该能打印客户端上报当数据信息


在report_new_msg里，为了防止误触发，所以加了特定关键词才会触发回复，关键词是“hello”和“hi”
```
if (strtolower($message['msg']) !== 'hello' && strtolower($message['msg']) !== 'hi') {
    $reply = [];
}
```