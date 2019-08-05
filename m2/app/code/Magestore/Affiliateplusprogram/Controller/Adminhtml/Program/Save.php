<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Controller\Adminhtml\Program;

/**
 * Class Save
 * @package Magestore\Affiliateplusprogram\Controller\Adminhtml\Program
 */
class Save extends \Magestore\Affiliateplusprogram\Controller\Adminhtml\AbstractAction
{
    /**
     * @param $data
     * @return mixed
     */
    protected function getData($data)
    {
        
        $valid_to = $data['valid_to'];

        $inputFilter = new \Zend_Filter_Input(
            ['valid_from' => $this->_dateFilter, 'valid_to' => $this->_dateFilter],
            [],
            $data
        );

        $data = $inputFilter->getUnescaped();
        if (isset($data['valid_from']) && $data['valid_from'] == '') {
            $data['valid_from'] = null;
        }
        if (isset($data['valid_to']) && $data['valid_to'] == '') {
            $data['valid_to'] = null;
        }

        if(empty($valid_to)){
            $data['valid_to'] = null;
        }

        if (isset($data['rule'])) {
            $rules = $data['rule'];
            if (isset($rules['conditions']))
                $data['conditions'] = $rules['conditions'];
            if (isset($rules['actions']))
                $data['actions'] = $rules['actions'];
            unset($data['rule']);
        }

        if (isset($data['program_name'])) {
            $data['name'] = $data['program_name'];
        }
        if (isset($data['customer_groups']) && is_array($data['customer_groups'])) {
            $data['customer_groups'] = implode(',', $data['customer_groups']);
        }
        return $data;
    }

    /**
     * @param $model
     * @param $data
     * @return array
     */
    public function getProgramAccount($model,
                                      $data)
    {
        if (isset($data['program_account']) && is_string($data['program_account'])) {
            parse_str($data['program_account'], $programAccount);
            $programAccount = array_unique(array_keys($programAccount));
        }
        if (isset($data['autojoin']) && $data['autojoin'] && isset($data['scope']) && $data['scope'] == \Magestore\Affiliateplusprogram\Model\Scope::SCOPE_GLOBAL) {
            $collections = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Account\Collection');
            if ($model->getId()) {
                $collections->getSelect()
                    ->joinLeft(
                        [
                            'j' => $collections->getTable('magestore_affiliateplusprogram_joined')
                        ], 'main_table.account_id = j.account_id AND j.program_id = ' . $model->getId(), array()
                    )->where('j.id IS NULL');
            }
            $data['program_account'] = $collections->getAllIds();
        } elseif (isset($data['autojoin']) && $data['autojoin'] && isset($data['scope']) && $data['scope'] == \Magestore\Affiliateplusprogram\Model\Scope::SCOPE_GROUPS) {
            if ($model->getData('customer_groups')) {
                $customerGroups = explode(',', $model->getData('customer_groups'));
                $collections = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Account\Collection');
                $collections->getSelect()
                    ->joinLeft(
                        [
                            'c' => $collections->getTable('customer_entity')
                        ], 'main_table.customer_id = c.entity_id', array()
                    )->where('FIND_IN_SET(c.group_id, ?)', implode(',', $customerGroups));
                if ($model->getId()) {
                    $collections->getSelect()
                        ->joinLeft(
                            [
                                'j' => $collections->getTable('magestore_affiliateplusprogram_joined')
                            ], 'main_table.account_id = j.account_id AND j.program_id = ' . $model->getId(), array()
                        )->where('j.id IS NULL');
                }
                $data['program_account'] = $collections->getAllIds();
            }
        }
        if (isset($programAccount) && $programAccount) {
            if (isset($data['program_account']) && is_array($data['program_account'])) {
                $data['program_account'] = array_unique(array_merge($programAccount, $data['program_account']));
            } else {
                $data['program_account'] = $programAccount;
            }

        } elseif (isset($data['program_account']) && is_array($data['program_account']) && $model->getId()) {
            $joinedAccounts = $this->_objectManager->create('Magestore\Affiliateplusprogram\Model\ResourceModel\Account\Collection')
                ->addFieldToFilter('program_id', $model->getId());
            foreach ($joinedAccounts as $joinedAccount) {
                $data['program_account'][] = $joinedAccount->getAccountId();
            }
            $data['program_account'] = array_unique($data['program_account']);
        }
        return array_key_exists('program_account', $data) ? $data['program_account'] : null;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {

            $model = $this->getModel('Magestore\Affiliateplusprogram\Model\Program');
            $data = $this->getData($data);
            if ($storeId = $this->getRequest()->getParam('store', 0)) {
                $model->setStoreId($storeId);
            }
            $id = $this->getRequest()->getParam('program_id');
            if ($id) {
                $model->load($id);
            }
            foreach ($model->getStoreAttributes() as $attribute) {
                $model->setData($attribute . '_default', false);
            }
            $model->addData($data)
                ->setId($id);

            try {
                $now = new \DateTime();
                $model->loadPost($data);
                if ($model->getActionsSerialized() != serialize($model->getActions()->asArray())) {
                    $model->setData('is_process', 0);
                }
                if ($model->getCreatedDate() == NULL) {
                    $model->setCreatedDate($now);
                }
                // calculate number of affiliate account
                $data['program_account'] = $this->getProgramAccount($model, $data);
                if (isset($data['program_account']) && is_array($data['program_account'])) {
                    $model->setData('num_account', count($data['program_account']));
                }
                $model->save();
                // save list of affiliate account
                if (isset($data['program_account']) && is_array($data['program_account'])) {
                    $this->getModel('Magestore\Affiliateplusprogram\Model\Account')
                        ->setProgramId($model->getId())
                        ->setAccountIds($data['program_account'])
                        ->saveAll();
                    $this->getModel('Magestore\Affiliateplusprogram\Model\Joined')->updateJoined($model->getId());

                }

                // save list of category
                if (isset($data['category_ids']) && is_string($data['category_ids'])) {
                    $categoryIds = array_unique(explode(',', $data['category_ids']));
                    if (is_array($categoryIds))
                        $this->getModel('Magestore\Affiliateplusprogram\Model\Category')
                            ->setProgramId($model->getId())
                            ->setCategoryIds($categoryIds)
                            ->setStoreId($storeId)
                            ->saveAll();
                }

                $this->messageManager->addSuccess(__('Program was successfully saved'));
                $this->_objectManager->create('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', [
                            'program_id' => $model->getId(),
                            'store' => $storeId,
                        ]
                    );
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->create('Magento\Backend\Model\Session')->setFormData($data);
                $this->_redirect('*/*/edit', [
                        'program_id' => $this->getRequest()->getParam('program_id'),
                        'store' => $storeId,
                    ]
                );
                return;
            }
        }
        $this->messageManager->addError(__('Unable to find program to save'));
        $this->_redirect('*/*/');

    }
}
