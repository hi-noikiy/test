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
 
class Gearup_Competera_Model_Resource_Pricechangelog extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('competera/pricechangelog', 'pricechange_id');
    }
}