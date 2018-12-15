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
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



/** @noinspection PhpIncludeInspection */
require Mage::getBaseDir('app').'/code/core/Mage/Contacts/controllers/IndexController.php';
class Mirasvit_Helpdesk_ContactsController extends Mage_Contacts_IndexController
{
    public function indexAction()
    {
        if (Mage::getSingleton('helpdesk/config')->getGeneralContactUsIsActive()) {
            if (Mage::getSingleton('customer/session')->getCustomer()->getId()) {
                $this->_redirect('helpdesk/ticket');

                return;
            }
        }
        // $this->loadLayout();
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();
        $update->addHandle('contacts_index_index');
        $this->loadLayoutUpdates();
        $this->generateLayoutXml();
        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        $this->getLayout()->getBlock('contactForm')
            ->setFormAction(Mage::getUrl('*/*/post'));

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    public function kbresultAction()
    {
        $this->loadLayout();

        $q = $this->getRequest()->getParam('s');
        Mage::register('search_query', $q);
        $collection = $this->getArticleCollection($q);
        if ($collection->count()) {
            Mage::register('search_result', $collection);
        }

        $block = $this->getLayout()->createBlock('helpdesk/contacts_form')
            ->setTemplate('mst_helpdesk/contacts/kb_result.phtml');

        echo $block->toHtml();
    }

    public function getArticleCollection($q)
    {
        $collection = Mage::getModel('kb/article')->getCollection()
            ->addFieldToFilter('main_table.is_active', true)
            ->addStoreIdFilter(Mage::app()->getStore()->getId())
        ;
        Mage::helper('kb')->addSearchFilter($collection, $q);
        $collection->setPageSize(4);

        return $collection;
    }

}
