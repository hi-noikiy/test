<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source;

/**
 * Source model for Collins Harper shipping methods
 */
class Coverage
{

    const ALWAYS = 'always';
    const OPTIONAL = 'optional';
    const NEVER = 'never';

    /**
     *
     * @var array
     */
    protected  $dataList = array(
        self::ALWAYS => 'Always',
      //  self::OPTIONAL => 'Optional',
        self::NEVER => 'Never',
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
