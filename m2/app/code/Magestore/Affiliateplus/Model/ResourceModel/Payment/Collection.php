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
namespace Magestore\Affiliateplus\Model\ResourceModel\Payment;

/**
 * Collection Collection
 */
class Collection extends \Magestore\Affiliateplus\Model\ResourceModel\AbtractCollection
{
    /**
     * @var bool
     */
    protected $_load_method_info = true;
    /**
     * @var
     */
    protected $_storeViewId;
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\Payment','Magestore\Affiliateplus\Model\ResourceModel\Payment');
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
     * @param $value
     * @return $this
     */
    public function setLoadMethodInfo($value){
        $this->_load_method_info = $value;
        return $this;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function addStoreToFilter($storeId){
        $this->getSelect()
            ->where('store_id = 0 OR store_id = ?',$storeId);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _afterLoad(){
        parent::_afterLoad();
        if ($this->_load_method_info)
            foreach ($this->_items as $item)
                $item->addPaymentInfo();
        return $this;
    }
}
