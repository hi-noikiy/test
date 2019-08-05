<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source\Manifest;

/**
 * Source model for Collins Harper shipping methods
 */
class Status extends \CollinsHarper\CanadaPost\Model\Source\AbstractSource
{

    const NOT = 'not';
    const YES = 'in_any';
    const CURRENT = 'current';
    
    /**
     *
     * @var array
     */
    protected  $dataList = array(
        self::NOT => "Not in a Manifest",
        self::YES => 'In a Manifest',
        self::CURRENT => 'In this Manifest',
    );

    /**
     * 
     * @return array
     */
    public function getList()
    {
       return $this->dataList;
    }

    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {

        $options = [];

        foreach ($this->dataList as $k => $code) {
            $options[$k] = __($code);
        }

        return $options;
    }

}
