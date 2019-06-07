<?php

class TM_ProLabels_Adminhtml_Prolabels_SystemController extends Mage_Adminhtml_Controller_Action
{
    protected $_productCount = 250;

    protected $_processTime = 20;

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/prolabels/system')
            ->_addBreadcrumb(
                Mage::helper('prolabels')->__('Templates Master'),
                Mage::helper('prolabels')->__('Templates Master')
            )
            ->_addBreadcrumb(
                Mage::helper('prolabels')->__('ProLabels'),
                Mage::helper('prolabels')->__('ProLabels')
            )
            ->_addBreadcrumb(
                Mage::helper('prolabels')->__('System Labels Manager'),
                Mage::helper('prolabels')->__('System Labels Manager')
            );
        return $this;
    }

    /**
     * Banner list page
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('TM'))
            ->_title($this->__('Prolabels'))
            ->_title($this->__('System Labels'));
        $this->_addContent(
            $this->getLayout()->createBlock('prolabels/adminhtml_system')
        );
        $this->renderLayout();
    }

    /**
     * Create new banner
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Banner edit form
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('prolabels/system');
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('prolabels')->__('This label no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
            $model->setData('label_status', $model->getData('l_status'));
            $model->unsData('l_status');
            $model->setData('label_name', $model->getData('system_label_name'));
            $model->unsData('system_label_name');
        }

        $this->_title($this->__('TM'))
            ->_title($this->__('Prolabels'))
            ->_title($this->__('System Labels'));
        if ($model->getId()) {
            $this->_title($this->__("Edit Label '%s'", $model->getSystemLabelName()));
        } else {
            $this->_title($this->__('Add Label'));
        }

        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        Mage::register('prolabels_system_rules', $model);

        $this->loadLayout(array('default', 'editor'))
            ->_setActiveMenu('templates_master/prolabels/system')
            ->_addBreadcrumb(Mage::helper('prolabels')->__('Templates Master'), Mage::helper('prolabels')->__('Templates Master'))
            ->_addBreadcrumb(Mage::helper('prolabels')->__('ProLabels'), Mage::helper('prolabels')->__('ProLabels'))
            ->_addBreadcrumb(Mage::helper('prolabels')->__('Rules Manager'), Mage::helper('prolabels')->__('Labels Manager'));

        $this
            ->_addBreadcrumb($id ? Mage::helper('prolabels')->__('Edit Label') : Mage::helper('prolabels')->__('New Label'), $id ? Mage::helper('prolabels')->__('Edit Label') : Mage::helper('prolabels')->__('New Label'))
            ->_addContent(
                $this->getLayout()->createBlock('prolabels/adminhtml_system_edit')
                    ->setData('action', $this->getUrl('*/*/save'))
                    ->setData('form_action_url', $this->getUrl('*/*/save'))
            )
            ->_addLeft($this->getLayout()->createBlock('prolabels/adminhtml_system_edit_tabs'))
            ->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('prolabels/adminhtml_system_grid')->toHtml()
        );
    }

    /**
     * Save label
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $data['l_status'] = $data['label_status'];
            $data['system_label_name'] = $data['label_name'];
            unset($data['label_status']);
            unset($data['label_name']);
            try {
                $result = array();
                $storeModel = Mage::getModel('prolabels/sysstore');

                foreach ($data['store_id'] as $store) {
                    if ($store == '0') {
                        if ($storeModel->allStoreLabelExist($data['rules_id'], $data['system_id'])) {
                            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('prolabels')->__('Label not saved! Please select other store.'));
                            Mage::getSingleton('adminhtml/session')->setPageData($data);
                            if ($data['system_id'] != '') {
                                $this->_redirect('*/*/edit', array('id' => $data['system_id']));
                                return;
                            } else {
                                $this->_redirect('*/*/new', array('rulesid' => $data['rules_id']));
                                return;
                            }
                        }
                    }
                    if ($storeModel->storeLabelExist($store, $data['rules_id'], $data['system_id'])) {
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('prolabels')->__('Label not saved! Please select other store.'));
                        Mage::getSingleton('adminhtml/session')->setPageData($data);
                        if ($data['system_id'] != '') {
                            $this->_redirect('*/*/edit', array('id' => $data['system_id']));
                            return;
                        } else {
                            $this->_redirect('*/*/new', array('rulesid' => $data['rules_id']));
                            return;
                        }
                    }
                }

                $model = Mage::getModel('prolabels/system');
                if ($data['system_id'] == '') {
                    $model->setId(null);
                    unset($data['system_id']);
                }
                $model->addData($data);
                /*
                ** Save customer groups
                 */
                if (Mage::getStoreConfig("prolabels/general/customer_group")) {
                    $model->setData('customer_group', serialize($data['customer_group_ids']));
                }
                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
                $model->save();
                $storeModel->deleteSystemStore($model->getId());
                foreach ($data['store_id'] as $store) {
                    $storeM = Mage::getModel('prolabels/sysstore');
                    $storeM->addData(array('store_id' => $store));
                    $storeM->addData(array('system_id' => $model->getId()));
                    $storeM->addData(array('rules_id' => $model->getRulesId()));
                    $storeM->save();
                }
                $this->getRequest()->setParam('system_id', $model->getId());
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('prolabels')->__('Label was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/new', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('prolabels/system');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('prolabels')->__('Label was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('prolabels')->__('Unable to find a label to delete'));
        $this->_redirect('*/*/');
    }

    public function relatedGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('prolabels/system');
        $model->load($id);
        Mage::register('prolabels_system_rules', $model);
        $this->loadLayout();

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('prolabels/adminhtml_system_edit_tab_products')->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/prolabels/system');
    }
}
