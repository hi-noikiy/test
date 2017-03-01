<?php

namespace Ktpl\Customreport\Controller\Adminhtml\Wholesaler;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Ktpl\Customreport\Model\Wholesaler;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends \Magento\Backend\App\Action {

    
    protected $dataPersistor;
    protected $_logger;
   
    public function __construct(
            Action\Context $context, DataPersistorInterface $dataPersistor,
            \Psr\Log\LoggerInterface $logger 
            
    ) {
        $this->_logger = $logger;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }
    

    public function execute() {
        // print_r($_POST); exit;
        $data = $this->getRequest()->getPostValue();
       // $mid = $this->getRequest()->getPostValue('manufacture_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
          
            if (empty($data['wholesaler_id'])) {
                $data['wholesaler_id'] = null;
            }
            
            $model = $this->_objectManager->create('Ktpl\Customreport\Model\Wholesaler');

            $id = $this->getRequest()->getParam('wholesaler_id');
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Wholesaler has been saved.'));
                $this->dataPersistor->clear('wholesalergrid');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getWholesalerId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Wholesaler.'));
            }

            $this->dataPersistor->set('wholesalergrid', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

}
