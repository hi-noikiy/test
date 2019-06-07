<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



/**
 * Use if reports/product_viewed block added in template
 * For example <?php echo $this->getLayout()->createBlock("reports/product_viewed")
 * ->setTemplate("reports/product_viewed.phtml")->toHtml();
 *
 * @return bool
 */
class Mirasvit_Fpc_Model_Container_PhtmlblockProductViewed extends Mirasvit_Fpc_Model_Container_Abstract
{
    public function applyToContent(&$content, $withoutBlockUpdate = false)
    {
        if (!$this->_definition['replacer_tag_begin']
            || !$this->_definition['template']
            || ($this->_definition['replacer_tag_begin']
                && strpos($content, $this->_definition['replacer_tag_begin']) === false)) {
                    return true;
        }

        $startTime = microtime(true);
        $definitionHash = $this->_definition['block'] . '_' .  $this->_definition['template'];
        Mage::helper('fpc/debug')->startTimer('FPC_BLOCK_' . $definitionHash);

        $pattern = '/'.preg_quote($this->_definition['replacer_tag_begin'], '/').'(.*?)'.preg_quote($this->_definition['replacer_tag_end'], '/').'/ims';
        $html = $this->_renderBlock();

        if ($html !== false) {
            ini_set('pcre.backtrack_limit', 100000000);
            Mage::helper('fpc/debug')->appendDebugInformationToBlock($html, $this, 0, $startTime);
            $content = preg_replace($pattern, str_replace('$', '\\$', $html), $content, 1);
            Mage::helper('fpc/debug')->stopTimer('FPC_BLOCK_' . $definitionHash);

            return true;
        }
        Mage::helper('fpc/debug')->stopTimer('FPC_BLOCK_' . $definitionHash);

        return false;
    }

    /**
     * Render block content.
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $html = '';
        if ($block = Mage::app()->getLayout()->createBlock($this->_definition['block'])) {
            $html = $block->setTemplate($this->_definition['template'])->toHtml();
            $this->applyProductWidgetViewed();
        }

        return $html;
    }
    /**
     * Apply product_widget_viewed data (if cms block use the wiget).
     * If you want don't show current product need register current_product in Processor.php
     *
     * @return bool
     */
    protected function applyProductWidgetViewed()
    {
        if (Mage::registry('current_product_id')) {
            Mage::getSingleton('log/visitor')->saveByRequest(false);
            Mage::getModel('reports/product_index_viewed')
                ->setProductId(Mage::registry('current_product_id'))
                ->save()
                ->calculate();
        }

        return true;
    }
}
