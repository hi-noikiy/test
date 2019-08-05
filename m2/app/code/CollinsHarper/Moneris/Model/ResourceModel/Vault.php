<?php
/**
 * Copyright © 2016 Collinsharper. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CollinsHarper\Moneris\Model\ResourceModel;

class Vault extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('collinsharper_moneris_payment_vault', 'vault_id');
    }
}
