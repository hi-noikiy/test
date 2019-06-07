<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (C) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/

class Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Design extends Mage_Adminhtml_Block_Widget_Form
{

	protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);

		/* Additional Information */
		$fieldset = $form->addFieldset("template_design", array(
				"legend" => Mage::helper('configurator')->__('Additional Information')
		));
		
		$fieldset->addField("more_info_design","select",array(
				"label" => Mage::helper('configurator')->__('Display'),
				"name" => "more_info_design",
				"options" => array("fade_in"=>Mage::helper('configurator')->__('Fade In'),"tooltip"=>Mage::helper('configurator')->__('Info Icon and Tooltip'))
		));

        /* Blacklist */
        $fieldset = $form->addFieldset("blacklist", array(
            "legend" => Mage::helper('configurator')->__('Blacklisting')
        ));

        $fieldset->addField("blacklist_mode","select",array(
            "label" => Mage::helper('configurator')->__('Mode'),
            "name" => "blacklist_mode",
            "options" => array("1"=>Mage::helper('configurator')->__('Hide values on blacklist'),"2"=>Mage::helper('configurator')->__('Disable values on blacklist'))
        ));

        $fieldset->addField("blacklist_children_auto","select",array(
            "label" => Mage::helper('configurator')->__('Add children to blacklist'),
            "name" => "blacklist_children_auto",
            "options" => array("1"=>Mage::helper('configurator')->__('Yes'),"2"=>Mage::helper('configurator')->__('No'))
        ));

        $fieldset->addField("blacklist_text_display","select",array(
            "label" => Mage::helper('configurator')->__('Show blacklisting text on option value'),
            "name" => "blacklist_text_display",
            "options" => array(0=>Mage::helper('configurator')->__('No'), 1=>Mage::helper('configurator')->__('Yes'))
        ));

		/* Groups */
		$fieldset = $form->addFieldset("groups", array(
				"legend" => Mage::helper('configurator')->__('Groups')
		));
		$fieldset->addField("group_layout","select",array(
				"label" => Mage::helper('configurator')->__('Layout'),
				"name" => "group_layout",
				"options" => array(
						0=>Mage::helper('configurator')->__('hide'),
						Justselling_Configurator_Block_Renderer::GROUP_VERTICAL_LAYOUT=>Mage::helper('configurator')->__('vertical'),
						Justselling_Configurator_Block_Renderer::GROUP_HORIZONTAL_LAYOUT=>Mage::helper('configurator')->__('horizontal'),
						Justselling_Configurator_Block_Renderer::GROUP_HORIZONTAL_WIZARD_LAYOUT=>Mage::helper('configurator')->__('horizontal Wizard')
				)
		));
		$fieldset->addField("group_enumerate","select",array(
				"label" => Mage::helper('configurator')->__('Enumerate'),
				"name" => "group_enumerate",
				"options" => array(1=>Mage::helper('configurator')->__('Yes'),0=>Mage::helper('configurator')->__('No'))
		));
        $fieldset->addField("group_switch_before_validate","select",array(
            "label" => Mage::helper('configurator')->__('Switch groups without validate'),
            "name" => "group_switch_before_validate",
            "options" => array(1=>Mage::helper('configurator')->__('Yes'),0=>Mage::helper('configurator')->__('No'))
        ));
		
		/* Prices */
		$fieldset = $form->addFieldset("prices", array(
				"legend" => Mage::helper('configurator')->__('Prices')
		));
		
		$fieldset->addField("option_value_price","select",array(
				"label" => Mage::helper('configurator')->__('Presentation of option value price'),
				"name" => "option_value_price",
				"options" => array(0=>Mage::helper('configurator')->__('absolute price'),1=>Mage::helper('configurator')->__('relative price change'),2=>Mage::helper('configurator')->__("don't show price"))
		));
		
		$fieldset->addField("option_value_price_zero","select",array(
				"label" => Mage::helper('configurator')->__('Show option value price 0'),
				"name" => "option_value_price_zero",
				"options" => array(1=>Mage::helper('configurator')->__('Yes'),0=>Mage::helper('configurator')->__('No'))
		));

		/* Combined Product Image */
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) {
			$fieldset = $form->addFieldset("product_image", array(
					"legend" => Mage::helper('configurator')->__('Combined Product Image')
			));
		
			$fieldset->addField("combined_product_image","select",array(
					"label" => Mage::helper('configurator')->__('Activate Combined Product Image'),
					"name" => "combined_product_image",
					"options" => array(1=>Mage::helper('configurator')->__('Yes'),0=>Mage::helper('configurator')->__('No'))
			));

			$fieldset->addType('baseimage', 'Justselling_Configurator_Block_Adminhtml_Form_Renderer_Fieldset_Templateimage');
			$fieldset->addField('base_image', 'baseimage', array(
				"label"     => Mage::helper('configurator')->__('Base-Image'),
				"name"      => "base_image",
				"type"      => "baseimage",
			));
		
			$fieldset->addField("jpeg_quality","select",array(
					"label" => Mage::helper('configurator')->__('JPEG Quality (%)'),
					"name" => "jpeg_quality",
					"options" => array(50=>"50",60=>"60",70=>"70",80=>"80",90=>"90",100=>"100")
			));

            $fieldset->addField("combined_adapt_size","select",array(
                "label" => Mage::helper('configurator')->__('Adapt size of product image'),
                "name" => "combined_adapt_size",
                "options" => array(1=>Mage::helper('configurator')->__('Yes'),0=>Mage::helper('configurator')->__('No'))
            ));

            $fieldset->addField("combined_adapt_factor","text",array(
                "label" => Mage::helper('configurator')->__('Adapt size calculation factor'),
                "name" => "combined_adapt_factor"
            ));

            $fieldset->addField("font_adapt_factor","text",array(
                "label" => Mage::helper('configurator')->__('Adapt font-size calculation factor'),
                "name" => "font_adapt_factor"
            ));
		
			$fieldset->addField('clear_cache_button', 'note', array(
					'text' => $this->getLayout()->createBlock('adminhtml/widget_button')
					->setData(array(
							'label'     => Mage::helper('configurator')->__('Clear Cache'),
							'id'   		=> 'configurator_cache_clear',
							'class'     => 'delete',
					))->toHtml(),
					'no_span'   => true,
					'label' => Mage::helper('configurator')->__('Clear Cache')
			)
			);
		}

        /* Text2Image */
        if (in_array(Mage::getSingleton('core/session')->getEdition(),array("U"))) {
            $fieldset = $form->addFieldset("text2image", array(
                "legend" => Mage::helper('configurator')->__('Text2Image')
            ));
            $fieldset->addField("text2image_singleline","select",array(
                "label" => Mage::helper('configurator')->__('Only show single line instead of multiline textarea'),
                "name" => "text2image_singleline",
                "options" => array(1=>Mage::helper('configurator')->__('Yes'),0=>Mage::helper('configurator')->__('No'))
            ));
        }

		if (Mage::getSingleton("adminhtml/session")->getConfiguratorData()) {
            $data = Mage::getSingleton("adminhtml/session")->getConfiguratorData();
			$form->setValues($data);
			Mage::getSingleton("adminhtml/session")->setConfiguratorData(null);
		} elseif ( Mage::registry("configurator_data") ) {
            $data = Mage::registry("configurator_data")->getData();
			$form->setValues( $data );
		}
		
		return parent::_prepareForm();
    }

    protected function _afterToHtml($html) {
        $block = Mage::helper('configurator')->getHelpIqLink(
            $this,
            "helpiq-lightbox",
            Mage::helper('configurator')->__('design'),
            Mage::helper('configurator')->__('Design')
        );
        return  $block.$html;
    }
}