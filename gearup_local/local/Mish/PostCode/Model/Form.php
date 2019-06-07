<?php

class Mish_PostCode_Model_Form extends Mage_Customer_Model_Form
{
    protected function _isAttributeOmitted($attribute) 
    {
        $value = parent::_isAttributeOmitted($attribute);
        
        if ($value) {
            return true;
        }
        
        if ($attribute->getAttributeCode() == 'postcode') {
            return true;
        }
        
        return false;
    }

}