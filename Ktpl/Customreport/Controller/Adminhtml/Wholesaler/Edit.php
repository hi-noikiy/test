<?php
namespace Ktpl\Customreport\Controller\Adminhtml\Wholesaler;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
   protected $_coreRegistry = null;

    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;//$this->_authorization->isAllowed('Ktpl_Brand::save_brand');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    { 
         $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ktpl_Customreport::a_menu_item5')
            ->addBreadcrumb(__('Wholesaler'), __('Wholesaler'))
            ->addBreadcrumb(__('Manage Wholesaler'), __('Manage Wholesaler'));
        return $resultPage;
        
        
    }

    /**
     * Edit Blog post
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {

         $id = $this->getRequest()->getParam('id'); 
        $model = $this->_objectManager->create('Ktpl\Customreport\Model\Wholesaler');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Wholesaler no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        } 

//        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
//        if (!empty($data)) {
//            $model->setData($data);
//        }

        $this->_coreRegistry->register('wholesalergrid', $model);
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction(); //echo 'asdg'; exit;
        $resultPage->addBreadcrumb(
            $id ? __('Edit Wholesaler') : __('New Wholesaler'),
            $id ? __('Edit Wholesaler') : __('New Wholesaler')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Wholesaler'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getWholesalerId() ? $model->getName() : __('New Wholesaler'));
       // echo 'dfha'; exit;
        return $resultPage;
    }
}
 