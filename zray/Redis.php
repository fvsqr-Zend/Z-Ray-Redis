<?php
namespace Redis;

class Redis
{
    /**
     * @var \Redis
     */
    private $redis;

    public function init($host, $port=null)
    {
        $this->redis = new \Redis();
        if ($port) {
            $this->redis->connect($host, $port);
        } else {
            $this->redis->connect($host);
        }
    }

    function statusInfo($context, &$storage)
    {
        $config = $this->redis->config("GET", "*");
        $infoServer = $this->redis->info("server");
        $infoMemory = $this->redis->info("memory");

        $redisConfig = array_map(function ($key) use($config)
        {
            return array(
                'property' => $key,
                'value' => $config[$key]
            );
        }, array_keys($config));

        $redisServerInfo = array_map(function ($key) use($infoServer)
        {
            return array(
                'property' => $key,
                'value' => $infoServer[$key]
            );
        }, array_keys($infoServer));

        $redisMemoryInfo = array_map(function ($key) use($infoMemory)
        {
            return array(
                'property' => $key,
                'value' => $infoMemory[$key]
            );
        }, array_keys($infoMemory));

        $storage['config'] = $redisConfig;
        $storage['serverInfo'] = $redisServerInfo;
        $storage['memoryInfo'] = $redisMemoryInfo;
    }

    function hashGetAll($context, &$storage)
    {
        $cacheKey = $context['functionArgs'][0];
        $value = $this->redis->hGetAll($cacheKey);
        $ttl = $this->redis->ttl($cacheKey);

        $item = array(
            'Cache Key' => $cacheKey,
            'value' => $value,
            'TTL' => gmdate('H ', $ttl) . 'hours,' . gmdate(' i ', $ttl) . 'minutes, ' . gmdate(' s ', $ttl) . 'seconds',
            'Item fetch Duration' => $context['durationExclusive'] . ' ms',
            'Called from File' => $context['calledFromFile'],
            'Called from Line' => $context['calledFromLine']
        );

        $storage['cachedItems'][$cacheKey] = $item;
    }
    
    function hSet($context, &$storage) {
        $cacheKey = $context['functionArgs'][0];
        $value = $context['functionArgs'][1];
        
        if (!is_array($value) && isset($context['functionArgs'][2])) {
            $value = array($value => $context['functionArgs'][2]);
        }
        
        $item = array(
            'Cache Key' => $cacheKey,
            'value' => $value,
            'Called from File' => $context['calledFromFile'],
            'Called from Line' => $context['calledFromLine']
        );
        
        $storage['cacheWrite'][$cacheKey] = $item;
    }
}
