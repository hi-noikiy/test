<?php  
namespace Ktpl\Customreport\Controller\Adminhtml\Cimorder;

use mPDF;


class Deliverynote extends \Magento\Backend\App\Action {

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
        //$pickup_id = $this->getRequest()->getParam('item_id');
        $data = $this->getRequest()->getPost();
         $this->inlineTranslation->suspend();
        try {
            $error = false;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $result = array();
            $template_id = "delivery_note";
            $pickuporder = $objectManager->create('Ktpl\Customreport\Model\Pickuporder')->getCollection();
            $pickuporder->addFieldToFilter('real_order_id', array('eq' => $order_id));
            
            $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
            $billingaddress = $order->getBillingAddress()->getData();

            //Start email processing and variable input
            $itemout = "";
            $deposit_amount = "";
            $payment_code = $order->getPayment()->getMethodInstance()->getCode();
            if($order->getIscimorder()) {
        	$salescimorder = $objectManager->create('Ktpl\Customreport\Model\Cimorder')->load($order->getIncrementId(), 'order_id');
        	$deposit_amount = $salescimorder->getDeposit();
        	$payment_method = "CIM";
        	if($deposit_amount == NULL) {
        		$payment_status = "No Deposit";	
        	} else { $payment_status = "Deposit"; }
        	
        } elseif($payment_code == "cashondelivery") {
	    	$payment_method = "Pay on Delivery";
	    	$payment_status = "Unpaid";
	    } elseif($payment_code == "banktransfer") {
	    	$payment_method = "Internet Banking";
	    	$payment_status = "Paid";
	    } else {
	    	$payment_method = "Credit Card";
	    	$payment_status = "Paid";
	    } 
       
        $grandtotal = 0; $vat = 0; $subtotal = 0;
        $itemcount = $pickuporder->count(); 
        if($itemcount > 1) { $rawheight = 1000/$itemcount; } else { $rawheight = 400; }
        $i=0;
        foreach($pickuporder as $item) {
                $unit_price = $item->getRetailPrice() / 1.15; 
                $amount = $unit_price * $item->getQty();
                $subtotal += $amount;
            $displayname = $item->getProductName().' - '.$item->getSku();
            $itemout .= '<tr>';
            if($i == $itemcount - 1) {
            $itemout .='<td valign="top" height="'.round($rawheight).'" style="border-right:1px solid #000000; padding:5px 10px; text-align: right; width: 100px;">'
                .$item->getQty().'
                </td>';
            } else {
                $itemout .='<td valign="top" style="border-right:1px solid #000000; padding:5px 10px; text-align: right; width: 100px;">'
                .$item->getQty().'
                </td>';
            }
            $itemout .= '<td valign="top" style="border-right:1px solid #000000; padding:5px 10px; text-align: left; width: 320px;">' 
                .$displayname.'</td>
            </tr>'; $i++;
        }
        $sender = array('name' => 'Priceguru.mu', 'email' => 'info@priceguru.mu');
        //recepient
        $email = "procurement@priceguru.mu";
        $emailName = "Purchase Order";
        
        //$email = "prashant.gohil@krishtechnolabs.com";
        //$emailName = "Prashant";

        //$subtotal = $order->getWholesalePrice() * $order->getQty();
        $vars = array();
        $vars = array(
            'increment_id' => $order->getIncrementId(), 
            'order_id' => $order_id,
            'billname' => $billingaddress['firstname']." ".$billingaddress['lastname'],
            'billto' => $billingaddress['street'],
            'telephone' => $billingaddress['telephone'],
            'sku' => $data['sku'],
            'rate' => number_format($unit_price),
            'qty' => $data['product_qty'],
            'itemout' => $itemout,
            'deposit' => $deposit_amount,
            'payment_method' => $payment_method,
            'payment_status' => $payment_status,
            //'subtotal' => number_format($subtotal),
            //'vat' => number_format($vat,2),	
            //'grandtotal' => number_format($grandtotal,2),
            'todaydate' => date('Y-m-d')
        );
        $emailTemplate = $objectManager->create('Magento\Email\Model\Template')->loadDefault($template_id);
            $processedTemplate = $emailTemplate->getProcessedTemplate($vars);
            
            $mpdf=new mPDF('c','A4'); 
                $mpdf->SetProtection(array('print'));
                $mpdf->SetDisplayMode('fullpage');
                $mpdf->WriteHTML($processedTemplate);
                if (!file_exists($this->_dir->getPath('var').'/deliverynote')) {
                            mkdir($this->_dir->getPath('var').'/deliverynote', 0777, true);
                }
                $fn = $this->_dir->getPath('var').'/deliverynote/invoice_'.$order_id.'.pdf';
                $mpdf->Output($fn, 'F');
       
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($template_id) // this code we have mentioned in the email_templates.xml
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
             $this->messageManager->addSuccess(__('Delivery note invoice created successfully.'));
             $this->_redirect('sales/order/view/', array('order_id' => $order_id));
             return;
        } catch(\Exception $e) {
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
