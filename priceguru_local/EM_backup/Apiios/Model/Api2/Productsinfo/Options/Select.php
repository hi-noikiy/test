<?php
class EM_Apiios_Model_Api2_Productsinfo_Options_Select extends EM_Apiios_Model_Api2_Productsinfo_Options_Abstract
{
    public function getOptionArray(){
        $result = $this->getOption()->getData();
        $items = array();
        foreach ($this->getOption()->getValues() as $_value) {
            $tmp = $_value->getData();
            $tmp['prices'] = $this->_formatPrice(array(
                'is_percent'    => ($_value->getPriceType() == 'percent'),
                'pricing_value' => $_value->getPrice(($_value->getPriceType() == 'percent'))
            ), false);
            $items[] = $tmp;
        }
        $result['attr_item'] = $items;
        return $result;
    }
}