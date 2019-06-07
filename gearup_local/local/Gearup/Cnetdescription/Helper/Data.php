<?php

/**
 * Helper
 */
class Gearup_Cnetdescription_Helper_Data extends Mage_Core_Helper_Abstract 
{
	public function checkFileType($type) {
        $allowedTypes = array(
            'text/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-office',
            'application/octet-stream',
            'application/wps-office.xls',
            'application/wps-office.xlsx'
        );
        if (!in_array($type, $allowedTypes)) {
            return false;
        } else {
            return true;
        }
    }
}