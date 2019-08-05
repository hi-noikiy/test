<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CollinsHarper\CanadaPost\Model;


use Magento\Framework\DataObject\IdentityInterface;



/**
 * Canada Post Sell Online (* deprecated)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Manifest extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'cpcanadapost_manifest';

    const STATUS_PENDING = 'pending';
    const STATUS_TRANSMITTED = 'transmitted';

    /**
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;
    
    /**
     *
     * @var string
     */
    protected $_eventPrefix = self::CACHE_TAG;



    protected function _construct()
    {
        $this->_init('CollinsHarper\CanadaPost\Model\ResourceModel\Manifest');
    }

    /**
     * Return identifiers for object
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * 
     * @return array
     */
    public function getCustomAttributes()
    {
        return [];
    }


}
