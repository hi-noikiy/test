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
 * Class Program
 * @package Magestore\Affiliateplusprogram\Model
 */
class Category extends AbstractModel
{
    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplusprogram\Model\ResourceModel\Category');
    }

    /**
     * @return $this
     */
    public function saveAll(){
        if ($this->getProgramId()){
            if ($this->getStoreId()){
                $this->saveAllInStore();
            }else {
                $stores = $this->_storeManager->getStores(true);
                foreach ($stores as $store){
                    $this->setStoreId($store->getId())->saveAllInStore();
                }
            }
        }
        return $this;
    }

    /**
     * @return Category
     */
    public function saveAllInStore(){
        if (is_array($this->getCategoryIds()))
            $newCategoryIds = array_combine($this->getCategoryIds(),$this->getCategoryIds());

        $collection = $this->getCollection()
            ->addFieldToFilter('program_id',$this->getProgramId())
            ->addFieldToFilter('store_id',$this->getStoreId());
        foreach ($collection as $item){
            $categoryId = $item->getCategoryId();
            if (in_array($categoryId,$newCategoryIds)){
                unset($newCategoryIds[$categoryId]);
            }else{
                $this->setId($item->getId())->delete();
            }
        }
        return $this->addCategory($newCategoryIds);
    }

    /**
     * @param $categoryIds
     * @return $this
     */
    public function addCategory($categoryIds){
        foreach ($categoryIds as $categoryId){
            if (is_numeric($categoryId)){
                $this->setCategoryId($categoryId)
                    ->setId(null)
                    ->save();
            }
        }
        return $this;
    }

}
