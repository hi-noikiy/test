<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Mobile3
 * @version    3.0.6
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


abstract class AW_Mobile3_Block_Decorator extends Mage_Core_Block_Template
{
    protected $block = null;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->_createBlock();
    }

    protected abstract function _createBlock();

    public function __get($name)
    {
        if ($this->block) {
            return $this->block->{$name};
        } else {
            return null;
        }
    }

    public function __set($name, $value)
    {
        if ($this->block) {
            $this->block->{$name} = $value;
        }
    }

    public function __call($method, $arguments = array())
    {
        if ($this->block && (method_exists($this->block, $method))) {
            return call_user_func_array(array($this->block, $method), $arguments);
        } else {
            return null;
        }
    }
}