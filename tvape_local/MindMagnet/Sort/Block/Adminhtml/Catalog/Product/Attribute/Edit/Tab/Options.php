<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attribute add/edit form options tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MindMagnet_Sort_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mindmagnetsort/eav/attribute/options.phtml');
    }

    /**
     * Retrieve frontend descriptions of attribute for each store
     *
     * @return array
     */
    public function getDescriptionValues()
    {
        $description = $this->getAttributeObject()->getMmsortDescription();
        if (!$description) {
            return array();
        }
        $values = json_decode($description, true);
        if (!isset($values['description'])) {
            return array();
        }
        $description = $values['description'];
        $stores = $values['stores'];

        $return = array();
        $cnt = count($description);
        for ($i = 0; $i<$cnt; $i++) {
            $return[$stores[$i]] = $description[$i];
        }

        return $return;
    }
}
