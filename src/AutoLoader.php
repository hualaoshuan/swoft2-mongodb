<?php declare(strict_types=1);

namespace Hualaoshuan\Mongodb;

use Swoft\Helper\ComposerJSON;
use Swoft\SwoftComponent;
use function dirname;
use Hualaoshuan\Mongodb\Config\MongoDBPoolConfig;

/**
 * Class AutoLoader
 */
class AutoLoader extends SwoftComponent
{
    /**
     * @return array
     */
    public function beans(): array
    {
        $config =  [
            'mongo' => [
                'class' => MongoDBPoolConfig::class,
                'appName' => config('mongo.appName'),
                'userName' => config('mongo.userName'),
                'password' => config('mongo.password'),
                'host' => config('mongo.host'),
                'port' => config('mongo.port'),
                'dbName' => config('mongo.dbName'),
                'authMechanism' => config('mongo.authMechanism')
            ]
        ];
        
        return $config;
    }

    /**
     * Get namespace and dir
     *
     * @return array
     * [
     *     namespace => dir path
     * ]
     */
    public function getPrefixDirs(): array
    {
        return [
            __NAMESPACE__ => __DIR__,
        ];
    }

    /**
     * Metadata information for the component.
     *
     * @return array
     * @see ComponentInterface::getMetadata()
     */
    public function metadata(): array
    {
        $jsonFile = dirname(__DIR__) . '/composer.json';

        return ComposerJSON::open($jsonFile)->getMetadata();
    }
}
