<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Output_Review extends EM_Apiios_Model_Api2_Checkout_Onepage_Output_Abstract
{
    public function  __construct() {
        $this->getCheckout()->setStepData('review', array(
            'label'     => Mage::helper('checkout')->__('Order Review'),
            'is_show'   => $this->isShow()
        ));
        parent::__construct();
        $this->getQuote()->collectTotals()->save();
    }

    public function getItems()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
    }

    public function getTotals()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getTotals();
    }

    public function toArrayFields(){
        /* Login Information */
        $fields = array();
        $helper = Mage::helper('checkout');
        $helperItem = Mage::helper('apiios/item')->setStore($this->getStore());
        $items = array();
        foreach($this->getItems() as $item){
            if($item->getProductType() == 'bundle')
                $bundleItem[] = $item->getId();
            if(!$item->getParentItemId() || in_array($item->getParentItemId(),$bundleItem)){
                $data = array();
                $data['name'] = $item->getProduct()->getName();
                if(!$item->getParentItemId())
                    $data['options'] = Mage::helper('apiios/item')->getItemOptionsArray($item,'review');
                $data['price'] = $helperItem->getPriceItem($item);
                $data['subtotal'] = $helperItem->getPriceItem($item,'subtotal');
                $items[] = $data;
            }
        }

        $final = array(
            'title' =>  $helper->__('Order Review'),
            'prefix'=>  'review',
            'items' =>  $items,
			'totals'	=>	Mage::getModel('apiios/api2_cart')->setStore($this->getStore())->totals()
        );
		
        return $final;
    }
}
?>
