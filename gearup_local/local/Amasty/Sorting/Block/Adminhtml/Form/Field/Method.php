<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


class Amasty_Sorting_Block_Adminhtml_Form_Field_Method extends Mage_Core_Block_Html_Select
{
    /** @var array */
    protected $methods;

    /**
     * @return array
     */
    protected function getMethods()
    {
        if (null === $this->methods) {
            $this->methods = Mage::getSingleton('amsorting/catalog_config')->getAttributeUsedForSortByArray();
        }

        return $this->methods;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->getMethods() as $value => $title) {
                $this->addOption($value, $title);
            }
        }

        return parent::_toHtml();
    }
}
