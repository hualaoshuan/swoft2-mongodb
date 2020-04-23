<?php

namespace Hualaoshuan\Mongodb\Config;

use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * @Bean()
 * Class MongoDBPoolConfig
 * @package App\MongoDB\Config
 */
class MongoDBPoolConfig
{
    /**
     * @var string 连接名称
     */
    protected $appName;

    /**
     * @var int 最小连接数
     */
    protected $minActive = 5;

    /**
     * @var  int 最大连接数
     */
    protected $maxActive = 10;

    /**
     * @var int 超时等待时间(ms)
     */
    protected $timeout = 20;

    /**
     * @var mixed 数据库用户名
     */
    protected $userName;

    /**
     * @var mixed 数据库连接密码
     */
    protected $password = '';

    /**
     * @var mixed 数据库连接host
     */
    protected $host;

    /**
     * @var int 数据库连接端口，默认27017
     */
    protected $port = 27017;

    /**
     * @var string 数据库名称
     */
    protected $databaseName;

    /**
     * @var string mongo 身份连接验证机制
     */
    protected $authMechanism = 'SCRAM-SHA-256';

    /**
     * @var string 数据库驱动
     */
    protected $driver = 'MongoDB';

    /**
     * @var string string 数据集，默认为空
     */
    protected $replica = '';
    
    public function __construct()
    {
        $this->appName = config('mongo.appName');
        $this->userName = config('mongo.userName');
        $this->password = config('mongo.password');
        $this->host = config('mongo.host');
        $this->port = config('mongo.port');
        $this->databaseName = config('mongo.dbName');
        $this->authMechanism = config('mongo.authMechanism');
    }

    /**
     * @return int
     */
    public function getMaxActive(): int
    {
        return $this->maxActive;
    }

    /**
     * @param int $maxActive
     */
    public function setMaxActive(int $maxActive)
    {
        $this->maxActive = $maxActive;
    }

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getMinActive(): int
    {
        return $this->minActive;
    }

    /**
     * @param int $minActive
     */
    public function setMinActive(int $minActive)
    {
        $this->minActive = $minActive;
    }

    /**
     * @return string
     */
    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * @param string $appName
     */
    public function setAppName(string $appName)
    {
        $this->appName = $appName;
    }

    
    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName(string $userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    /**
     * @param string $databaseName
     */
    public function setDatabaseName(string $databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     */
    public function setDriver(string $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getName(): string
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getUri(): array
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isUseProvider(): bool
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getBalancer(): string
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getProvider(): string
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getMaxWait(): int
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return string
     */
    public function getAuthMechanism(): string
    {
        return $this->authMechanism;
    }

    /**
     * @param string $authMechanism
     */
    public function setAuthMechanism(string $authMechanism)
    {
        $this->authMechanism = $authMechanism;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getMaxIdleTime(): int
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getMaxWaitTime(): int
    {
        throw new \Exception(__CLASS__.__FUNCTION__.'not complete');
    }

    /**
     * @return string
     */
    public function getReplica(): string
    {
        return $this->replica;
    }

    /**
     * @param string $replica
     */
    public function setReplica(string $replica)
    {
        $this->replica = $replica;
    }
}
