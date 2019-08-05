<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source\Date;

/**
 * Source model for Collins Harper shipping methods
 */
class Formats
{

    const FULL = 'full';
    const LONG = 'long';
    const MEDIUM = 'medium';
    const SHORT = 'short';

    const FULL_FORMAT = 'l, F j, Y';
    const LONG_FORMAT = 'F j, Y';
    const MEDIUM_FORMAT = 'M j, Y';
    const SHORT_FORMAT = 'n/j/y';

    /**
     *
     * @var bool
     */
    protected  $dataList = false;
    
    /**
     * 
     * @return array
     */
    public function getList()
    {
        if(!$this->dataList) {
              $this->dataList = array(
                self::FULL => date('l, F j, Y', strtotime("now")),
                self::LONG => date('F j, Y', strtotime("now")),
                self::MEDIUM => date('M j, Y', strtotime("now")),
                self::SHORT => date('n/j/y', strtotime("now")),
    );


        }
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

        foreach ($this->getList() as $k => $code) {
            $options[] = ['value' => $k, 'label' => ($code)];
        }

        return $options;
    }
}
