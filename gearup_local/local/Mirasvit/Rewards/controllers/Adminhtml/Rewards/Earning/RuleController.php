<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rewards_Adminhtml_Rewards_Earning_RuleController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return Mirasvit_Rewards_Adminhtml_Rewards_Earning_RuleController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('rewards');

        return $this;
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Earning Rules'));
        $this->_initAction();
        $this->_addContent($this->getLayout()
            ->createBlock('rewards/adminhtml_earning_rule'));
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function addAction()
    {
        $this->_title($this->__('New Earning Rule'));

        $this->_initEarningRule();

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('EarningRule  Manager'),
                Mage::helper('adminhtml')->__('EarningRule Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Earning Rule '),
            Mage::helper('adminhtml')->__('Add Earning Rule'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('rewards/adminhtml_earning_rule_edit'))
                ->_addLeft($this->getLayout()->createBlock('rewards/adminhtml_earning_rule_edit_tabs'));
        $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $earningRule = $this->_initEarningRule();

        if ($earningRule->getId()) {
            $this->_title($this->__("Edit Earning Rule '%s'", $earningRule->getName()));
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Earning Rules'),
                    Mage::helper('adminhtml')->__('Earning Rules'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Earning Rule '),
                    Mage::helper('adminhtml')->__('Edit Earning Rule '));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('rewards/adminhtml_earning_rule_edit'))
                    ->_addLeft($this->getLayout()->createBlock('rewards/adminhtml_earning_rule_edit_tabs'));
            $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('The Earning Rule does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $earningRule = $this->_initEarningRule();
            $earningRule->addData($data);
            if (isset($data['rule'])) {
                $earningRule->loadPost($data['rule']);
            }
            if (!$earningRule->getType() && $earningRule->getOrigData('type')) {
                $earningRule->setType($earningRule->getOrigData('type'));
            }
            //format date to standart
            $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            Mage::helper('mstcore/date')->formatDateForSave($earningRule, 'active_from', $format);
            Mage::helper('mstcore/date')->formatDateForSave($earningRule, 'active_to', $format);

            // Transfer to Group should be checked every time rule is saved - just in case
            $groups = Mage::getModel('customer/group')->getCollection()->toOptionArray();
            if (!array_key_exists($earningRule->getTransferToGroup(), $groups)) {
                $earningRule->setData('transfer_to_group', null);
            }


            try {
                $earningRule->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Earning Rule was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $earningRule->getId(),
                        'store' => $earningRule->getStoreId()));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('adminhtml')->__('Unable to find Earning Rule to save'));
        $this->_redirect('*/*/');
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $earningRule = Mage::getModel('rewards/earning_rule');

                $earningRule->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Earning Rule was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                    ->getParam('id'), ));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * @return void
     */
    public function massChangeAction()
    {
        $ids = $this->getRequest()->getParam('earning_rule_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Please select Earning Rule(s)'));
        } else {
            try {
                $isActive = $this->getRequest()->getParam('is_active');
                foreach ($ids as $id) {
                    /** @var Mirasvit_Rewards_Model_Earning_Rule $earning_rule */
                    $earningRule = Mage::getModel('rewards/earning_rule')->load($id);
                    $earningRule->setIsActive($isActive);
                    $earningRule->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully updated', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * @return void
     */
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('earning_rule_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Please select Earning Rule(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    /** @var Mirasvit_Rewards_Model_Earning_Rule $earningRule */
                    $earningRule = Mage::getModel('rewards/earning_rule')
                        ->setIsMassDelete(true)
                        ->load($id);
                    $earningRule->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * @return Mirasvit_Rewards_Model_Earning_Rule
     */
    public function _initEarningRule()
    {
        $earningRule = Mage::getModel('rewards/earning_rule');
        if ($this->getRequest()->getParam('id')) {
            $earningRule->load($this->getRequest()->getParam('id'));
            if ($storeId = (int) $this->getRequest()->getParam('store')) {
                $earningRule->setStoreId($storeId);
            }
        }

        Mage::register('current_earning_rule', $earningRule);

        return $earningRule;
    }

    /**
     * @return void
     */
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $param = $this->getRequest()->getParam('type');
        $parts = explode('%', $param);
        $ruleType = null;
        if (isset($parts[1])) {
            $ruleType = $parts[1];
            $param = $parts[0];
        }
        if (empty($ruleType) && isset($parts[0])) {
            $ruleType = $parts[0];
        }
        $typeArr = explode('|', str_replace('-', '/', $param));
        $type = $typeArr[0];

        $ruleModel = Mage::getModel('rewards/earning_rule')->setType($ruleType);
        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule($ruleModel)
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewards/earning_rule');
    }

    /************************/

    /**
     * @return void
     * @throws Exception
     */
    public function applyRulesAction()
    {
        try {
            Mage::getModel('rewards/earning_rule')->applyAll();
            Mage::app()->removeCache('catalog_rules_dirty');
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('catalogrule')->__('The rules have been applied.')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('catalogrule')->__('Unable to apply rules.')
            );
            throw $e;
        }
        $this->_redirect('*/*');
    }
}
