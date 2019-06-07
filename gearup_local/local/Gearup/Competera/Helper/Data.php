<?php
/**
 * Magento
 *
 * DISCLAIMER
 *
 * Competera API to compare / update price
 *
 * @category   Gearup
 * @package    Gearup_Competera
 * @author     Gunjan <gunjan@krishtechnolabs.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Gearup_Competera_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $priceArr = array();
    
    public function getCompeteraPrice() {
        if(count($this->priceArr) > 0)
        {
            return $this->priceArr;
        } else {
            // Fetch Competera prices
            $competeraRequestUrl = 'https://dashboard.competera.net/api/v1/campaigns/739/reports/comparison_by_products/?username=michal@gear-up.me&api_key=16ce043c9b51908ad695b01cb0515940f5004bb0&limit=0';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $competeraRequestUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $data = curl_exec($ch);
            curl_close($ch);
            $jsonData = Mage::helper('core')->jsonDecode($data);
            foreach ($jsonData['objects'] as $jsonArr) {
                if($jsonArr['product']['opportunity']['suggested_price'] != '') {
                    $this->priceArr[$jsonArr['product']['sku']] = $jsonArr['product']['opportunity']['suggested_price'];
                }
            }
        }
        return $this->priceArr;
    }
}