<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model;

class Storage
{
    /**
     * Free gift quote items with tax
     *
     * @var array
     */
    public static $cachedFreeGiftsWithTax = [];

    /**
     * Cached price with tax for free gift quote items
     *
     * @var array
     */
    public static $cachedQuoteItemPricesWithTax = [];
}
