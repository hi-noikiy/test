<?php  
namespace Ktpl\Customreport\Controller\Adminhtml\Cimorder;

use mPDF;


class Purchaseorder extends \Magento\Backend\App\Action {

    protected $_transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $storeManager;
    protected $_escaper;
    protected $_dir;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct( 
            \Magento\Framework\App\Action\Context $context,
            \Ktpl\Customreport\Model\Trans $transportBuilder, 
            \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, 
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
            \Magento\Store\Model\StoreManagerInterface $storeManager, 
            \Magento\Framework\Filesystem\DirectoryList $dir,
            \Magento\Framework\Escaper $escaper
    ) { 
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_escaper = $escaper;
         $this->_dir = $dir;
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
            
            $template_id = "purchase_order_pdf";
            $pickuporder = $objectManager->create('Ktpl\Customreport\Model\Pickuporder')->load($pickup_id);
            if($pickuporder->getData('po_created') == 0) { $pickuporder->setData('po_created', 1); $pickuporder->save(); }
            
            $itemoutput = "";
            $grandtotal = 0;
        //echo 'asdg12'; exit;
            $increment_id = $pickuporder->getOrderId();
            $wholesaler = $objectManager->create('Ktpl\Customreport\Model\Wholesaler')->load($pickuporder->getWholesalerId());
            if($wholesaler) {
                    $wholesaler_name = $wholesaler->getName();
                    $wholesaler_address = $wholesaler->getAddress();
            } else {
                    $wholesaler_name =""; $wholesaler_address="";
            }
            $subtotal = $pickuporder->getWholesalePrice() * $pickuporder->getQty();
            $grandtotal += $subtotal;

            $itemoutput .= '<tr>
                    <td valign="top" width="300" height="700" style="border-right:1px solid #000000; padding:5px 10px; text-align: left;">';
                    if($pickuporder->getAttributes() != "") { 
                    $itemoutput .= 'Options: '. $pickuporder->getAttributes() .'<br>';
                }
                $itemoutput .= $pickuporder->getSku().'
                    </td>
                    <td valign="top" width="80" style="border-right:1px solid #000000; padding:5px 10px; text-align: right;">'. 
                    $pickuporder->getQty().'
                    </td>
                    <td valign="top" width="120" style="border-right:1px solid #000000; padding:5px 10px; text-align: right;">'. 
                    number_format($pickuporder->getWholesalePrice()).'
                    </td>
                    <td valign="top" width="120" style="padding:5px 10px; text-align: right;">'. 
                    number_format($subtotal).'
                    </td>
              </tr>';

            $sender = array('name' => 'Priceguru.mu', 'email' => 'info@priceguru.mu');
            $email = "procurement@priceguru.mu";
            $emailName = "Purchase Order";
            $vars = array();
            $vars = array(
        	'real_order_id' => $order_id, 
        	'increment_id' => $increment_id,
        	'itemoutput' => $itemoutput,
        	'grandtotal' => number_format($grandtotal),
        	'vendor' => $wholesaler_name.", ".$wholesaler_address, 
        	'todaydate' => date('Y-m-d')
            );
            $emailTemplate = $objectManager->create('Magento\Email\Model\Template')->loadDefault($template_id);
            $processedTemplate = $emailTemplate->getProcessedTemplate($vars);
            $mpdf=new mPDF('c','A4'); 
			$mpdf->SetProtection(array('print'));
			$mpdf->SetDisplayMode('fullpage');
			$mpdf->WriteHTML($processedTemplate);
                        if (!file_exists($this->_dir->getPath('var').'/purchase_order')) {
                            mkdir($this->_dir->getPath('var').'/purchase_order', 0777, true);
                        }
			$fn = $this->_dir->getPath('var').'/purchase_order/invoice_'.$order_id.'.pdf';
			$mpdf->Output($fn, 'F');
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->_transportBuilder
                    ->setTemplateIdentifier(1) // this code we have mentioned in the email_templates.xml
                    ->setTemplateOptions(
                            [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                            ]
                    )
                    ->setTemplateVars($vars)
                    ->setFrom($sender)
                    ->addTo($email,$emailName)
                    ->addAttachment(file_get_contents($fn))
                    ->getTransport();
		
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            $this->messageManager->addSuccess(__('Purchase order created successfully.'));
            $this->_redirect('sales/order/view/', array('order_id' => $order_id));
            return;
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                    __('Unable to send email.' . $e->getMessage())
            );
            $this->_redirect('sales/order/view/', array('order_id' => $order_id));
            return;
        }
    }

    protected function _isAllowed() {
        return true;
    }

}
