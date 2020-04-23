<?php

namespace Hualaoshuan\Mongodb;

class MongoDBQueryBuilder
{
    /**
     * 组装 mongo json 字段查询，格式为: ['cate' => ['type' => 6,'childType' => 'c']
     * 返回的格式为:  ['cate.type' => 6, 'cate.childType' => 'c']
     * 目前只有两级组装，后续有需要在优化
     * @param array $condition
     * @return array
     * @throws \Exception
     */
    public static function buildJsonSearch(array $condition)
    {
        $result = [];
        foreach ($condition as $k => $v)
        {
            if (!is_array($v)) {
                throw new \Exception('json 查询字段格式错误');
            }
            foreach ($v as $item => $value) {
                $result[] = [$k.'.'.$item => $value];
            }
        }

        return $result;
    }


    /**
     * 组装 mongo text search 条件
     * @param string $keywords 关键字
     * @return array
     */
    public static function buildTextSearch(string $keywords)
    {
        return ['$text' => ['$search' => $keywords]];
    }


    /**
     * 组装正则模糊查询
     * @param string $filed
     * @param string $keywords
     * @return array
     */
    public static function buildRegexpSearch(string $filed, string $keywords)
    {
        return [$filed => ['$regex' => $keywords]];
    }
}
