<?php
/**
 * Copyright © 2016 Collinsharper. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CollinsHarper\Moneris\Model;

class Vault extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('CollinsHarper\Moneris\Model\ResourceModel\Vault');
    }
}
