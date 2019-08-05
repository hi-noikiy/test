<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Shopping Cart reports admin controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Ktpl\Guestabandoned\Controller\Adminhtml;

/**
 * @api
 * @since 100.0.2
 */
abstract class Guestabandoned extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Add reports and shopping cart breadcrumbs
     *
     * @return $this
     */
    public function _initAction() {
        $this->_view->loadLayout();
        $this->_addBreadcrumb(__('Guestabandoned'), __('Guestabandoned'));
        $this->_addBreadcrumb(__('Guestabandoned Cart'), __('Guestabandoned Cart'));
        return $this;
    }

    /**
     * Determine if action is allowed for reports module
     *
     * @return bool
     */
    protected function _isAllowed() {
        return true;
    }

}
