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
class Office extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'cpcanadapost_office';
    const FIELD_SEP = 'cpcanadapost_office';
    const FIELD_OFFICE_ID = 'cp_office_id';

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
        $this->_init('CollinsHarper\CanadaPost\Model\ResourceModel\Office');
    }


    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    // TODO identify the datatype of @return
    /**
     * 
     * @param int $office_id
     * @return type
     */
    public function getByCpOfficeId($office_id)
    {

        return $this->getCollection()
            ->addFieldToFilter(self::FIELD_OFFICE_ID, $office_id)
            ->getFirstItem();

    }

    /**
     * 
     * @return string
     */
    public function getOfficeAddress()
    {

        return $this->getAddress() . self::FIELD_SEP . $this->getCity() . self::FIELD_SEP . $this->getProvince() . self::FIELD_SEP . $this->getPostalCode();

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
