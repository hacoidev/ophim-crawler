<?php

namespace Ophim\Crawler\OphimCrawler;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Option
{
    public static function get($name, $default = null)
    {
        $entry = static::getEntry();
        $fields = array_column(static::getAllOptions(), 'value', 'name');

        $options = array_merge($fields, json_decode($entry->value, true) ?? []);

        return isset($options[$name]) ? $options[$name] : $default;
    }

    // public static function set($name, $value)
    // {
    //     $entry = static::getEntry();
    //     $fields = array_column(static::getAllOptions(), 'value', 'name');

    //     $options = array_merge($fields, json_decode($entry->value, true) ?? []);

    //     $options[$name] = $value;

    //     return Setting::updateOrCreate([
    //         'key' => 'hacoidev/ophim-crawler.options',
    //     ], [
    //         'name' => 'Options',
    //         'field' => json_encode(['name' => 'value', 'type', 'hidden']),
    //         'value' => json_encode($options),
    //         'group' => 'crawler',
    //         'active' => false
    //     ]);
    // }

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
        $categories = [];
        $regions = [];
        try {
            $categories = Cache::remember('ophim_categories', 86400, function () {
                $data = json_decode(file_get_contents(sprintf('%s/the-loai', config('ophim_crawler.domain', 'https://ophim1.com'))), true) ?? [];
                return collect($data)->pluck('name', 'name')->toArray();
            });

            $regions = Cache::remember('ophim_regions', 86400, function () {
                $data = json_decode(file_get_contents(sprintf('%s/quoc-gia', config('ophim_crawler.domain', 'https://ophim1.com'))), true) ?? [];
                return collect($data)->pluck('name', 'name')->toArray();
            });
        } catch (\Throwable $th) {

        }


        $fields = [
            'episodes' => 'Tập mới',
            'status' => 'Trạng thái phim',
            'episode_time' => 'Thời lượng tập phim',
            'episode_current' => 'Số tập phim hiện tại',
            'episode_total' => 'Tổng số tập phim',
            'name' => 'Tên phim',
            'origin_name' => 'Tên gốc phim',
            'content' => 'Mô tả nội dung phim',
            'thumb_url' => 'Ảnh Thumb',
            'poster_url' => 'Ảnh Poster',
            'trailer_url' => 'Trailer URL',
            'quality' => 'Chất lượng phim',
            'language' => 'Ngôn ngữ',
            'notify' => 'Nội dung thông báo',
            'showtimes' => 'Giờ chiếu phim',
            'publish_year' => 'Năm xuất bản',
            'is_copyright' => 'Đánh dấu có bản quyền',
            'type' => 'Định dạng phim',
            'is_shown_in_theater' => 'Đánh dấu phim chiếu rạp',
            'actors' => 'Diễn viên',
            'directors' => 'Đạo diễn',
            'categories' => 'Thể loại',
            'regions' => 'Khu vực',
            'tags' => 'Từ khóa',
            'studios' => 'Studio',
        ];

        return [
            'domain' => [
                'name' => 'domain',
                'label' => 'API Domain',
                'type' => 'text',
                'value' => 'https://ophim1.com',
                'tab' => 'Setting'
            ],
            'download_image' => [
                'name' => 'download_image',
                'label' => 'Tải ảnh khi crawl',
                'type' => 'checkbox',
                'tab' => 'Image Optimize'
            ],
            'should_resize_thumb' => [
                'name' => 'should_resize_thumb',
                'label' => 'Resize ảnh thumb khi tải về',
                'type' => 'checkbox',
                'tab' => 'Image Optimize'
            ],
            'resize_thumb_width' => [
                'name' => 'resize_thumb_width',
                'label' => 'Chiều rộng ảnh thumb (px)',
                'type' => 'number',
                'default' => 200,
                'attributes' => [
                    'placeholder' => 'Để trống nếu muốn giữ nguyên tỉ lệ',
                    'class'       => 'form-control',
                ],
                'wrapper' => [
                    'class'       => 'form-group col-md-6',
                ],
                'tab' => 'Image Optimize'
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
                ],
                'tab' => 'Image Optimize'
            ],
            'should_resize_poster' => [
                'name' => 'should_resize_poster',
                'label' => 'Resize ảnh poster khi tải về',
                'type' => 'checkbox',
                'tab' => 'Image Optimize'
            ],
            'resize_poster_width' => [
                'name' => 'resize_poster_width',
                'label' => 'Chiều rộng ảnh poster (px)',
                'type' => 'number',
                'default' => 300,
                'attributes' => [
                    'placeholder' => 'Để trống nếu muốn giữ nguyên tỉ lệ',
                    'class'       => 'form-control',
                ],
                'wrapper' => [
                    'class'       => 'form-group col-md-6',
                ],
                'tab' => 'Image Optimize'
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
                ],
                'tab' => 'Image Optimize'
            ],
            'crawler_schedule_enable' => [
                'name' => 'crawler_schedule_enable',
                'label' => '<b>Bật/Tắt tự động</b>',
                'default' => false,
                'type' => 'checkbox',
                'tab' => 'Schedule'
            ],
            'crawler_schedule_page_from' => [
                'name' => 'crawler_schedule_page_from',
                'label' => 'Trang đầu',
                'type' => 'number',
                'default' => 1,
                'attributes' => [
                    'placeholder' => '1',
                    'class'       => 'form-control',
                ],
                'wrapper' => [
                    'class'       => 'form-group col-md-4',
                ],
                'tab' => 'Schedule'
            ],
            'crawler_schedule_page_to' => [
                'name' => 'crawler_schedule_page_to',
                'label' => 'Trang cuối',
                'type' => 'number',
                'default' => 2,
                'attributes' => [
                    'placeholder' => '2',
                    'class'       => 'form-control',
                ],
                'wrapper' => [
                    'class'       => 'form-group col-md-4',
                ],
                'tab' => 'Schedule'
            ],
            'crawler_schedule_cron_config' => [
                'name'        => 'crawler_schedule_cron_config',
                'label'       => 'Cron config',
                'type'        => 'text',
                'default'     => '* * * * *',
                'hint'        => '<a target="_blank" href="https://crontab.guru/every-10-minutes">See more</a>',
                'attributes' => [
                    'placeholder' => '* * * * * *',
                    'class'       => 'form-control',
                ],
                'wrapper' => [
                    'class'       => 'form-group col-md-4',
                ],
                'tab'   => 'Schedule'
            ],
            'crawler_schedule_excludedType' => [
                'name' => 'crawler_schedule_excludedType',
                'label' => 'Bỏ qua định dạng',
                'type' => 'select_from_array',
                'options'         => ['series' => 'Phim Bộ', 'single' => 'Phim Lẻ', 'hoathinh' => 'Hoạt Hình', 'tvshows' => 'TV Shows'],
                'allows_null'     => false,
                'allows_multiple' => true,
                'tab' => 'Schedule'
            ],
            'crawler_schedule_excludedCategories' => [
                'name' => 'crawler_schedule_excludedCategories',
                'label' => 'Bỏ qua thể loại',
                'type' => 'select_from_array',
                'options'         => $categories,
                'allows_null'     => false,
                'allows_multiple' => true,
                'tab' => 'Schedule'
            ],
            'crawler_schedule_excludedRegions' => [
                'name' => 'crawler_schedule_excludedRegions',
                'label' => 'Bỏ qua quốc gia',
                'type' => 'select_from_array',
                'options'         => $regions,
                'allows_null'     => false,
                'allows_multiple' => true,
                'tab' => 'Schedule'
            ],
            'crawler_schedule_fields' => [
                'name' => 'crawler_schedule_fields',
                'label' => 'Field cập nhật',
                'type' => 'select_from_array',
                'default' => array_keys($fields),
                'options'         => $fields,
                'allows_null'     => false,
                'allows_multiple' => true,
                'tab' => 'Schedule'
            ],
        ];
    }
}
