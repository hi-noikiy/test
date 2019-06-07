<?php

require_once( Mage::getBaseDir() . '/app/code/community/Redstage/SaveForLater/controllers/IndexController.php' );

class Gearup_SaveForLater_IndexController extends Redstage_SaveForLater_IndexController {

    public function moveAction() {
        Mage::getSingleton('core/session')->setUpdateAction('saveforlater');
        $saveforlater_item_id = Mage::app()->getRequest()->getParam('item');
        if ($saveforlater_item_id) {
            $saveforlater_item = Mage::getModel('saveforlater/item')->load($saveforlater_item_id);
            $buy_request = unserialize($saveforlater_item->getBuyRequest());
            $params = $this->getRequest()->getParams();
            $buy_request['form_key'] = Mage::getSingleton('core/session')->getFormKey();

            /*limitation by stock when saving from 'later' to cart*/
            $product = Mage::getModel('catalog/product')->load($buy_request['product']);
            $stockQty = $product->getStockItem()->getQty();
            $savedQty = $buy_request['qty'];

            if($savedQty > $stockQty) {
                $buy_request['qty'] = $stockQty;
                $buy_request['original_qty'] = $stockQty;

            }

            $this->getRequest()->setParams($buy_request);
            $this->addAction();

            $saveforlater_item->delete();

            $quote = Mage::getModel('checkout/cart')->getQuote();
//            $cartItemId = $quote->getItemByProduct($product)->getId();

            foreach($quote->getAllItems() as $item) {
                if($item->getProductId() == $product->getId()){
                    $cartItemId = $item->getId();
                }
            }

            if($savedQty > $stockQty && $product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $message = Mage::helper('cataloginventory')->__('Available qty to purchase is %s pcs',
                    1 * $stockQty);
                $cartMessage[$cartItemId] = array(
                    "text" => $message,
                    "type" => "error"
                );

                Mage::getSingleton('core/session')->setCartMessage($cartMessage);
            }

            $this->_redirect('checkout/onepage/ajax');
        }
    }

    public function updateQtyAction() {
        Mage::getSingleton('core/session')->setUpdateAction('saveforlater_qty_change');

        if (!$this->_validateFormKey()) {
            $this->_redirect('checkout/onepage/ajax');
            return;
        }
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                        $model = Mage::getModel('saveforlater/item')->load($index);
                        $buyRequest = unserialize($model->getData('buy_request'));
                        $buyRequest['original_qty'] = trim($data['qty']);
                        $buyRequest['qty'] = $buyRequest['original_qty'];
                        $model->setData('buy_request', serialize($buyRequest));
                        $model->setQty($cartData[$index]['qty'])->save();
                    }
                }
                $this->_redirect('checkout/onepage/ajax');
            }
        } catch (Exception $ex) {
            
        }
    }

    public function saveAction() {

        Mage::getSingleton('core/session')->setUpdateAction('saveforlater');
        $quote_item_id = Mage::app()->getRequest()->getParam('item');

        if ($quote_item_id) {

            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            $customer_id = '';
            if ($customer = Mage::getSingleton('customer/session')->getCustomer()) {
                $customer_id = $customer->getId();
            }

            foreach ($quote->getAllItems() as $quote_item) {
                if ($quote_item->getId() == $quote_item_id) {

                    $model = Mage::getModel('saveforlater/item')
                        ->setCustomerId($customer_id)
                        ->setQuoteId($quote->getId())
                        ->setProductId($quote_item->getProduct()->getId())
                        ->setName($quote_item->getName())
                        ->setQty($quote_item->getQty())
                        ->setPrice($quote_item->getPrice() + $quote_item->getBaseTaxAmount())
                        //->setBuyRequest( serialize( $quote_item->getBuyRequest()->getData() ) )
                        ->setBuyRequest(serialize(array_merge($quote_item->getBuyRequest()->getData(), array('product' => $quote_item->getProductId()))))
                        ->setDateSaved(date('Y-m-d h:i:s', Mage::getModel('core/date')->timestamp()));

                    try {

                        /* print_r( $model->getData() );
                          exit; */

                        $model->save();
                        $quote->removeItem($quote_item->getId());
                        $quote->save();

                        Mage::getSingleton('checkout/session')->addSuccess($model->getName() . ' was added to ' . $this->__('Saved for Later'));
                    } catch (Exception $e) {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    }

                    break;
                }
            }
        }

        $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/ajax'));
    }
}
