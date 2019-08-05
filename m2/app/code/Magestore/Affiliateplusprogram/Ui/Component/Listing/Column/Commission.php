<?php

/**
 * Magestore
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

namespace Magestore\Affiliateplusprogram\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ProgramstatusActions
 * @package Magestore\Affiliateplusprogram\Ui\Component\Listing\Column
 */
class Commission extends \Magestore\Affiliateplusprogram\Ui\Component\Listing\Column\AbstractColumn
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * Constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * prepare item.
     *
     * @param array $item
     *
     * @return $this
     */
    protected function _prepareItem(array & $item) {

            if (isset($item[$this->getData('name')]) && $item[$this->getData('name')]) {
                if($item['commission_type'] == 'percentage'){
                    $item[$this->getData('name')] = sprintf("%.2f",$item[$this->getData('name')]).'%';

                } else {
                     $item[$this->getData('name')] = $this->_storeManager->getStore()->getBaseCurrency()->format($item[$this->getData('name')]);
                }
            }
        return $item[$this->getData('name')];
    }
}
