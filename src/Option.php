<?php

namespace Ophim\Crawler\OphimCrawler;

use Backpack\Settings\app\Models\Setting;

class Option
{
    public static function get($name, $default = null)
    {
        $entry = static::getEntry();
        $fields = array_column(static::getAllOptions(), 'value', 'name');

        $options = array_merge($fields, json_decode($entry->value, true) ?? []);

        return isset($options[$name]) ? $options[$name] : $default;
    }

    public static function getEntry()
    {
        return Setting::firstOrCreate([
            'key' => 'hacoidev/ophim-crawler.options',
        ], [
            'name' => 'Options',
            'field' => json_encode(['name' => 'value', 'type', 'hidden']),
            'group' => 'crawler',
            'active' => false
        ]);
    }

    public static function getAllOptions()
    {
        return [
            'domain' => [
                'name' => 'domain',
                'label' => 'API Domain',
                'type' => 'text',
                'value' => 'https://ophim1.com'
            ],
            'download_image' => [
                'name' => 'download_image',
                'label' => 'Tải ảnh khi crawl',
                'type' => 'checkbox',
            ],
        ];
    }
}
