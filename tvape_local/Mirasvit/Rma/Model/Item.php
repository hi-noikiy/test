<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



/**
 * @method Mirasvit_Rma_Model_Resource_Item_Collection|Mirasvit_Rma_Model_Item[] getCollection()
 * @method Mirasvit_Rma_Model_Item load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Rma_Model_Item setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Rma_Model_Item setIsMassStatus(bool $flag)
 * @method Mirasvit_Rma_Model_Resource_Item getResource()
 * @method int getProductId()
 * @method Mirasvit_Rma_Model_Item setProductId(int $entityId)
 * @method int getReasonId()
 * @method Mirasvit_Rma_Model_Item setReasonId(int $reasonId)
 * @method int getResolutionId()
 * @method Mirasvit_Rma_Model_Item setResolutionId(int $resolutionId)
 * @method int getConditionId()
 * @method Mirasvit_Rma_Model_Item setConditionId(int $conditionId)
 * @method int getRmaId()
 * @method Mirasvit_Rma_Model_Item setRmaId(int $rmaId)
 * @method Mirasvit_Rma_Model_Item setExchangeProductId(int $id)
 * @method int getExchangeProductId()
 * @method Mirasvit_Rma_Model_Item setQtyRequested(int $qty)
 * @method int getQtyRequested()
 * @method Mirasvit_Rma_Model_Item setToStock(bool $flag)
 * @method bool getToStock()
 * @method int getOrderId()
 * @method $this setOrderId(int $param)
 */
class Mirasvit_Rma_Model_Item extends Mirasvit_Rma_Model_ItemAbstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rma/item');
    }

    /**
     * @param bool $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /**
     * @var Mage_Catalog_Model_Product $_product
     */
    protected $_product = null;

    /**
     * @return bool|Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->getProductId()) {
            return false;
        }
        if ($this->_product === null) {
            $this->_product = Mage::getModel('catalog/product')->load($this->getProductId());
        }

        return $this->_product;
    }

    /**
     * @var int $_stockQty
     */
    protected $_stockQty;

    /**
     * @return int
     */
    public function getQtyStock()
    {
        if (!$this->_stockQty) {
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $this->getOrderItem()->getSku());
            $this->_stockQty = (int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
        }

        return $this->_stockQty;
    }

    /**
     * @var Mage_Sales_Model_Order_Item
     */
    protected $_orderItem;

    /**
     * @return Mage_Sales_Model_Order_Item
     */
    public function getOrderItem()
    {
        if (!$this->_orderItem) {
            $this->_orderItem = Mage::getModel('sales/order_item')->load($this->getOrderItemId());
        }

        return $this->_orderItem;
    }

    /**
     * @return int
     */
    public function getQtyOrdered()
    {
        if ($this->getOrderItemId()) {
            return (int) $this->getOrderItem()->getQtyOrdered();
        } else { //offline orders
            return (int) $this->getQtyRequested();
        }
    }

    /**
     * Returns quantity, available for return.
     *
     * @return int
     */
    public function getQtyAvailable()
    {
        $others = Mage::helper('rma')->getRmaByOrder($this->getOrderItem()->getOrder());
        $qtyReturned = 0;
        foreach ($others as $rma) {
            if ($this->getRmaId() != $rma->getId()
                    && $rma->getStatus()->getCode() != Mirasvit_Rma_Model_Status::REJECTED) {
                $items = $rma->getItemCollection();
                foreach ($items as $item) {
                    if ($item->getProductId() == $this->getProductId()) {
                        $qtyReturned = $qtyReturned + $item->getQtyRequested();
                    }
                }
            } else {
                $qtyReturned = $qtyReturned + $this->getQtyRequested();
            }
        }

        return $this->getQtyOrdered() - $qtyReturned;
    }

    /**
     * @return string
     */
    public function getProductSku()
    {
        return $this->getOrderItem()->getSku();
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     *
     * @return $this
     */
    public function initFromOrderItem($orderItem)
    {
        $this->_orderItem = $orderItem;
        $this->setOrderItemId($orderItem->getId());
        $this->setProductId($orderItem->getProductId());
        $this->setOrderId($orderItem->getOrderId());
        $this->setName($orderItem->getName());
        $this->setProductOptions($orderItem->getProductOptions());
        $this->setProductType($orderItem->getProductType());
        $qtyShipped = $orderItem->getQtyShipped();
        if (!$product = $orderItem->getProduct()) { //magento 1.6 does not have this method
            if ($productId = $orderItem->getProductId()) {
                $product = Mage::getModel('catalog/product')->load($productId);
            }
        }
        $status = null;
        if ($product) {
            $status = $product->getRmaStatus();
        }

        $this->setIsRmaAllowed((string) $status !== '0');

        // we have option to allow rma when status is processing (for example). so products are not shipped yet.
        if ($qtyShipped == 0) {
            $qtyShipped = $orderItem->getQtyOrdered();
        }
        $qty = $qtyShipped - $this->getQtyInRma($orderItem);
        if ($qty < 0) {
            $qty = 0;
        }
        $this->setQtyAvailable($qty);

        //we need this to avoid error of mysql foreign key
        if (!$this->getReasonId()) {
            $this->setReasonId(null);
        }
        if (!$this->getResolutionId()) {
            $this->setResolutionId(null);
        }
        if (!$this->getConditionId()) {
            $this->setConditionId(null);
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return int
     */
    protected function getQtyInRma($orderItem)
    {
        $collection = Mage::getModel('rma/item')->getCollection();
        $collection->addFieldToFilter('order_item_id', $orderItem->getId());
        // echo $collection->getSelect();die;
        $sum = 0;
        foreach ($collection as $item) {
            $sum += $item->getQtyRequested();
        }

        return $sum;
    }

    /**
     * @return array
     */
    public function getProductOptions()
    {
        $options = $this->getData('product_options');
        if (is_string($options)) {
            $options = @unserialize($options);
            $this->setData('product_options', $options);
        }

        return $options;
    }

    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_exchangeProduct;

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getExchangeProduct()
    {
        if (!$this->_exchangeProduct) {
            $this->_exchangeProduct = Mage::getModel('catalog/product')->load($this->getExchangeProductId());
        }

        return $this->_exchangeProduct;
    }

    /**
     * @return bool
     */
    public function isRefund()
    {
        if ($resolution = Mage::helper('rma')->getResolutionByCode('refund')) {
            return $this->getResolutionId() == $resolution->getId();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isExchange()
    {
        if ($resolution = Mage::helper('rma')->getResolutionByCode('exchange')) {
            return $this->getResolutionId() == $resolution->getId();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isCredit()
    {
        if ($resolution = Mage::helper('rma')->getResolutionByCode('credit')) {
            return $this->getResolutionId() == $resolution->getId();
        }

        return false;
    }

    /**
     * @return float
     */
    public function getOrderItemPrice()
    {
        $orderItem = $this->getOrderItem();
        if ($orderItem->getId()) {
            $store = ($orderItem->getOrder()) ? $orderItem->getOrder()->getStore() : $this->getRma()->getStore();
            if (Mage::getStoreConfig('tax/calculation/price_includes_tax', $store->getId())) {
                $price = Mage::helper('tax')->getPrice($orderItem->getProduct(),
                    $orderItem->getProduct()->getFinalPrice(), true);
            } else {
                $price = $orderItem->getPrice();
            }
            return $price;
        } else {
            return 0;
        }
    }

}
