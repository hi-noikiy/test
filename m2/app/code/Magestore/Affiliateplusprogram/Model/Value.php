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
namespace Magestore\Affiliateplusprogram\Model;

/**
 * Class Value
 * @package Magestore\Affiliateplusprogram\Model
 */
class Value extends AbstractModel
{


    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplusprogram\Model\ResourceModel\Value');
    }

    /**
     * @param $programId
     * @param $storeId
     * @param $attributeCode
     * @return $this
     */
    public function loadAttributeValue($programId, $storeId, $attributeCode){
        $attributeValue = $this->getCollection()
            ->addFieldToFilter('program_id',$programId)
            ->addFieldToFilter('store_id',$storeId)
            ->addFieldToFilter('attribute_code',$attributeCode)
            ->getFirstItem();

        $this->setData('program_id',$programId)
            ->setData('store_id',$storeId)
            ->setData('attribute_code',$attributeCode);

        if ($attributeValue){
            $this->addData($attributeValue->getData())
                ->setId($attributeValue->getId());
        }
        return $this;
    }
}
