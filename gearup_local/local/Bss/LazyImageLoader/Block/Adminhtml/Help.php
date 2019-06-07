<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_LazyImageLoad
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
class Bss_LazyImageLoader_Block_Adminhtml_Help extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<tr id="row_lazyimageloader_general_help">
        <td class="label">
        <label for="lazyimageloader_general_help"> Help exclude lazy images</label>
        </td>
        <td class="value">
            <p>- Add attribute <span style="font-weight:bold;color:red">notlazy</span> after <span style="font-weight:bold;color:red">src</span> attribute to &lt;img&gt; for prevent lazy load.</p>
            <p>- Example:</p>
            <i>From: &lt;img src="bss.png" alt="Bss"&gt;</i><br />
            <i>To: &lt;img src="bss.png" notlazy alt="Bss"&gt;</i>
        </td>
    </tr>';

    return $html;
}
}