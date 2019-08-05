<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source;

/**
 * Source model for Collins Harper shipping methods
 */
class Quotetype
{

    /**
     *
     * @var array
     */
    protected  $dataList = array(
        \CollinsHarper\CanadaPost\Helper\AbstractHelp::QUOTE_COUNTER => 'Counter - will return the regular price paid by retail consumers',
        \CollinsHarper\CanadaPost\Helper\AbstractHelp::QUOTE_COMMERCIAL => 'Commercial - will return the contracted price between Canada Post and the contract holder',
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
