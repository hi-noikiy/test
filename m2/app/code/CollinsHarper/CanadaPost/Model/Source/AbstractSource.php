<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source;

/**
 * Source model for Collins Harper shipping methods
 */
class AbstractSource extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{


    /**
     * 
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

}
