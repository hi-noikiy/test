<?php

namespace Ktpl\RepresentativeReport\Block\Adminhtml;

class Salesform extends \Magento\Framework\View\Element\Template
{
    protected $orderConfig;
    protected $formkey;
    
    protected $salesrepHelper;
    
    /**
     * 
     * @param \Magento\Sales\Model\Order\ConfigFactory $orderConfig
     * @param \Cminds\Salesrep\Helper\Data $salesrepHelper
     * @param \Magento\Framework\View\Element\Template\Context $contex
     * @param \Magento\Framework\Data\Form\FormKey $formkey
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\Order\ConfigFactory $orderConfig,
        \Cminds\Salesrep\Helper\Data $salesrepHelper,    
        \Magento\Framework\View\Element\Template\Context $contex,
        \Magento\Framework\Data\Form\FormKey $formkey,    
        array $data = []
    ) {
        $this->orderConfig = $orderConfig;
        $this->formkey = $formkey;
        $this->salesrepHelper = $salesrepHelper;
        parent::__construct($contex,$data);
    }
    
    /**
     * 
     * @return int
     */
    public function Setup()
    {
        if(!empty($_POST)){
            return $_POST;
        }
        return 0;
    }
    
    /**
     * 
     * @return orderstatus array
     */
    public function Orderstatus() {
        $statuses = $this->orderConfig->create()->getStatuses();
            $values = [];
            foreach ($statuses as $code => $label) {
                    $values[] = ['label' => __($label), 'value' => $code];
            }
        return $values;    
    }
    
    /**
     * 
     * @return type
     */
    public function salesrepresentative(){
        return $this->salesrepHelper->getAdminsForReport();
    }
    
    /**
     * 
     * @return url
     */
    public function getFormUrl()
    {
        return $this->getUrl('*/*/index', ['_current' => true]);
    }
    
    /**
     * 
     * @return formkey
     */
    public function Formkey()
    {
        return $this->formkey->getFormKey();
    }
}
