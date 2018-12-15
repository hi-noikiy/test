<?php
class EM_Apiios_Model_Api2_Productsinfo_Options_Text extends EM_Apiios_Model_Api2_Productsinfo_Options_Abstract
{
    /**
     * Returns default value to show in text input
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
    }
}