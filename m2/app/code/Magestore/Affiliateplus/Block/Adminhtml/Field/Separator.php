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
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Adminhtml\Field;

class Separator extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {


        $id = $element->getHtmlId();
        $html  = '<tr id="row_' . $id . '">'
            . '<td class="label" colspan="1">';
        $marginTop = $element->getComment() ? $element->getComment() : '0px';
        $html .= '<div class="aff-separator-configuration-content">';
        $html .= $element->getLabel();
        $html .= '</div></td>';
        $html .= '<td></td><td></td><td></td></tr>';

        return $html;
    }
}