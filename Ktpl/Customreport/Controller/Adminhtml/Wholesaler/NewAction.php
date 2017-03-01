<?php
namespace Ktpl\Customreport\Controller\Adminhtml\Wholesaler;
//use Ktpl\Brand\Controller\Adminhtml\News;
 
class NewAction extends \Magento\Backend\App\Action
{
     protected $resultForwardFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
       
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        //echo 'hfsH'; exit;
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }

    protected function _isAllowed()
    {
        return true;//$this->_authorization->isAllowed('Ktpl_Brand::save_brand');
    }
}
