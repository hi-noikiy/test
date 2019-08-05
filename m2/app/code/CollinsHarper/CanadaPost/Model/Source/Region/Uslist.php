<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source\Region;

use \CollinsHarper\CanadaPost\Model\Source\Region\CaList;

/**
 * Source model for Collins Harper shipping methods
 */
class Uslist extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    const COUNTRY_US = 'US';

    /**
     *
     * @var \Magento\Directory\Model\RegionFactory 
     */
    protected $_regionFactory;

    /**
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Directory\Model\RegionFactory $regionFactory,
        array $data = []
    ) {
        $this->_regionFactory = $regionFactory;
    }


    /**
     * 
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = true)
    {
        return $this->toOptionArrayCountry($isMultiSelect, self::COUNTRY_US);
    }

    /**
     * 
     * @param bool $isMultiSelect
     * @param string $country
     * @return array
     */
    public function toOptionArrayCountry($isMultiSelect = false, $country = self::COUNTRY_US)
    {
        $isMultiSelect = true;
        $country = self::COUNTRY_US;
        if (1) {
            $x = $this->_regionFactory->create()->getCollection();
            $this->_options[$country] = $x
                ->addCountryFilter($country)
                ->load();
        }

        $options = array();
        foreach ($this->_options[$country] as $o) {
            $options[] = array('value'=> $o->getCode(), 'label'=> $o->getDefaultName());
        }

        if (!$isMultiSelect) {
            array_unshift($options, array('value'=>'', 'label'=> __('--Please Select--')));
        }

        return $options;
    }

    /**
     * 
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }



}
