<?php

class Gearup_Blog_PostController extends Mage_Core_Controller_Front_Action {

    public function preDispatch() {
        parent::preDispatch();
        if (!Mage::helper('blog')->getEnabled()) {
            $this->_redirectUrl(Mage::helper('core/url')->getHomeUrl());
        }
    }

    public function commentsAction() {
         $head = $this->getLayout()->getBlock('head');
            if ($head) {
                $head->unsetChild('aw_blog_og');
              
            }
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        /* Load the block belonging to the current step */
        $update->load('gearup_blog_post_comments');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        $this->getResponse()->setBody($output);
        return $output;
    }

}
