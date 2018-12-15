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



class Mirasvit_Helpdesk_SatisfactionController extends Mage_Core_Controller_Front_Action
{

    /**
     * @return void
     */
    public function formAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function rateAction()
    {
        $rate = $this->getRequest()->getParam('rate');
        $uid = $this->getRequest()->getParam('message_owner');
        if ($uid == "") { //support of old URL format
            $uid = $this->getRequest()->getParam('uid');
        }
        if ($satisfaction = Mage::helper('helpdesk/satisfaction')->addRate($uid, $rate)) {
            if ($this->getRequest()->isAjax()) {
                echo 'success';

                return;
            } else {
                $this->_redirect('helpdesk/satisfaction/form', array(
                    'message_owner' => $uid,
                    'satisfaction' => $satisfaction->getId(),
                ));
            }
        } else {
            echo "Your IP is not allowed for submitting rates";
        }
    }

    /**
     * @return void
     */
    public function postAction()
    {
        $uid = $this->getRequest()->getParam('message_owner');

        $comment = array();

        foreach ($this->getRequest()->getParams() as $key => $value) {
            if ($key != 'message_owner' && $key != 'satisfaction') {
                $comment[] = ucfirst($key).': '.$value;
            }
        }
        if (count($comment) > 1) {
            $comment = implode(PHP_EOL, $comment);
        } else {
            $comment = $this->getRequest()->getParam('comment');
        }

        if ($comment) {
            Mage::helper('helpdesk/satisfaction')->addComment($uid, $comment);
        }

        $this->loadLayout();
        $this->renderLayout();
    }
}
