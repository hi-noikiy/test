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
namespace Magestore\Affiliateplus\Model\ResourceModel\Account;

/**
 * Collection Collection
 */
class Collection extends \Magestore\Affiliateplus\Model\ResourceModel\AbtractCollection
{

    /**
     * @var
     */
    protected $_addedTable;
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\Account','Magestore\Affiliateplus\Model\ResourceModel\Account');
    }
    /**
     * @return int
     */
    public function getStoreViewId()
    {
        return $this->_storeViewId;
    }

    /**
     * @param $storeViewId
     * @return $this
     */
    public function setStoreViewId($storeViewId)
    {
        $this->_storeViewId = $storeViewId;
        return $this;
    }

    /**
     * @return $this
     */
    protected function _afterLoad(){
        parent::_afterLoad();
        if ($storeId = $this->getStoreViewId())
            foreach ($this->_items as $item){
                $item->setStoreViewId($storeId)->loadStoreValue();
            }
        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition=null) {
        if ($storeId = $this->getStoreViewId()) {
            $attributes = array_merge(
                $this->_accountModel->getStoreAttributes(),
                $this->_accountModel->getBalanceAttributes()
            );
            if (in_array($field, $attributes)) {
                if (!in_array($field, $this->_addedTable)) {
                    $this->getSelect()
                        ->joinLeft(
                            [
                                $field => $this->getTable(\Magestore\Affiliateplus\Setup\InstallSchema::SCHEMA_ACCOUNT_VALUE)
                            ],
                            "main_table.account_id = $field.account_id" .
                            " AND $field.store_id = $storeId" .
                            " AND $field.attribute_code = '$field'",
                            []
                        );
                    $this->_addedTable[] = $field;
                }
                return parent::addFieldToFilter("IF($field.value_id IS NULL, main_table.$field, $field.value)", $condition);
            }
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
