<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Controller\Adminhtml\Manifest;



class Grid extends \CollinsHarper\CanadaPost\Controller\Adminhtml\AbstractController
{

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::manifests');
    }

    /**
     * Render AJAX-grid only
     *
     * @return void
     */
    public function execute()
    {
        $this->chLogger->info(__METHOD__ . __LINE__);

        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

}