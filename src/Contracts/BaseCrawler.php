<?php

namespace Ophim\Crawler\OphimCrawler\Contracts;

abstract class BaseCrawler
{
    protected $link;
    protected $fields;
    protected $excludedCategories;
    protected $excludedRegions;
    protected $excludedType;
    protected $forceUpdate;

    public function __construct($link, $fields, $excludedCategories = [], $excludedRegions = [], $excludedType = [], $forceUpdate)
    {
        $this->link = $link;
        $this->fields = $fields;
        $this->excludedCategories = $excludedCategories;
        $this->excludedRegions = $excludedRegions;
        $this->excludedType = $excludedType;
        $this->forceUpdate = $forceUpdate;
    }

    abstract public function handle();
}
