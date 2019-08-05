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
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Model;

/**
 * Model Account
 */
class AccountValue extends AbtractModel
{


    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\AccountValue');
    }

    public function loadAttributeValue($accountId, $storeId, $attributeCode){
        $attributeValue = $this->getCollection()
            ->addFieldToFilter('account_id',$accountId)
            ->addFieldToFilter('store_id',$storeId)
            ->addFieldToFilter('attribute_code',$attributeCode)
            ->getFirstItem();
        $this->setData('account_id',$accountId)
            ->setData('store_id',$storeId)
            ->setData('attribute_code',$attributeCode);
        if ($attributeValue)
            $this->addData($attributeValue->getData())
                ->setId($attributeValue->getId());
        return $this;
    }
}
