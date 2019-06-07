<?php
/**
 * Frontier Force
 */

class FFDX_Shipping_Model_System_Config_Source_Codestation 
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'KWI', 'label'=> 'KWI'),
            array('value' => 'DXB', 'label'=> 'DXB'),
            array('value' => 'BHR', 'label'=> 'BHR'),            
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
            'KWI' => 'KWI',
            'DXB' => 'DXB',
            'BHR' => 'BHR'
        );
    }
}
