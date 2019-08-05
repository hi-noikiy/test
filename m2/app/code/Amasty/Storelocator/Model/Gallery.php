<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model;

use Magento\Framework\Model\AbstractModel;

class Gallery extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel\Gallery::class);
    }
}
