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
            'should_resize_thumb' => [
                'name' => 'should_resize_thumb',
                'label' => 'Resize ảnh thumb khi tải về',
                'type' => 'checkbox',
            ],
            'resize_thumb_width' => [
                'name' => 'resize_thumb_width',
                'label' => 'Chiều rộng ảnh thumb (px)',
                'type' => 'number',
                'default' => 300,
                'attributes' => [
                    'placeholder' => 'Để trống nếu muốn giữ nguyên tỉ lệ',
                    'class'       => 'form-control',
                ],
                'wrapper' => [
                    'class'       => 'form-group col-md-6',
                ]
            ],
            'resize_thumb_height' => [
                'name' => 'resize_thumb_height',
                'label' => 'Chiều cao ảnh thumb (px)',
                'type' => 'number',
                'attributes' => [
                    'placeholder' => 'Để trống nếu muốn giữ nguyên tỉ lệ',
                    'class'       => 'form-control',
                ],
                'wrapper' => [
                    'class'       => 'form-group col-md-6',
                ]
            ],
            'should_resize_poster' => [
                'name' => 'should_resize_poster',
                'label' => 'Resize ảnh poster khi tải về',
                'type' => 'checkbox',
            ],
            'resize_poster_width' => [
                'name' => 'resize_poster_width',
                'label' => 'Chiều rộng ảnh poster (px)',
                'type' => 'number',
                'default' => 450,
                'attributes' => [
                    'placeholder' => 'Để trống nếu muốn giữ nguyên tỉ lệ',
                    'class'       => 'form-control',
                ],
                'wrapper' => [
                    'class'       => 'form-group col-md-6',
                ]
            ],
            'resize_poster_height' => [
                'name' => 'resize_poster_height',
                'label' => 'Chiều cao ảnh poster (px)',
                'type' => 'number',
                'attributes' => [
                    'placeholder' => 'Để trống nếu muốn giữ nguyên tỉ lệ',
                    'class'       => 'form-control',
                ],
                'wrapper' => [
                    'class'       => 'form-group col-md-6',
                ]
            ],

        ];
    }
}
