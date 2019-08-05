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
class Manifestlink extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'cpcanadapost_manifestlink';

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
        $this->_init('CollinsHarper\CanadaPost\Model\ResourceModel\Manifestlink');
    }

    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * 
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}
