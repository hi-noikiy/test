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
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once ("Mage/Adminhtml/controllers/Catalog/ProductController.php");

class Ktpl_Stockexport_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    
    public function massExportAction()
    { 
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s).'));
            $this->_redirect('*/*/index');
        }
        else {
            //write headers to the csv file
            $content = "sku,price,qty\n";
            try {
                foreach ($productIds as $productId) {
                    $product = Mage::getSingleton('catalog/product')->load($productId);
                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                    $content .= "\"{$product->getSku()}\",\"{$product->getPrice()}\",\"{$stock->getQty()}\",\n";
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/index');
            }
            $this->_prepareDownloadResponse('export.csv', $content, 'text/csv');
        }

    }
    
}