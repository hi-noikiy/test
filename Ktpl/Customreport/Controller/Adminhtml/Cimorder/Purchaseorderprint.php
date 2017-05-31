<?php  
namespace Ktpl\Customreport\Controller\Adminhtml\Cimorder;

use mPDF;


class Purchaseorderprint extends \Magento\Backend\App\Action {

    protected $_transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $storeManager;
    protected $_escaper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct( 
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, 
            \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, 
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
            \Magento\Store\Model\StoreManagerInterface $storeManager, 
            \Magento\Framework\Escaper $escaper
    ) { 
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_escaper = $escaper;
    }

    /**
     * Post user question
     *
     * @return void
     * @throws \Exception
     */
    public function execute() {
        
        
        $order_id = $this->getRequest()->getParam('order_id');
        $pickup_id = $this->getRequest()->getParam('item_id');

        $this->inlineTranslation->suspend();
        try {
            $error = false;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            
            $template_id = "purchase_order_email";
            $order_id = $this->getRequest()->getParam('order_id');
            $real_id = $this->getRequest()->getParam('real_id');
            $order = $objectManager->create('Ktpl\Customreport\Model\Pickuporder')->getCollection();
            $order->addFieldToFilter('pickup_id', array('eq' => $pickup_id));
            $order->addFieldToFilter('real_order_id', array('eq' => $order_id));
             
            $itemoutput = "";
            $grandtotal = 0;
            foreach($order as $item) {
                $increment_id = $item->getOrderId();
        	 $wholesaler = $objectManager->create('Ktpl\Customreport\Model\Wholesaler')->load($item->getWholesalerId());
        	if($wholesaler) {
        		$wholesaler_name = $wholesaler->getName();
        		$wholesaler_address = $wholesaler->getAddress();
        	} else {
        		$wholesaler_name =""; $wholesaler_address="";
        	}
        	$subtotal = $item->getWholesalePrice() * $item->getQty();
        	$grandtotal += $subtotal;

        	$itemoutput .= '<tr>
        		<td valign="top" width="300" height="700" style="border-right:1px solid #000000; padding:5px 10px; text-align: left;">';
        		if($item->getAttributes() != "") { 
                	$itemoutput .= 'Options: '. $item->getAttributes() .'<br>';
                }
                $itemoutput .= $item->getSku().'
              	</td>
              	<td valign="top" width="80" style="border-right:1px solid #000000; padding:5px 10px; text-align: right;">'. 
                	$item->getQty().'
             	</td>
              	<td valign="top" width="120" style="border-right:1px solid #000000; padding:5px 10px; text-align: right;">'. 
                	number_format($item->getWholesalePrice()).'
              	</td>
              	<td valign="top" width="120" style="padding:5px 10px; text-align: right;">'. 
                	number_format($subtotal).'
              	</td>
              </tr>';
            }
            $sender = array('name' => 'Priceguru.mu', 'email' => 'info@priceguru.mu');
            $email = "khodu.vaishnav@krishtechnolabs.com";
            $emailName = "Khodu";
        
            $emailvars = array();
            $emailvars['real_order_id'] = $order_id;
            $emailvars['increment_id'] = $increment_id;
            $emailvars['itemoutput'] = $itemoutput;
            $emailvars['grandtotal'] = number_format($grandtotal);
            $emailvars['vendor'] = $wholesaler_name.", ".$wholesaler_address;
            $emailvars['todaydate'] = date('Y-m-d');
            
            $emailTemplate = $objectManager->create('Magento\Email\Model\Template')->loadDefault('purchase_order_pdf');
            $processedTemplate = $emailTemplate->getProcessedTemplate($emailvars);
       
        

			$mpdf=new mPDF('c','A4'); 
			$mpdf->SetProtection(array('print'));
			$mpdf->SetDisplayMode('fullpage');
			$mpdf->WriteHTML($processedTemplate);
			$fn = 'invoice'. $order_id .'.pdf';
			$mpdf->Output($fn, 'I');
		
        } catch (\Exception $e) {
                $result['error'] =  __('Unable to create print copy.');
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                    __('Unable to create print copy.' . $e->getMessage())
            );
           
        }
        //echo 'asdg12'; exit;
           

    }

    protected function _isAllowed() {
        return true;
    }

}
