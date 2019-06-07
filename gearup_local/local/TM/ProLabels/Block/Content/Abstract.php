<?php

abstract class TM_ProLabels_Block_Content_Abstract
    extends Mage_Core_Block_Template
{

    protected $mode = '';

    public function getMediaDir()
    {
        return
            rtrim(Mage::getBaseUrl('media'), DS)
            . DS
            . 'prolabel'
            . DS;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function getLabelText($label)
    {
        $product = $this->getProduct();
        if (!$product) {
            return false;
        }
        $helper = Mage::helper('prolabels');
        return $this->escapeHtml(
                $helper->_getText($product, $this->mode, $label)
            );
    }

    public function validateContentLabel($label)
    {
        $helper = Mage::helper('prolabels');
        if ('1' == $label['rules_id']) {
            return $helper->_isOnSale(
                    $this->getProduct(), $this->mode, $label
                );
        } else if ('2' == $label['rules_id']) {
            return $helper->_canShowQuantity(
                    $this->getProduct(), $this->mode, $label
                );
        } else if ('3' == $label['rules_id']) {
            return $helper->checkNewDate($this->getProduct());
        }
    }

    public function renderContentLabel($label, $cssClass = 'tt-gplus') {
        $tooltipText = $this->getLabelText($label);

        $image = sprintf(
            '<img src="%s" alt="%s" />',
            $this->getMediaDir() . $label[$this->mode . '_image'],
            $tooltipText
        );

        $tooltip = '';
        if ($tooltipText != '') {
            $tooltip = sprintf('<span class="tooltip-label" style="display: none;">%s</span>', $tooltipText);
        }

        $aHref = '';
        $aTitle = '';
        if ($label['product_custom_url']) {
            $aHref = sprintf('href="%s" target="_blank"', $label[$this->mode . '_custom_url']);
        }
        if ($tooltipText != '') {
            $aTitle = sprintf('title="%s"', $tooltipText);
        }

        return sprintf(
                '<a class="%5$s" %1$s %2$s>%3$s%4$s</a>',
                $aHref,
                $aTitle,
                $image,
                $tooltip,
                $cssClass
            );
    }

    public function renderMobileLabel($data, $cssClass = '')
    {

        $helper = Mage::helper('prolabels');
        $product = $this->getProduct();
        $mode = $this->getMode();
        $html = '';

        if ($mode == 'category') {
            $html .= $helper->getCategoryProductUrl($product, $mode, $data);
        }
        if ($data['rules_id'] == '2') {
            $out = $helper->_canShowQuantity($product, $mode, $data);
            if ($out == 'out') {
                if ($data[$mode . "_out_stock"] == '1' && !empty($data[$mode . "_out_stock_image"])) {
                    $labelImg = $data[$mode . "_out_stock_image"];
                    $html     .= '<span style="'
                        . $helper->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg);
                }
            } else {
                $labelImg = $data[$mode . "_image"];
                $html .= '<span  style="'
                    . $helper->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg);
            }
        } else {
            $labelImg = $data[$mode . "_image"];
            $html .= '<span style="'
                . $helper->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg);
        }


        if (!$helper->_hasLabelPosition($data[$mode . "_position"])) {

            $html .= $helper->_getTableMargins(
                $data[$mode . "_position"],
                Mage::getBaseDir('media') . '/prolabel/' . $labelImg
            );
        }
        $imgPath = Mage::getBaseDir('media') . '/prolabel/' . $labelImg;
        $onClick = '';
        if ($mode == "category") {
            $separator = "'";
            $onClick = 'onclick="return false;"';
        }
        $background = '';
        if ($labelImg) {
            $background = sprintf(
                'background: url(%s) no-repeat 0 0;',
                Mage::getBaseUrl('media'). 'prolabel/' . $labelImg
            );
        }
        $html .= $data[$mode . '_position_style'].'"
                class = "prolabel-mobile">
            <span class="prolabels-image-mobile" ' . $onClick . ' style="cursor:pointer;'
                . $background
                . $helper->_getTableSize(Mage::getBaseDir('media') . "/prolabel/" . $labelImg).'">' .
            $helper->_getProductUrl($product, $imgPath, $mode, $data) .
            '</span>
            </span>';

        if ($mode == 'category') {
            $html .= '</a>';
        }

        return $html;

    }

    public function getContentLabels()
    {
        $result = array();
        $helper = Mage::helper('prolabels');
        $contentLabels = $helper->getRegistryContentLabels(
                $this->getProduct()->getId(),
                $this->mode
            );

        foreach ($contentLabels as $label) {
            if (Mage::getStoreConfig("prolabels/general/customer_group")) {
                $labelCustomerGroups = unserialize($label['customer_group']);
                $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                if ($labelCustomerGroups) {
                    if (!in_array($roleId, $labelCustomerGroups)) {
                        continue;
                    }
                }
            }
            if (array_key_exists('system_id', $label)) {
                if ($this->validateContentLabel($label)) {
                    if ($helper->checkSystemLabelStore($label['system_id'], $this->mode)) {
                        $result[] = $label;
                    }
                }
            } else {
                if ($helper->checkLabelStore($label['rules_id'], $this->mode)) {
                    $result[] = $label;
                }
            }
        }

        return $result;
    }

    public function getMobileLabels()
    {
        $helper = Mage::helper('prolabels');
        $moveToContent = Mage::getStoreConfig("prolabels/general/mobile");

        if (!$moveToContent || !$helper->isMobileMode()) {
            return array();
        }

        $product = $this->getProduct();
        $mode = $this->getMode();

        $labelsData = $helper->getRegistryLabelsData($product->getId(), $mode);

        if (Mage::getStoreConfig("prolabels/general/priority")) {
            $labelsData = $helper->checkLabelPriority($labelsData, $mode, $product);
        }

        $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        foreach ($labelsData as $key => $data) {

            $customerGroups = unserialize($data['customer_group']);
            if ($customerGroups && !in_array($roleId, $customerGroups)) {
                unset($labelsData[$key]);
                continue;
            }

            if (isset($data['system_id'])) {
                if (!$helper->checkSystemLabelStore($data['system_id'], $mode)) {
                    unset($labelsData[$key]);
                    continue;
                }
            } else {
                if (!$helper->checkLabelStore($data['rules_id'], $mode)) {
                    unset($labelsData[$key]);
                    continue;
                }
            }

            if (empty($data[$mode . '_image'])) {
                if (!$data['rules_id'] == '2'
                    && $helper->_canShowQuantity($product, $mode, $data) != 'out')
                {
                    unset($labelsData[$key]);
                    continue;
                }
            }

            if ($data['rules_id'] == '1' && !$helper->_isOnSale($product, $mode, $data)) {
                unset($labelsData[$key]);
                continue;
            }

            if ($data['rules_id'] == '2') {
                $out = $helper->_canShowQuantity($product, $mode, $data);
                if (!$out) {
                    unset($labelsData[$key]);
                    continue;
                }
            }

            if ($data['rules_id'] == '3' && !$helper->checkNewDate($product)) {
                unset($labelsData[$key]);
                continue;
            }

        }

        return $labelsData;
    }

    public function getTooltipConfig()
    {
        return json_encode(array(
                'background' => Mage::getStoreConfig('prolabels/tooltip/background'),
                'color' => Mage::getStoreConfig('prolabels/tooltip/color')
            ));
    }

}
