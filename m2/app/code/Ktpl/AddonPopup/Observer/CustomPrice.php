<?php
    
    namespace Ktpl\AddonPopup\Observer;
 
    use Magento\Framework\Event\ObserverInterface;
    use Magento\Framework\App\RequestInterface;
 
    class CustomPrice implements ObserverInterface
    {
        public function execute(\Magento\Framework\Event\Observer $observer) {
            
            if(isset($_POST['addon_price']) && $_POST['addon_price'] != ''){
                    $item = $observer->getEvent()->getData('quote_item'); 
                    $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
                    $price = $_POST['addon_price']; //set your price here
                    $item->setCustomPrice($price);
                    $item->setOriginalCustomPrice($price);
                    $item->getProduct()->setIsSuperMode(true);
            }    
        }
 
    }