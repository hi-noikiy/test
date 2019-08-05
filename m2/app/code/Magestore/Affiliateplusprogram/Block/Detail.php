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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Block;

/**
 * Class Detail
 * @package Magestore\Affiliateplusprogram\Block
 */
class Detail extends AbstractProgram
{
    /**
     * @return mixed
     */
    public function getProgram(){
        if (!$this->hasData('program')){
            $this->setData('program',$this->_programFactory->create()
                ->setStoreId($this->_storeManager->getStore()->getId())
                ->load($this->getRequest()->getParam('id')));
        }
        return $this->getData('program');
    }

    /**
     * @return mixed
     */
    public function isJoined(){
        if (!$this->hasData('is_joined')){
            $this->setData('is_joined',in_array($this->getProgram()->getId(),$this->_helper->getJoinedProgramIds()));
        }
        return $this->getData('is_joined');
    }

    /**
     * @param $row
     * @return string
     */
    public function getProductName($row){
        return sprintf('<a href="%1" title="%2">%3</a>'
            ,$this->isJoined() ? $this->_urlHelper->addAccToUrl($row->getProductUrl()) : $row->getProductUrl()
            ,__('View Product Detail')
            ,$row->getName()
        );
    }
}
