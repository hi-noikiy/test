<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source;

/**
 * Source model for Collins Harper shipping methods
 */
class Reason
{

    /**
     *
     * @var array
     */
    protected  $dataList =  array(
        'GIF' => 'gift',
        'DOC' => 'document',
        'SAM' => 'commercial sample',
        'REP' => 'repair or warranty',
        'SOG' => 'sale of goods',
        'OTH' => 'other',
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
            $options[] = ['value' => $k, 'label' => __($code)];
        }

        return $options;
    }
}
