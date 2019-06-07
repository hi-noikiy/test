<?php

/**
 * Hatimeria Orders Manager controller
 *
 * @category   Hatimeria
 * @package    Hatimeria_OrderManager
 */

class Hatimeria_OrderManager_Adminhtml_PeriodController extends Mage_Adminhtml_Controller_Action
{
    const CHECKBOXES = 3;

    protected $_objectId = 'order_id';
    protected $_blockGroup = 'hordermanager';
    protected $_controller = 'adminhtml_period';
    protected $_mode = 'edit';

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/hordermanager');
    }
    /**
     * Init actions
     *
     * @return Hatimeria_OrderManager_Adminhtml_PeriodController
     */
    protected function _initAction()
    {
        $helper = Mage::helper('hordermanager');

        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('hordermanager/period')
            ->_addBreadcrumb($helper->__('Period Control'), $helper->__('Period Control'))
            ->_addBreadcrumb($helper->__('Period'), $helper->__('Period'));

        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Order Manager'))->_title($this->__('Period'));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * View action
     */
    public function viewAction()
    {
        $this->_title($this->__('Order Manager'))->_title($this->__('Period'));
        $helper = Mage::helper('hordermanager');
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('period_id');
        $model = Mage::getModel('hordermanager/period');

        // 2. Initial checking
        if ($id) {
            $model->load($id);

            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($helper->__('This entity no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // 4. Register model to use later in blocks
        Mage::register('current_period', $model);

        // 5. Build edit form
        $this->_initAction()
            ->_addBreadcrumb($id ? $helper->__('Edit Period') : $helper->__('New Period'),
                $id ? $helper->__('Edit Period') : $helper->__('New Period'))
            ->renderLayout();
    }

    /**
     * Hide action
     */
    public function hideAction()
    {
        $helper = Mage::helper('hordermanager');
        $periodId = $this->getRequest()->getParam('period_id');
        // 1. Get ID and create model
        $orderId = $this->getRequest()->getParam('order_link_id');
        $order = Mage::getModel('hordermanager/order');

        // 2. Initial checking
        if ($orderId) {
            $order->load($orderId);

            if ($order->isObjectNew()) {
                Mage::throwException('Not found!');
            }

            if (!$order->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($helper->__('This entity no longer exists.'));
                $this->_redirect('*/*/');
                return;
            } else {
                   $order->load($orderId)
                   ->setIsHidden('1')
                   ->save();
           }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('This order is not visible now.'));
        $this->_redirect('*/*/view', array('period_id' => $periodId));
        return;
    }

    /**
     * Updating orders
     */
    public function updatePostAction()
    {
        if (!$this->getRequest()->isPost()) {
           $this->_redirect('*/*/');
        }
        $items = $this->getRequest()->getPost('items');
        $orders = $this->getRequest()->getPost('orders');
        $periodId = $this->getRequest()->getParam('period_id');

        $defaultData = array(
            'supplier_notes' => '',
            'admin_notes' => '',
            'ordered' => '0',
            'in_stock' => '0'
        );

        $ordersCollection = Mage::getModel('hordermanager/order_item')->getCollection()
            ->addFieldToFilter('order_id', $orders)->load();

        foreach ($ordersCollection as $item) {
            $newData = array_merge($defaultData, $items[$item->getItemId()]);
            $item->setSupplierNotes($newData['supplier_notes']);
            $item->setAdminNotes($newData['admin_notes']);
            $item->setOrdered($newData['ordered']);
            $item->setInStock($newData['in_stock']);

            $item->save();
        }

        $this->_redirect('*/*/view', array('period_id' => $periodId));
    }

    /**
     * Edit action
     */
    public function editAction()
    {
        $helper = Mage::helper('hordermanager');
        $this->_title($this->__('Order Manager'))->_title($this->__('Order'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('period_id');
        $model = Mage::getModel('hordermanager/period');

        // 1.1 Create order model
        $orderModel = Mage::getModel('hordermanager/order');
        Mage::register('order', $orderModel);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($helper->__('This entity no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getTitle() : $this->__('New Order'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (! empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('period', $model);

        // 5. Build edit form
        $this->_initAction()
            ->_addBreadcrumb($id ? $helper->__('Edit Period') : $helper->__('New Order'),
                $id ? $helper->__('Edit Period') : $helper->__('New Period'))
            ->renderLayout();
    }

    /**
     * Save order visibility action
     */
    public function saveAction()
    {
        $helper = Mage::helper('hordermanager');
        $periodId = $this->getRequest()->getParam('period_id');

        // check if data sent
        if ($data = $this->getRequest()->getPost()) {

            $orderId = $data['order_id'];

            $orderModelCollection = Mage::getModel('hordermanager/order')->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('period_id', $periodId);

            $orderModel = $orderModelCollection->getFirstItem();

            if (!$orderModel->getId() && $orderId) {
                Mage::getSingleton('adminhtml/session')->addError($helper->__('This entity no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }

            $orderModel->setIsHidden($data['visibility']);

            // try to save it
            try {
                // save the data
                $orderModel->save();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('The Order is now visible.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('period_id' => $periodId));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/view', array('period_id' => $periodId));
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('period_id' => $periodId));
                return;
            }
        }
        $this->_redirect('*/*/view', array('period_id' => $periodId));
    }

    public function initAction()
    {
        $helper = Mage::helper('hordermanager');
        $period = Mage::getSingleton('hordermanager/period');

        if ('supplier' != Mage::getSingleton('admin/session')->getUser()->getUsername()) {

            try {
                $period->initPeriods();
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($helper->__('The Periods have not been initialized.'));
                Mage::log('Init from controller: ' . $e->getMessage(), null, 'hordermanager_init_model_exception.log');
                return;
            }
            Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('The Periods have been initialized.'));
        }

        $this->_redirectReferer('*/*/index');
        return;
    }

    public function clearAction()
    {
        $helper = Mage::helper('hordermanager');
        $period = Mage::getSingleton('hordermanager/period');

        if ('supplier' != Mage::getSingleton('admin/session')->getUser()->getUsername()) {
            try {
                $period->clearPeriods();
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($helper->__('The Periods have not been cleaned.'));
                Mage::log('Clear from controller: ' . $e->getMessage(), null, 'hordermanager_init_model_exception.log');
                return;
            }

            Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('The Periods have been deleted.'));
        }
        $this->_redirectReferer('*/*/index');
        return;
    }
}