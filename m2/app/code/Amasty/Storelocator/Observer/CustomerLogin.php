<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\PageCache\Model\Cache\Type as CacheManager;
use Amasty\Storelocator\Model\Review;
use Amasty\Storelocator\Model\Location;

class CustomerLogin implements ObserverInterface
{
    /**
     * @var CacheManager
     */
    private $cache;

    /**
     * @var Review
     */
    private $review;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [Review::CACHE_TAG, Location::CACHE_TAG]);
    }
}
