<?php

namespace Hualaoshuan\Mongodb;

use MongoDB\Driver\Exception\Exception;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * @Bean()
 * Class Mongo
 * @package Hualaoshuan\Mongodb
 */
class Mongo
{

    /**
     * 返回满足 $filter 的全部数据
     * @param string $namespace
     * @param array $filter
     * @param array $options
     * @return array
     * @throws MongoDBException
     */
    public static function fetchAll(string $namespace, array $filter = [], array $options = [])
    {
        try {
            /**
             * @var $collection MongoDBConnection
             */
            $collection = self::getConnection();
            return  $collection->executeQueryAll($namespace, $filter, $options);
        } catch (\Exception  $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } catch (Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        }
    }

    
    /**
     * 返回满足 $filter 的分页数据
     * @param string $namespace
     * @param int $limit
     * @param int $currentPage
     * @param array $filter
     * @param array $options
     * @return array
     * @throws MongoDBException
     */
    public static function fetchPagination(string $namespace, int $limit, int $currentPage, array $filter = [], array $options = [])
    {
        try {
            /**
             * @var $collection MongoDBConnection
             */
            $collection = self::getConnection();
            return $collection->execQueryPagination($namespace, $limit, $currentPage, $filter, $options);
        } catch (\Exception  $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        } catch (Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        }
    }

    
    /**
     * 批量插入
     * @param $namespace
     * @param array $data
     * @return bool|string
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Exception\ConnectionException
     * @throws MongoDBException
     */
    public static function insertAll($namespace, array $data)
    {
        if (count($data) == count($data, 1)) {
            throw new  MongoDBException('data is can only be a two-dimensional array');
        }
        try {
            /**
             * @var $collection MongoDBConnection
             */
            $collection = self::getConnection();
            return $collection->insertAll($namespace, $data);
        } catch (MongoDBException $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        }
    }

    
    /**
     * 数据插入数据库
     * @param $namespace
     * @param array $data
     * @return bool|mixed
     * @throws MongoDBException
     */
    public static function insert($namespace, array $data = [])
    {
        try {
            /**
             * @var $collection MongoDBConnection
             */
            $collection = self::getConnection();
            return $collection->insert($namespace, $data);
        } catch (\Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        }
    }

    
    /**
     * 更新数据满足 $filter 的行的信息成 $newObject
     * @param $namespace
     * @param array $filter
     * @param array $newObj
     * @return bool
     * @throws MongoDBException
     */
    public static function updateRow($namespace, array $filter = [], array $newObj = [])
    {
        try {
            /**
             * @var $collection MongoDBConnection
             */
            $collection = self::getConnection();
            return $collection->updateRow($namespace, $filter, $newObj);
        } catch (\Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        }
    }

    
    /**
     * 只更新数据满足 $filter 的行的列信息中在 $newObject 中出现过的字段
     * @param $namespace
     * @param array $filter
     * @param array $newObj
     * @return bool
     * @throws MongoDBException
     */
    public static function updateColumn($namespace, array $filter = [], array $newObj = [])
    {
        try {
            /**
             * @var $collection MongoDBConnection
             */
            $collection = self::getConnection();
            return $collection->updateColumn($namespace, $filter, $newObj);
        } catch (\Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        }
    }

    
    /**
     * 删除满足条件的数据，默认只删除匹配条件的第一条记录，如果要删除多条 $limit=true
     * @param string $namespace
     * @param array $filter
     * @param bool $limit
     * @return bool
     * @throws MongoDBException
     */
    public static function delete(string $namespace, array $filter = [], bool $limit = false)
    {
        try {
            /**
             * @var $collection MongoDBConnection
             */
            $collection = self::getConnection();
            return $collection->delete($namespace, $filter, $limit);
        } catch (\Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        }
    }

    
    /**
     * 返回 collection 中满足条件的数量
     *
     * @param string $namespace
     * @param array $filter
     * @return bool
     * @throws MongoDBException
     */
    public static function count(string $namespace, array $filter = [])
    {
        try {
            /**
             * @var $collection MongoDBConnection
             */
            $collection = self::getConnection();
            return $collection->count($namespace, $filter);
        } catch (\Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        }
    }


    /**
     * 聚合查询
     * @param string $namespace
     * @param array $filter
     * @return bool
     * @throws Exception
     * @throws MongoDBException
     */
    public static function command(string $namespace, array $filter = [])
    {
        try {
            /**
             * @var $collection MongoDBConnection
             */
            $collection = self::getConnection();
            return $collection->command($namespace, $filter);
        } catch (\Exception $e) {
            throw new MongoDBException($e->getFile() . $e->getLine() . $e->getMessage());
        }
    }

    
    /**
     * 获取一个当前的可用连接
     * @throws \Exception
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Exception\ConnectionException
     */
    private static function getConnection()
    {
        return (new MongoDBPool())->getConnection();
    }
}
