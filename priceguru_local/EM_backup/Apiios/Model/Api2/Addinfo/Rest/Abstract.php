<?php
class EM_Apiios_Model_Api2_Addinfo_Rest_Abstract extends Mage_Api2_Model_Resource
{
    /**
     * Get additional information collection as json
     * @return array
     */
    protected function _retrieveCollection(){
        $result = array(
			array(
				"name" => "Function 1",
				"price" => "$99",
				"link" => "www.google.com"
			),
			array(
				"name" => "Function 2",
				"price" => "$99",
				"link" => "www.google.com"
			),
			array(
				"name" => "Function 3",
				"price" => "$99",
				"link" => "www.google.com"
			)
		);
        return $result;
    }
	
	protected function _retrieve(){
		return array(
			"contact_name" => "PHUC TRAN",
			"contact_email" => "phuc.tran@codespot.vn",
			"external_functions_url" => "http://www.google.com",
			"latest_version" => "2.1",
			"update_url" => "http://www.emthemes.com/commercial-magento-extensions/emobicart.html"
		);
	}
}
?>