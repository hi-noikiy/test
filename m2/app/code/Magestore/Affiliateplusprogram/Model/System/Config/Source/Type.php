<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Model\System\Config\Source;

/**
 * Class Type
 * @package Magestore\Affiliateplusprogram\Model\System\Config\Source
 */
class Type
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
        return [
            [
                'value' => '', 'label'=>__('As General Configuration')
            ],
            [
                'value' => 'sales', 'label'=>__('Pay per Sales')
            ],
            [
                'value' => 'profit', 'label'=>__('Pay per Profit')
            ],
        ];
    }
}