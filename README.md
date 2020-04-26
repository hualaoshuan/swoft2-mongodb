
## 安装
`composer require hualaoshuan/swoft2-mongodb`

## 使用方法
1. 在对应的`config/dev`和`config/pro`目录下新建`mongo.php`配置文件
```php
<?php

return [
    'appName' => 'mongodb-swoole-connection',
    'userName' => 'root',
    'password' => 'asdf',
    'host' => '192.168.60.169',
    'port' => 27017,
    'dbName'=> 'test',
    'authMechanism' => 'SCRAM-SHA-256'
];
```
2. 在 Controller 里调用
```php
<?php

namespace App\Http\Controller;

use Hualaoshuan\Mongodb\Mongo;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;

/**
 * Class testController
 * @Controller(prefix="/test")
 */
class TestController{
    
    /**
     * @RequestMapping(route="/test",method={RequestMethod::GET})
     */
    public function test(){
        $result = Mongo::fetchAll('user', ['id' => 1], ['id']);
        var_dump($result);
        return 'OK';
    }
}
```