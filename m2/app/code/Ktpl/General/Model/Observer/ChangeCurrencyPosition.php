<?php        
namespace Ktpl\General\Model\Observer;    
use Magento\Framework\Event\ObserverInterface;    
class ChangeCurrencyPosition implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {    

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$currencyOptions = $observer->getEvent()->getCurrencyOptions(); 
		$request = $objectManager->get('Magento\Framework\App\Request\Http');
		$request->getModuleName();
        $moduleName = $request->getModuleName();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();
        $route      = $request->getRouteName();
        
        if ($observer->getEvent()->getBaseCode() == 'EUR' && $moduleName == 'sales' && $controller == 'order_invoice' && $action == 'email' && $route=='sales') {
	        $currencyOptions->setData('position', \Magento\Framework\Currency::RIGHT);  
	        return $this;
    	}
    }    
}