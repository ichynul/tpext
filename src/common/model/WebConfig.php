<?php

namespace tpext\common\model;

use think\Model;

class WebConfig extends Model
{
    protected $autoWriteTimestamp = 'dateTime';

    public static function clearCache($configKey)
    {
        cache('web_config_' . $configKey, null);
    }

    public static function config($configKey, $reget = false)
    {
        $cache = cache('web_config_' . $configKey);

        if ($cache && !$reget) {
            return $cache;
        }

        $theConfig = static::where(['key' => $configKey])->find();
        if (!$theConfig) {
            return [];
        }

        $config = json_decode($theConfig['config'], 1);

        $rootPath = app()->getRootPath();
        $filePath = $rootPath . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $theConfig['file']);

        if (!is_file($filePath)) {
            return $config;
        }

        $default = include $filePath;

        if (empty($config)) {
            return $default;
        }

        $values = [];
        foreach ($default as $key => $val) {

            if ($key == '__config__' || $key == '__saving__') {
                continue;
            }
            $values[$key] = $config[$key] ?? $val;
        }
        if (!empty($values)) {
            cache('web_config_' . $configKey, $values);
        }

        return $values;
    }
}
