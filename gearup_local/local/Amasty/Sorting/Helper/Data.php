<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Helper_Data extends Mage_Core_Helper_Abstract 
{
    private $methodCodes = null;

    public function getMethods()
    {
//        $isSearch = in_array(Mage::app()->getRequest()->getModuleName(), array('sqli_singlesearchresult', 'catalogsearch')); 
//        if ($isSearch)
//            return array();
        
        
        // class names. order defines the position in the dropdown
        $methods = array(
            'new',    
            'saving',
            'bestselling',    
            'mostviewed',    
            'toprated',    
            'commented',    
            'wished',
            'qty',
            'profit',
            'revenue',
            'revenueview',
            'orderview',
        ); 

        return $methods;
    }

    public function getMethodModels()
    {
        if ($this->methodCodes === null) {
            $this->methodCodes = array();
            foreach ($this->getMethods() as $className) {
                $method = Mage::getSingleton('amsorting/method_' . $className);
                $this->methodCodes[$method->getCode()] = $method;
            }
        }

        return $this->methodCodes;
    }

    /**
     * @param string $string
     *
     * @return array|null
     */
    public function unserialize($string)
    {
        if (!@class_exists('Amasty_Base_Helper_String')) {
            $message = $this->getUnserializeError();
            Mage::logException(new Exception($message));
            if (Mage::app()->getStore()->isAdmin()) {
                Mage::helper('ambase/utils')->_exit($message);
            } else {
                Mage::throwException($this->__('Sorry, something went wrong. Please contact us or try again later.'));
            }
        }

        return \Amasty_Base_Helper_String::unserialize($string);
    }

    /**
     * @return string
     */
    public function getUnserializeError()
    {
        return 'If there is the following text it means that Amasty_Base is not updated to the latest 
                             version.<p>In order to fix the error, please, download and install the latest version of 
                             the Amasty_Base, which is included in all our extensions.
                        <p>If some assistance is needed, please submit a support ticket with us at: '
            . '<a href="https://amasty.com/contacts/" target="_blank">https://amasty.com/contacts/</a>';
    }
}
