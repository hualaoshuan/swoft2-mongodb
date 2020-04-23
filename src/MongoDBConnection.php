<?php

namespace Hualaoshuan\Mongodb;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Connection\Pool\AbstractConnection;
use Hualaoshuan\Mongodb\Config\MongoDBPoolConfig;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\{
    BulkWrite, Command, Manager, Query, WriteConcern
};
use MongoDB\Driver\Exception\{
    AuthenticationException, ConnectionException, Exception, InvalidArgumentException, RuntimeException
};
use Hualaoshuan\Mongodb\MongoDBException;


/**
 * Class MongoDBConnection
 * @package App\MongoDB
 * @Bean()
 */
class MongoDBConnection extends AbstractConnection
{
    
    /**
     * @Inject()
     * @var MongoDBPool
     */
    protected $pool;

    /**
     * @Inject()
     * @var MongoDBPoolConfig
     */
    private $poolConfig;

    /**
     * @var Manager
     */
    private $connection;

    /**
     * Create connectioin
     * @return void
     * @throws MongoDBException
     */
    public function createConnection()
    {
        try {
            $username = $this->poolConfig->getUserName();
            $password = $this->poolConfig->getPassword();
            if (!empty($username) && !empty($password)) {
                $uri = sprintf(
                    'mongodb://%s:%s@%s:%d/%s',
                    $this->poolConfig->getUserName(),
                    $this->poolConfig->getPassword(),
                    $this->poolConfig->getHost(),
                    $this->poolConfig->getPort(),
                    $this->poolConfig->getDatabaseName()
                );
            } else {
                $uri = sprintf(
                    'mongodb://%s:%d/%s',
                    $this->poolConfig->getHost(),
                    $this->poolConfig->getPort(),
                    $this->poolConfig->getDatabaseName()
                );
            }
            $urlOptions = [];
            // 数据集
            $replica = $this->poolConfig->getReplica();
            if ($replica) {
                $urlOptions['replicaSet'] = $replica;
            }
            $this->connection = new \MongoDB\Driver\Manager($uri, $urlOptions);
        } catch (InvalidArgumentException $e) {
            throw MongoDBException::managerError('mongo 连接参数错误:' . $e->getMessage());
        } catch (RuntimeException $e) {
            throw MongoDBException::managerError('mongo uri格式错误:' . $e->getMessage());
        }
        
    }

    
    /**
     * Reconnect
     * @throws MongoDBException
     */
    public function reconnect(): bool
    {
        $this->createConnection();
    }

    /**
     * 判断当前的数据库连接是否已经超时
     *
     * @return bool
     * @throws \MongoDB\Driver\Exception\Exception
     * @throws MongoDBException
     */
    public function check(): bool
    {
        try {
            $command = new Command(['ping' => 1]);
            $this->connection->executeCommand($this->poolConfig->getDatabaseName(), $command);
            return true;
        } catch (\Throwable $e) {
            return $this->catchMongoException($e);
        }
    }

    /**
     * 查询返回结果的全部数据
     * @param string $namespace
     * @param array $filter
     * @param array $options
     * @return array
     * @throws \Hualaoshuan\Mongodb\MongoDBException
     */
    public function executeQueryAll(string $namespace, array $filter = [], array $options = [])
    {
        if (!empty($filter['_id']) && !($filter['_id'] instanceof ObjectId)) {
            $filter['_id'] = new ObjectId($filter['_id']);
        }
        
        // 查询数据
        $result = [];
        try {
            $query = new Query($filter, $options);
            $cursor = $this->connection->executeQuery($this->poolConfig->getDatabaseName() . '.' . $namespace, $query);
            foreach ($cursor as $document) {
                $document = (array)$document;
                $document['_id'] = (string)$document['_id'];
                $result[] = $document;
            }
        } catch (\Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } catch (Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } finally {
            $this->pool->release($this);
            return $result;
        }
    }

    /**
     * 返回分页数据，默认每页 10 条
     * @param string $namespace
     * @param int $limit
     * @param int $currentPage
     * @param array $filter
     * @param array $options
     * @return array
     * @throws \Hualaoshuan\Mongodb\MongoDBException
     */
    public function execQueryPagination(string $namespace, int $limit = 10, int $currentPage = 0, array $filter = [], array $options = [])
    {
        if (!empty($filter['_id']) && !($filter['_id'] instanceof ObjectId)) {
            $filter['_id'] = new ObjectId($filter['_id']);
        }
        // 查询数据
        $data = [];
        $result = [];

        // 每次最多返回 10 条记录
        if (!isset($options['limit']) || (int)$options['limit'] <= 0) {
            $options['limit'] = $limit;
        }

        if (!isset($options['skip']) || (int)$options['skip'] <= 0) {
            $options['skip'] = $currentPage * $limit;
        }

        try {
            $query = new Query($filter, $options);
            $cursor = $this->connection->executeQuery($this->poolConfig->getDatabaseName() . '.' . $namespace, $query);

            foreach ($cursor as $document) {
                $document = (array)$document;
                $document['_id'] = (string)$document['_id'];
                $data[] = $document;
            }

            $result['totalCount'] = $this->count($namespace, $filter);
            $result['currentPage'] = $currentPage;
            $result['perPage'] = $limit;
            $result['list'] = $data;

        } catch (\Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } catch (Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } finally {
            $this->pool->release($this);
            return $result;
        }
    }

    /**
     * 数据插入
     * http://php.net/manual/zh/mongodb-driver-bulkwrite.insert.php
     * $data1 = ['title' => 'one'];
     * $data2 = ['_id' => 'custom ID', 'title' => 'two'];
     * $data3 = ['_id' => new MongoDB\BSON\ObjectId, 'title' => 'three'];
     *
     * @param string $namespace
     * @param array $data
     * @return bool|string
     * @throws \Hualaoshuan\Mongodb\MongoDBException
     */
    public function insert(string $namespace, array $data = [])
    {
        try {
            $bulk = new BulkWrite();
            $insertId = (string)$bulk->insert($data);
            $written = new WriteConcern(WriteConcern::MAJORITY, 1000);
            $this->connection->executeBulkWrite($this->poolConfig->getDatabaseName() . '.' . $namespace, $bulk, $written);
        } catch (\Exception $e) {
            $insertId = false;
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } finally {
            $this->pool->release($this);
            return $insertId;
        }
    }

    /**
     * 批量数据插入
     * http://php.net/manual/zh/mongodb-driver-bulkwrite.insert.php
     * $data = [
     * ['title' => 'one'],
     * ['_id' => 'custom ID', 'title' => 'two'],
     * ['_id' => new MongoDB\BSON\ObjectId, 'title' => 'three']
     * ];
     * @param string $namespace
     * @param array $data
     * @return bool|string
     * @throws \Hualaoshuan\Mongodb\MongoDBException
     */
    public function insertAll(string $namespace, array $data = [])
    {
        try {
            $bulk = new BulkWrite();
            foreach ($data as $items) {
                $insertId[] = (string)$bulk->insert($items);
            }
            $written = new WriteConcern(WriteConcern::MAJORITY, 1000);
            $this->connection->executeBulkWrite($this->poolConfig->getDatabaseName() . '.' . $namespace, $bulk, $written);
        } catch (\Exception $e) {
            $insertId = false;
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } finally {
            $this->pool->release($this);
            return $insertId;
        }
    }

    /**
     * 数据更新,效果是满足filter的行,只更新$newObj中的$set出现的字段
     * http://php.net/manual/zh/mongodb-driver-bulkwrite.update.php
     * $bulk->update(
     *   ['x' => 2],
     *   ['$set' => ['y' => 3]],
     *   ['multi' => false, 'upsert' => false]
     * );
     *
     * @param string $namespace
     * @param array $filter
     * @param array $newObj
     * @return bool
     * @throws \Hualaoshuan\Mongodb\MongoDBException
     */
    public function updateRow(string $namespace, array $filter = [], array $newObj = []): bool
    {
        try {
            if (!empty($filter['_id']) && !($filter['_id'] instanceof ObjectId)) {
                $filter['_id'] = new ObjectId($filter['_id']);
            }

            $bulk = new BulkWrite;
            $bulk->update(
                $filter,
                ['$set' => $newObj],
                ['multi' => true, 'upsert' => false]
            );
            $written = new WriteConcern(WriteConcern::MAJORITY, 1000);
            $result = $this->connection->executeBulkWrite($this->poolConfig->getDatabaseName() . '.' . $namespace, $bulk, $written);
            $modifiedCount = $result->getModifiedCount();
            $update = $modifiedCount == 0 ? false : true;
        } catch (\Exception $e) {
            $update = false;
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } finally {
            $this->pool->release($this);
            return $update;
        }
    }

    /**
     * 数据更新, 效果是满足filter的行数据更新成$newObj
     * http://php.net/manual/zh/mongodb-driver-bulkwrite.update.php
     * $bulk->update(
     *   ['x' => 2],
     *   [['y' => 3]],
     *   ['multi' => false, 'upsert' => false]
     * );
     *
     * @param string $namespace
     * @param array $filter
     * @param array $newObj
     * @return bool
     * @throws \Hualaoshuan\Mongodb\MongoDBException
     */
    public function updateColumn(string $namespace, array $filter = [], array $newObj = []): bool
    {
        try {
            if (!empty($filter['_id']) && !($filter['_id'] instanceof ObjectId)) {
                $filter['_id'] = new ObjectId($filter['_id']);
            }

            $bulk = new BulkWrite;
            $bulk->update(
                $filter,
                ['$set' => $newObj],
                ['multi' => false, 'upsert' => false]
            );
            $written = new WriteConcern(WriteConcern::MAJORITY, 1000);
            $result = $this->connection->executeBulkWrite($this->poolConfig->getDatabaseName() . '.' . $namespace, $bulk, $written);
            $modifiedCount = $result->getModifiedCount();
            $update = $modifiedCount == 1 ? true : false;
        } catch (\Exception $e) {
            $update = false;
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } finally {
            $this->pool->release($this);
            return $update;
        }
    }

    /**
     * 删除数据
     * @param string $namespace
     * @param array $filter
     * @param bool $limit
     * @return bool
     * @throws \Hualaoshuan\Mongodb\MongoDBException
     */
    public function delete(string $namespace, array $filter = [], bool $limit = false): bool
    {
        try {
            if (!empty($filter['_id']) && !($filter['_id'] instanceof ObjectId)) {
                $filter['_id'] = new ObjectId($filter['_id']);
            }

            $bulk = new BulkWrite;
            $bulk->delete($filter, ['limit' => $limit]);
            $written = new WriteConcern(WriteConcern::MAJORITY, 1000);
            $this->connection->executeBulkWrite($this->poolConfig->getDatabaseName() . '.' . $namespace, $bulk, $written);
            $delete = true;
        } catch (\Exception $e) {
            $delete = false;
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } finally {
            $this->pool->release($this);
            return $delete;
        }
    }

    /**
     * 获取collection 中满足条件的条数
     * @param string $namespace
     * @param array $filter
     * @return bool
     * @throws \Hualaoshuan\Mongodb\MongoDBException
     */
    public function count(string $namespace, array $filter = [])
    {
        try {
            $command = new Command([
                'count' => $namespace,
                'query' => $filter
            ]);
            $cursor = $this->connection->executeCommand($this->poolConfig->getDatabaseName(), $command);
            $count = $cursor->toArray()[0]->n;
            return $count;
        } catch (\Exception $e) {
            $count = false;
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } catch (Exception $e) {
            $count = false;
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } finally {
            $this->pool->release($this);
            return $count;
        }
    }


    /**
     * 获取collection 中满足条件的条数
     * @param string $namespace
     * @param array $filter
     * @return bool
     * @throws Exception
     */
    public function command(string $namespace, array $filter = [])
    {
        try {
            $command = new Command([
                'aggregate' => $namespace,
                'pipeline' => $filter,
                'cursor' => new \stdClass()
            ]);
            $cursor = $this->connection->executeCommand($this->poolConfig->getDatabaseName(), $command);
            $count = $cursor->toArray()[0];
        } catch (\Exception $e) {
            $count = false;
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } finally {
            $this->pool->release($this);
            return $count;
        }
    }

    /**
     * @param \Throwable $e
     * @return bool
     * @throws MongoDBException
     */
    private function catchMongoException(\Throwable $e)
    {
        switch ($e) {
            case ($e instanceof InvalidArgumentException):
                {
                    throw MongoDBException::managerError('mongo argument exception: ' . $e->getMessage());
                }
            case ($e instanceof AuthenticationException):
                {
                    throw MongoDBException::managerError('mongo 数据库连接授权失败:' . $e->getMessage());
                }
            case ($e instanceof ConnectionException):
                {
                    /**
                     * https://cloud.tencent.com/document/product/240/4980
                     * 存在连接失败的，那么进行重连
                     */
                    for ($counts = 1; $counts <= 5; $counts++) {
                        try {
                            $this->createConnection();
                        } catch (\Exception $e) {
                            // App::error('mongo connection error' . $e->getFile() . $e->getLine() . $e->getMessage());
                            continue;
                        }
                        break;
                    }
                    return true;
                }
            case ($e instanceof RuntimeException):
                {
                    throw MongoDBException::managerError('mongo runtime exception: ' . $e->getMessage());
                }
            default:
                {
                    throw MongoDBException::managerError('mongo unexpected exception: ' . $e->getMessage());
                }
        }
    }

    public function create(): void
    {
        // TODO: Implement create() method.
    }

    public function close(): void
    {
        // TODO: Implement close() method.
    }


}
