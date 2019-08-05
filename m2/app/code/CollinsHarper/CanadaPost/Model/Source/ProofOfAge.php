<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source;

/**
 * Source model for Collins Harper shipping methods
 */
class ProofOfAge extends \CollinsHarper\CanadaPost\Model\Source\AbstractSource
{

    /**
     *
     * @var array
     */
    protected  $dataList = array(
        \CollinsHarper\CanadaPost\Helper\Option::OPTIONS_PA_NONE => "None",
        \CollinsHarper\CanadaPost\Helper\Option::OPTIONS_PA18 => \CollinsHarper\CanadaPost\Helper\Option::OPTIONS_PA18_LABEL, // "18+",
        \CollinsHarper\CanadaPost\Helper\Option::OPTIONS_PA19 => \CollinsHarper\CanadaPost\Helper\Option::OPTIONS_PA19_LABEL, //"19+",
        \CollinsHarper\CanadaPost\Helper\Option::OPTIONS_PA_PROV => \CollinsHarper\CanadaPost\Helper\Option::OPTIONS_PA_PROV_LABEL, //"18+ or 19+ by province",
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
            $options[] = ['value' => $k, 'label' => $code];
        }

        return $options;
    }

}
