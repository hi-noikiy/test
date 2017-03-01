<?php  
namespace Ktpl\Customreport\Controller\Adminhtml\Cimorder;

use mPDF;


class Salesinvoice extends \Magento\Backend\App\Action {

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
            
            $vatinvoice = $objectManager->create('Ktpl\Customreport\Model\Salesinvoicevat')->load($order_id, 'order_id');
            $vatinvoice->setInvoiceId($data['invoice_id']);
            $vatinvoice->setOrderId($order_id);
            $vatinvoice->setVatregno($data['vatregno']);
            $vatinvoice->setBrn($data['brn']);
            $vatinvoice->save();

            $result = array();
            $template_id = "vat_invoice_email";
        
            $pickuporder = $objectManager->create('Ktpl\Customreport\Model\Pickuporder')->getCollection();
            $pickuporder->addFieldToFilter('real_order_id', array('eq' => $order_id));

            $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
            $billingaddress = $order->getBillingAddress()->getData();

            $shipping_amount = $order->getShippingAmount();
            $reward_points = 1;//$order->getData('rewardpoints_discount');
            $grandtotal = 0; $vat = 0; $subtotal = 0;
            $itemout = "";
            $itemcount = $pickuporder->count(); 
            if($itemcount > 1) { $rawheight = 1000/$itemcount; } else { $rawheight = 400; }
            $i=0;
            foreach($pickuporder as $item) {
                $unit_price = $item->getRetailPrice() / 1.15; 
                $amount = $unit_price * $item->getQty();
                $subtotal += $amount;

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
                    .$item->getSku().'
                    </td>
                    <td valign="top" style="border-right:1px solid #000000; padding:5px 10px; text-align: right; width: 100px;">' 
                    .number_format($unit_price).'  
                    </td>
                    <td valign="top" style="padding:5px 10px; text-align: right; width: 100px;">' 
                    .number_format($amount).' 
                    </td>
                </tr>'; $i++;
            }
            $vat = $subtotal*0.15;
            $grandtotal = $subtotal + $vat;

            if($shipping_amount > 0) {
                $grandtotal += $shipping_amount;
                $shipping_amount = number_format($shipping_amount,2);
            } else { $shipping_amount = ""; }

            if($reward_points > 0) {
                $grandtotal -= $reward_points;
                $reward_points = number_format($reward_points,2);
            } else { $reward_points = ""; }

            $sender = array('name' => 'Priceguru.mu', 'email' => 'info@priceguru.mu');
            $email = "procurement@priceguru.mu";
            $emailName = "Priceguru.mu";
            $vars = array();
            $vars = array(
                'invoice_id' => $data['invoice_id'], 
                'order_id' => $order->getIncrementId(),
                'billname' => $billingaddress['firstname']." ".$billingaddress['lastname'],
                'billto' => $billingaddress['street'],
                'telephone' => $billingaddress['telephone'],
                'vatregno' => $data['vatregno'],
                'brn' => $data['brn'],
                'shipping' => $shipping_amount,
                'itemout' => $itemout,
                //'sku' => $data['sku'],
                //'rate' => number_format($unit_price,2),
                //'qty' => $data['product_qty'],
                'reward_points' => $reward_points,
                'subtotal' => number_format($subtotal,2),
                'vat' => number_format($vat,2),
                'grandtotal' => number_format($grandtotal,2),
                'todaydate' => date('Y-m-d')
            );
            $emailTemplate = $objectManager->create('Magento\Email\Model\Template')->loadDefault($template_id);
            $processedTemplate = $emailTemplate->getProcessedTemplate($vars);
            
            $mpdf=new mPDF('c','A4'); 
                $mpdf->SetProtection(array('print'));
                $mpdf->SetDisplayMode('fullpage');
                $mpdf->WriteHTML($processedTemplate);
                if (!file_exists($this->_dir->getPath('var').'/sale_invoice')) {
                            mkdir($this->_dir->getPath('var').'/sale_invoice', 0777, true);
                        }
                $fn = $this->_dir->getPath('var').'/sale_invoice/invoice_'.$order_id.'.pdf';
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
             $this->messageManager->addSuccess(__('Invoice created successfully.'));
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
