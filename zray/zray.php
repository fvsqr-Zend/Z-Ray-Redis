<?php
namespace Redis;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Redis.php';

$zre = new \ZRayExtension('redis');
$zrayRedis = new Redis();

$zre->setMetadata(array(
    'logo' => __DIR__ . DIRECTORY_SEPARATOR . 'redis-logo.png'
));

$zre->setEnabledAfter('Redis::connect');

$zre->traceFunction(
    'Redis::connect', 
    function($context, &$storage) use ($zrayRedis) {
        $connectionPort = isset($context['functionArgs'][1]) ? $context['functionArgs'][1] : null;
        $zrayRedis->init($context['functionArgs'][0],  $connectionPort);
    },  
    array($zrayRedis, 'statusInfo')
);

$zre->traceFunction('Redis::hGetAll', function () {}, array($zrayRedis, 'hashGetAll'));
$zre->traceFunction('Redis::hMset', function () {}, array($zrayRedis, 'hSet'));
$zre->traceFunction('Redis::hSet', function () {}, array($zrayRedis, 'hSet'));
