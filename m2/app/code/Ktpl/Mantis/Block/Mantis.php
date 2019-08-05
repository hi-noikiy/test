<?php

namespace Ktpl\Mantis\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 *
 * @api
 * @since 100.0.2
 */
	
	class Mantis extends Template {
	
	public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Customer\Model\Session $customerSession,  
    \Magento\Framework\ObjectManagerInterface $objectManager,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Ktpl\General\Helper\Data $helper,
    array $data = []
 	) {
    parent::__construct($context, $data);
     $this->customerSession = $customerSession;
     $this->_objectManager = $objectManager;
     $this->checkoutSession = $checkoutSession;
     $this->helper = $helper;
	}

      public function QuartData()
	{
		 $orderId = $this->checkoutSession->getLastOrderId();
		 
		 if($orderId){
		 $order =  $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
		 $QuartData['orderId'] = '';
		 $QuartData['gTotal'] = '';
		 $request = $this->_objectManager->get('Magento\Framework\App\Request\Http');
		 $request->getModuleName();
         $moduleName = $request->getModuleName();
         $controller = $request->getControllerName();
         $action     = $request->getActionName();
         $route      = $request->getRouteName();
        
        if ($moduleName == 'checkout' && $controller == 'onepage' && $action == 'success' && $route=='checkout') {
		 $QuartData['orderId'] = $orderId;
		 $total = $order->getGrandTotal();
		 $QuartData['gTotal'] = $total;
		}
		 return $QuartData;
		}
	}

	public function mantisCode()
	{
		    $data = $this->QuartData();
		    $getTransaction = $this->getAdvertiserCode();
			echo $str = "<script type='text/javascript'>
							var mantis = mantis || [];
							mantis.push(['analytics', 'load', {
							advertiser: '".$getTransaction."',
							transaction: '".$data['orderId']."',
							revenue: '".$data['gTotal']."'
							}]);
							</script>";
	}

	public function getTransaction()
	{
		$data = $this->QuartData();
		return $data['orderId'];		
	}

	public function getAdvertiserCode()
	{
		return $this->helper->getConfig('ktpl_mentis_section/mentis/code');
	}	
}