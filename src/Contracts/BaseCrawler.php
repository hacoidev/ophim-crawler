<?php

namespace Ophim\Crawler\OphimCrawler\Contracts;

abstract class BaseCrawler
{
    protected $link;
    protected $fields;
    protected $excludedCategories;
    protected $excludedRegions;
    protected $excludedType;

    public function __construct($link, $fields, $excludedCategories = [], $excludedRegions = [], $excludedType = [])
    {
        $this->link = $link;
        $this->fields = $fields;
        $this->excludedCategories = $excludedCategories;
        $this->excludedRegions = $excludedRegions;
        $this->excludedType = $excludedType;
    }

    abstract public function handle();
}
