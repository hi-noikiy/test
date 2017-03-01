<?php

namespace Ktpl\Customreport\Controller\Adminhtml\Cimorder;

class Sendemail extends \Magento\Backend\App\Action {

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
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->_redirect('*/*/');
            return;
        }

        $this->inlineTranslation->suspend();
        try {
            
            $error = false;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $cimemail = $this->getRequest()->getParam('cim-email-template');
            $template_id = "cimorder_email_".$cimemail;
            $order_id = $this->getRequest()->getParam('order_id');
            $real_id = $this->getRequest()->getParam('real_id');
            $order = $objectManager->create('Ktpl\Customreport\Model\Cimorder')->load($order_id, 'order_id');
            
            $email = 'khodu.vaishnav@krishtechnolabs.com';//$order->getEmail();
            $emailName = $order->getCustomerName();
            $vars = array();
            $vars = array('order' => $order);
            $sender = ['name' => 'Priceguru.mu','email' => 'credit@priceguru.mu',];

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
           /* $transport = $this->_transportBuilder
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
                    ->getTransport();

            $transport->sendMessage();*/
            $this->inlineTranslation->resume();
            $this->messageManager->addSuccess(
                    __('CIM order email send successfully.')
            );
            $this->_redirect('sales/order/view/', array('order_id' => $real_id));
            return;
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                    __('Unable to send email.' . $e->getMessage())
            );
            $this->_redirect('*/*/');
            return;
        }
    }

    protected function _isAllowed() {
        return true;
    }

}
