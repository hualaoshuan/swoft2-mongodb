<?php

namespace Hualaoshuan\Mongodb;


class MongoDBException extends \Exception
{
    /**
     * @param string $msg
     * @throws MongoDBException
     */
    public static function managerError(string $msg)
    {
        throw new self($msg);
    }
}
