<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source;

/**
 * Source model for Collins Harper shipping methods
 */
class Locale
{

    /**
     *
     * @var array
     */
    protected  $dataList = array(
        \CollinsHarper\CanadaPost\Helper\AbstractHelp::LANG_CP_RATE_FR => 'English',
        \CollinsHarper\CanadaPost\Helper\AbstractHelp::LANG_CP_RATE_EN => 'French',
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
