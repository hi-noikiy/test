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

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Edit
 * @package Magestore\Affiliateplusprogram\Controller\Adminhtml\Program
 */
class Edit extends \Magestore\Affiliateplusprogram\Controller\Adminhtml\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        $this->_initCategories();
        $id = $this->getRequest()->getParam('program_id');
        $storeId = $this->getRequest()->getParam('store', 0);
        /** @var \Magestore\Affiliateplusprogram\Model\ResourceModel\Program\Collection $collection */

        $model = $this->getModel('Magestore\Affiliateplusprogram\Model\Program');

        if ($storeId){
            $model->setStoreId($storeId);
        }
        if($id){
            $model->load($id);
        }

        $data = $this->_objectManager->create('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $model->getConditions()->setJsFormObject('affiliateplusprogram_conditions_fieldset');
        $model->getActions()->setJsFormObject('affiliateplusprogram_actions_fieldset');
        $this->_initAction();
        if($id){
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Program "%1"', $model->getName()));
        }else {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Program'));
        }

        $this->_coreRegistry->register('affiliateplusprogram_data', $model);


        $breadcrumb = $id ? __('Edit Program') : __('New Program');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->_view->renderLayout();

    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magestore_Affiliateprogram::program'
        )->_addBreadcrumb(
            __('Programs'),
            __('Programs')
        );
        return $this;
    }

    protected function _initCategories() {
        if (!$this->_coreRegistry->registry('program_categories')){
            $programId = $this->getRequest()->getParam('program_id');
        }
        if ($programId) {
            $categoryCollection = $this->_objectManager->create('Magestore\Affiliateplusprogram\Model\ResourceModel\Category\Collection')
                ->addFieldToFilter('program_id', $programId);
            if ($storeId = $this->getRequest()->getParam('store', 0))
                $categoryCollection->addFieldToFilter('store_id', $storeId);
            $categories = array();
            foreach ($categoryCollection as $category)
                $categories[] = $category->getCategoryId();
            $this->_coreRegistry->register('program_categories', $categories);
        }
    }
}
