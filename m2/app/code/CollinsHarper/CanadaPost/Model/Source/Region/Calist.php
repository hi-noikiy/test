<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source\Region;


/**
 * Source model for Collins Harper shipping methods
 */
class Calist extends \CollinsHarper\CanadaPost\Model\Source\AbstractSource
{

    const COUNTRY_US = 'US';
    const COUNTRY_CA = 'CA';

    /**
     *
     * @var array
     */
    protected $_options;
    
    /**
     *
     * @var string
     */
    protected $_country;

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
    public function toOptionArrayCa($isMultiSelect = false)
    {
        return $this->toOptionArray($isMultiSelect, self::COUNTRY_CA);
    }

    /**
     * 
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArrayUs($isMultiSelect = false)
    {
        return $this->toOptionArray($isMultiSelect, self::COUNTRY_CA);
    }

    /**
     * 
     * @param bool $isMultiSelect
     * @param string $country
     * @return array
     */
    public function toOptionArrayCountry($isMultiSelect = true, $country = self::COUNTRY_CA)
    {
        $isMultiSelect = true;
        $country = self::COUNTRY_CA;
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
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        return $this->toOptionArrayCountry($isMultiSelect, self::COUNTRY_CA);
    }



}
