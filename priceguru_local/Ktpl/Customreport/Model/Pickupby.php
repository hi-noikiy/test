<?php

class Ktpl_Customreport_Model_Pickupby extends Varien_Object
{
    

    static public function getOptionArray()
    {
        return array(
            1   => Mage::helper('customreport')->__('ALVIN'),
            2   => Mage::helper('customreport')->__('DANNY'),
            3   => Mage::helper('customreport')->__('ERIC'),
            4   => Mage::helper('customreport')->__('KESHAV'),
            5   => Mage::helper('customreport')->__('KEVIN'),
            6   => Mage::helper('customreport')->__('NALEEN'),
            7   => Mage::helper('customreport')->__('SEBASTIEN'),
            8   => Mage::helper('customreport')->__('STAN'),
            9   => Mage::helper('customreport')->__('STEPHEN'),
            10  => Mage::helper('customreport')->__('THIBAULT'),
            11  => Mage::helper('customreport')->__('VINCENT'),
            12  => Mage::helper('customreport')->__('YANNICK'),
            13  => Mage::helper('customreport')->__('DELIVERY'),
            14  => Mage::helper('customreport')->__('SUPPLIER'),
            15  => Mage::helper('customreport')->__('PG OFFICE'),
            16  => Mage::helper('customreport')->__('Ashley'),
            17  => Mage::helper('customreport')->__('Luvin'),
            18  => Mage::helper('customreport')->__('Girish'),
            
        );
    }
}