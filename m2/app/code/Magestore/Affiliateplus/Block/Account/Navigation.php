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
namespace Magestore\Affiliateplus\Block\Account;

/**
 * Class Navigation
 * @package Magestore\Affiliateplus\Block\Account
 */
class Navigation extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * @var array
     */
    protected $_links = [];
    /**
     * @var string
     */
    protected $_navigation_title = '';
    /**
     * @var bool
     */
    protected $_activeLink = false;
    /**
     * @param $title
     * @return $this
     */
    public function setNavigationTitle($title){
        $this->_navigation_title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getNavigationTitle(){
        return $this->_navigation_title;
    }

    /**
     * @param $name
     * @param $path
     * @param $label
     * @param bool|false $disabled
     * @param int $order
     * @param array $urlParams
     * @return $this
     */
    public function addLink($name, $path, $label, $disabled = false, $order = 0, $urlParams=array())
    {
        if (isset($this->_links[$order])) $order++;

        $link = new \Magento\Framework\DataObject(
            [
                'name' 		=> $name,
                'path' 		=> $path,
                'label' 	=> $label,
                'disabled'	=> $disabled,
                'order'		=> $order,
                'url' 		=> $this->getUrl($path, $urlParams),
             ]
        );

        $this->_eventManager->dispatch('affiliateplus_account_navigation_add_link',[
            'block'		=> $this,
            'link'		=> $link,
        ]
    );

        $this->_links[$order] = $link;
        return $this;
    }

    /**
     * @return array
     */
    public function getLinks(){
        $links = new \Magento\Framework\DataObject([
            'links'	=> $this->_links,
        ]
        );

        $this->_eventManager->dispatch('affiliateplus_account_navigation_get_links',[
            'block'		=> $this,
            'links_obj'	=> $links,
        ]
        );

        $this->_links = $links->getLinks();

        ksort($this->_links);

        return $this->_links;
    }

    /**
     * @param $link
     * @return bool
     */
    public function isActive($link){
        $this->_activeLink = $this->_requestInterface->getFullActionName("/");
        if ($this->_completePath($link->getPath()) == $this->_activeLink) {
            return true;
        }
        if (in_array($this->_activeLink,[
                'affiliatepluslevel/index/listTierTransaction',
                'affiliatepluspayperlead/index/listleadcommission'
            ]) && $this->_completePath($link->getPath()) == 'affiliateplus/index/listTransaction')
            return true;
        return false;
    }

    /**
     * @param $path
     * @return string
     */
    protected function _completePath($path)
    {
        $path = rtrim($path, '/');
        switch (sizeof(explode('/', $path))) {
            case 1:
                $path .= '/index';
            // no break

            case 2:
                $path .= '/index';
        }
        return $path;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}