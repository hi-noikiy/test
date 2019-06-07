<?php
/**
 * Frontier Force
 */

class FFDX_Shipping_Model_System_Config_Source_Insured 
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'Y', 'label'=> 'Y'),
            array('value' => 'N', 'label'=> 'N'),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'Y' => 'Y',
            'N' => 'N',
        );
    }
}
