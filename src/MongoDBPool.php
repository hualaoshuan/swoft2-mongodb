<?php

namespace Hualaoshuan\Mongodb;

use MongoDB\Collection;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\BeanFactory;
use Swoft\Db\Connection\ConnectionManager;
use Swoft\Db\Exception\DbException;
use Swoft\Connection\Pool\Exception\ConnectionPoolException;
use Swoft\Connection\Pool\Contract\ConnectionInterface;
use Swoft\Connection\Pool\Contract\PoolInterface;
use Hualaoshuan\Mongodb\Config\MongoDBPoolConfig;

/**
 * Class MongoDBPool
 * @package App\MongoDB
 * @Bean()
 */
class MongoDBPool
{
    
    /**
     * @var \SplQueue;
     */
    protected $queue;

    /**
     * 当前的连接数量
     * @var int
     */
    protected $currentCount = 0;
    

    /**
     * 创建MongoDB数据库连接
     *
     * @return ConnectionInterface
     * @throws DbException
     */
    public function createConnection()
    {
        $connection = BeanFactory::getBean(MongoDBConnection::class);
        $connection->createConnection();

        if ( !$this->queue ) {
            $this->queue = new \SplQueue();
        }

        $this->queue->push($connection);

        return $connection;
    }


    /**
     * 获取当前连接池中的可用连接
     * @return ConnectionInterface
     * @throws ConnectionException
     * @throws DbException
     */
    public function getConnection()
    {
        return $this->getConnectionByQueue();
    }

    
    /**
     * 释放链接
     * @param ConnectionInterface $connection
     */
    public function release(ConnectionInterface $connection)
    {
        
        $connection->updateLastTime();
        $connection->setRelease(true);
    }

    
    /**
     * 返回当前队列中的一个可用连接
     *
     * @return ConnectionInterface
     * @throws ConnectionException
     * @throws DbException
     */
    protected function getConnectionByQueue()
    {
        if ( !$this->queue ) {
            $this->queue = new \SplQueue();
        }
        
        if (!$this->queue->isEmpty()) {
            return $this->getOriginalConnection();
        }

        // 如果当前并发超过了连接池设置的最大数量 提示报错
        $poolConfig = BeanFactory::getBean(MongoDBPoolConfig::class);
        if ($this->currentCount >= $poolConfig->getMaxActive()) {
            throw new ConnectionPoolException('Connection pool queue is full');
        }

        $connect = $this->createConnection();
        $this->currentCount++;

        return $connect;
    }

    
    /**
     * Get original connection
     *
     * @return ConnectionInterface
     */
    private function getOriginalConnection()
    {
        return $this->queue->shift();
    }

}
