<?php
/**
 * Magento
 *
 * DISCLAIMER
 *
 * Competera API to compare / update price
 *
 * @category   Gearup
 * @package    Gearup_Competera
 * @author     Gunjan <gunjan@krishtechnolabs.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Gearup_Competera_Adminhtml_Competera_PriceloadController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('competera/priceload');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function massUpdateAction(){
        try {
            $priceChangeLog = Mage::getModel('competera/pricechangelog');
            $currentTime = Varien_Date::now();
            $entityIds = $this->getRequest()->getParam('entity_id');
            $compHistyory = Mage::getModel('competera/competerahistory');
            $compHistyory->setTitle('Update Log '.date('Y-m-d H:i:s'));
            $compHistyory->setCreationTime($currentTime);
            $compHistyory->save(); 
            $historyId = $compHistyory->getId();
            $priceArr = Mage::helper('competera')->getCompeteraPrice();

            $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
            $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
            $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
            $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
            foreach($entityIds as $entity){
                // Load product and update price
                $product = Mage::getModel('catalog/product')->load($entity);
                $customProductPrice = Mage::getModel('competera/customprice')->load($entity);
                $priceNew = ($customProductPrice->getCustomPrice() > 0 )?$customProductPrice->getCustomPrice():$priceArr[$product->getSku()]/$rates[$currentCurrencyCode];
                //Update product special price if it is available
                /*
                if($product->getSpecialPrice() > 0) {
                    $priceType = "Special Price";
                    $oldPrice = $product->getSpecialPrice();
                    $product->setSpecialPrice($priceNew);
                } else {
                    $priceType = "Price";
                    $oldPrice = $product->getPrice();
                    $product->setPrice($priceNew);
                }*/

                
                $curPrice = ($product->getSpecialPrice() != '' ? $product->getSpecialPrice() : $product->getPrice());

                  

                //Do not repice lowe than minimal price
                if($product->getMinPriceCustom() > $priceNew) {
                    $priceNew = $product->getMinPriceCustom();
                }
                $flag = true;
                if ($curPrice > $priceNew)
                    $priceDiff = (1 - $priceNew / $curPrice) * 100;
                else {
                    $priceDiff = (1 - $curPrice / $priceNew) * 100;
                    $flag = false;
                }        
                //Update special price if new price is lower than current price
                if($product->getPrice() > $priceNew && ($flag == true && $priceDiff >= 3 ) ) {
                    $priceType = "Special Price";
                    $oldPrice = $product->getSpecialPrice();
                    $product->setSpecialPrice($priceNew);
                } else {
                    $priceType = "Price";
                    $oldPrice = $product->getPrice();
                    $product->setPrice($priceNew);
                }

                $product->save();

                // Save price change log
                $priceChangeLog->setHistoryId($historyId)
                                ->setCreationTime($currentTime)
                                ->setSku($product->getSku())
                                ->setPartNumber($product->getPartNr())
                                ->setName($product->getName())
                                ->setPriceType($priceType)
                                ->setOldPrice($oldPrice)
                                ->setNewPrice($priceNew);
                $priceChangeLog->save();
                $priceChangeLog->unsetData();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('competera')->__('Total of %d record(s) were updated.', count($entityIds)));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('competera/priceload');
    }
    
    public function custompriceAction(){
         $entityId = $this->getRequest()->getParam('id');
         $price = $this->getRequest()->getParam('price');                    
         $product = Mage::getModel('competera/customprice')->load($entityId);
         $product->setId($entityId);
         $product->setCustomPrice($price);
         $product->save();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('competera/adminhtml_competera_priceload_grid')->toHtml()
        );
    }
    
}