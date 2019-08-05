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
namespace Magestore\Affiliateplus\Block;

/**
 * @category Magestore
 * @package  Magestore_Affiliateplus
 * @module   Affiliateplus
 * @author   Magestore Developer
 */
class Sales extends AbstractTemplate
{
    /**
     * @var array
     */
    protected $_transBlocksLabel = [];
    /**
     * @var null
     */
    protected $_activeTransBlock = null;

    /**
     * @param $name
     * @param $label
     * @param $link
     * @param $block
     * @return $this
     */
    public function addTransactionBlock($name,$label,$link,$block){
        $this->_transBlocksLabel[$name] =[
            'label'	=> $label,
            'link'	=> $this->getUrl($link)
        ];
        $this->setChild($name,$block);
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function activeTransactionBlock($name){
        $this->_activeTransBlock = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionHtml(){
        return $this->getChildHtml($this->_activeTransBlock);
    }

    /**
     * @return array|bool
     */
    public function getCommissionTabs(){
        if (count($this->_transBlocksLabel) > 1)
            return $this->_transBlocksLabel;
        return false;
    }

    /**
     * @return null
     */
    public function getActiveTab(){
        return $this->_activeTransBlock;
    }
    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}
