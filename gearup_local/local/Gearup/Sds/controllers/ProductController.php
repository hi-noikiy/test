<?php

require_once 'Mage/Sendfriend/controllers/ProductController.php';

class Gearup_Sds_ProductController extends Mage_Sendfriend_ProductController {

    public function awsendAction()
    {
        $model      = $this->_initSendToFriendModel();
        $awpost = Mage::app()->getRequest()->getParam('awpost');

        if (!$awpost) {
            $this->_forward('noRoute');
            return;
        }

        if ($model->getMaxSendsToFriend() && $model->isExceedLimit()) {
            Mage::getSingleton('catalog/session')->addNotice(
                $this->__('The messages cannot be sent more than %d times in an hour', $model->getMaxSendsToFriend())
            );
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $data = Mage::getSingleton('catalog/session')->getSendfriendFormData();
        if ($data) {
            Mage::getSingleton('catalog/session')->setSendfriendFormData(true);
            $block = $this->getLayout()->getBlock('sendfriend.send');
            if ($block) {
                $block->setFormData($data);
            }
        }

        $this->renderLayout();
    }
}