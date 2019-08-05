<?php

namespace ShippyPro\ShippyPro\Model\Config\Source;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Help extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        $html .= '<a href="' . $this->getBaseUrl() . 'shippypro/index/help" target="blank"><button type="button" class="action-default scalable primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"><span class="ui-button-text"><span>See the guide</span></span></button></a>';
        return $html;
    }
}