<?php
/**
* Date picker block for system config
*
*/

namespace Ktpl\Guestabandoned\Block\Adminhtml\System\Config;

class Date
{
    protected function _getElementHtml(\Varien\Data\Form\Element\AbstractElement $element)
    {
        $date = new Varien_Data_Form_Element_Date;
        $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $data = array(
            'name'      => $element->getName(),
            'html_id'   => $element->getId(),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
        );
        $date->setData($data);
        $date->setValue($element->getValue(), $format);
        $date->setFormat(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
        $date->setClass($element->getFieldConfig()->validate->asArray());
        $date->setForm($element->getForm());

        return $date->getElementHtml();
    }
}