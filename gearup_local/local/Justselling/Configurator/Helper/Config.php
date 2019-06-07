<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_Helper_Config extends Mage_Core_Helper_Abstract {

	public function getBlacklistJson($templateid) {
		if ($templateid) {
			$cache_key = "PRODCONF_BLACKLIST_".$templateid;
			if (Mage::helper("configurator")->readFromCache($cache_key)) {
				$blacklist = Mage::helper("configurator")->readFromCache($cache_key);
			} else {
				$blacklist = array();
				$option_ids = array();

				/*
				 * Set Blacklist Mode
				 */
				$template = Mage::getModel("configurator/template")->load($templateid);
				$design = unserialize($template->getDesign());
				$blacklist_mode = 1;
				if (isset($design["blacklist_mode"]))
					$blacklist_mode = $design["blacklist_mode"];
				$blacklist["blacklist_mode"] = $blacklist_mode;

				$blacklistChildrenAuto = 1;
				if (isset($design["blacklist_children_auto"])) {
					$blacklistChildrenAuto = $design["blacklist_children_auto"];
				}
				$blacklist["blacklistChildrenAuto"] = $blacklistChildrenAuto;

				$groupSwitchBeforeValidate = 0;
				if (isset($design["group_switch_before_validate"])) {
					$groupSwitchBeforeValidate = $design["group_switch_before_validate"];
				}
				$blacklist["groupSwitchBeforeValidate"] = $groupSwitchBeforeValidate;

				/*
				 * Get Blacklist values and options
				 */

				$options = Mage::getModel("configurator/option")->getCollection();
				$options->addFieldToFilter("template_id", $templateid);
				/* @var $option Justselling_Configurator_Model_Option */
				foreach ($options as $option) {
					$option_ids[] = $option->getId();
					$values = Mage::getModel("configurator/value")->getCollection();
					$values->addFieldToFilter("option_id",$option->getId());
					/* @var $value Justselling_Configurator_Model_Value */
					foreach ($values as $value) {
						$blacklistitems = Mage::getModel("configurator/blacklist")->getCollection();
						$blacklistitems->addFieldToFilter("option_value_id", $value->getId());
						$blacklist["blacklist_values"][$value->getId()] = array();
						/* @var $blacklistitem Justselling_Configurator_Model_Blacklist */
						foreach ($blacklistitems as $blacklistitem) {
							if ($blacklistitem->getChildOptionId()) {
								$opt = Mage::getModel("configurator/option")->load($blacklistitem->getChildOptionId());
								$blacklist["blacklist_values"][$value->getId()]["blacklist_options"][] = array("id"=>$blacklistitem->getChildOptionId(), "alt_title"=>$opt->getAltTitle(), "title"=>$opt->getTitle());
							} elseif ($blacklistitem->getChildOptionValueId()) {
								$blacklist["blacklist_values"][$value->getId()]["blacklist_values"][] = $blacklistitem->getChildOptionValueId();
							}
						}

						// Delete Array if no blacklist values and/or options were found or add options data to array
						if (sizeof($blacklist["blacklist_values"][$value->getId()]) == 0) {
							unset ($blacklist["blacklist_values"][$value->getId()]);
						} else {
							$blacklist["blacklist_values"][$value->getId()]["option"]["id"] = $option->getId();
							$blacklist["blacklist_values"][$value->getId()]["option"]["alt_title"] = $option->getAltTitle();
							$blacklist["blacklist_values"][$value->getId()]["option"]["title"] = $option->getTitle();
						}
					}
				}
				// Delete Array if no blacklist values and/or options are defined
				if (isset($blacklist["blacklist_values"]) && sizeof($blacklist["blacklist_values"]) == 0) {
					unset($blacklist["blacklist_values"]);
				}

				/*
				* Get Blacklist Tags
				*/

				$blacklisttags = Mage::getModel("configurator/valuetagblacklist")->getCollection();
				/* @var $blacklisttag Justselling_Configurator_Model_Valuetagblacklist */
				foreach ($blacklisttags as $blacklisttag) {
					if (in_array($blacklisttag->getOptionId(), $option_ids)) {
						$blacklist["blacklist_tags"][$blacklisttag->getOptionValueId()][] = $blacklisttag->getTag();
					}
				}


				/*
				 * Get Blacklist expressions
				 */

				$blacklistexpressions = Mage::getModel("configurator/optionblacklist")->getCollection();
				$blacklist["blacklist_expressions"] = array();
				foreach ($blacklistexpressions as $blacklistexpression) {
					if (in_array($blacklistexpression->getOptionId(), $option_ids)) {
						if (!isset($blacklist["blacklist_expressions"][$blacklistexpression->getOptionId()])) {
							$blacklist["blacklist_expressions"][$blacklistexpression->getOptionId()] = array();
						}
						$index = sizeof($blacklist["blacklist_expressions"][$blacklistexpression->getOptionId()]);
						$blacklist["blacklist_expressions"][$blacklistexpression->getOptionId()][$index] = array();
						$blacklist["blacklist_expressions"][$blacklistexpression->getOptionId()][$index]["value"] = $blacklistexpression->getValue();
						$blacklist["blacklist_expressions"][$blacklistexpression->getOptionId()][$index]["operator"] = $blacklistexpression->getOperator();
						$blacklist["blacklist_expressions"][$blacklistexpression->getOptionId()][$index]["value_id"] = $blacklistexpression->getChildOptionValueId();

					}
				}

				/*
				 * Render an array with all options and there values
				 * Render an array with all values and there option-id
				 */
				$options_and_values = array();
				$values_and_option = array();
				$options_defaults = array();
				$options_type = array();
				$values_tags = array();
				$options = Mage::getModel("configurator/option")->getCollection();
				$options->addFieldToFilter("template_id", $templateid);

				foreach ($options as $option) {
					$options_and_values[$option->getId()] = array();
					$options_type[$option->getId()] = $option->getType();
					if ($option->getDefaultValue()) {
						$options_defaults[$option->getId()] = $option->getDefaultValue();
					}
					$values = Mage::getModel("configurator/value")->getCollection();
					$values->addFieldToFilter("option_id", $option->getId());
					foreach ($values as $value) {
						$options_and_values[$option->getId()][] = 0.0 + $value->getId();
						$values_and_option[$value->getId()] = 0.0 + $option->getId();

						$tags = Mage::getModel("configurator/valuetag")->getCollection();
						$tags->addFieldToFilter("option_value_id", $value->getId());
						foreach ($tags as $tag) {
							$values_tags[$tag->getTag()][] = $value->getId();
						}
					}
				}
				$blacklist["options_and_values"] = $options_and_values;
				$blacklist["values_and_option"] = $values_and_option;
				$blacklist["options_defaults"] = $options_defaults;
				$blacklist["options_type"] = $options_type;
				$blacklist["blacklist_tag_values"] = $values_tags;

				/*
				 * Render a list with the children of any option
				 * Add only relevant children vor blacklisting (e.g. no matrixvalue or expression)
				 */
				$option_children = array();
				$options_alt_title = array();
				$options_title = array();
				$options = Mage::getModel("configurator/option")->getCollection();
				$options->addFieldToFilter("template_id", $templateid);
				foreach ($options as $option) {
					$option_id = $option->getId();
					$options_alt_title[$option_id] = $option->getAltTitle();
					$options_title[$option_id] = $option->getTitle();

					$child_options = Mage::getModel("configurator/option")->getCollection();
					$child_options->addFieldToFilter("parent_id", $option->getId());
					foreach ($child_options as $child_option) {
						if (!(in_array($child_option->getType(), array("matrixvalue","expression"))))  {
							$option_children[$option->getId()][] = 0.0 + $child_option->getId();
						}
					}
				}
				$blacklist["option_and_children"] = $option_children;
				$blacklist["option_and_alt_title"] = $options_alt_title;
				$blacklist["option_and_title"] = $options_title;

				$options_combined_image = array();
				foreach ($options as $option) {
					$options_combined_image[$option->getId()] = false;
					if ($template->getCombinedProductImage()) {
						if (in_array($option->getType(), array("selectimage", "overlayimage", "overlayimagecombi", "listimage","radiobuttons","select","selectcombi","listimagecombi"))) {
							$values = Mage::getModel("configurator/value")->getCollection();
							$values->addFieldToFilter("option_id", $option->getId());
							foreach ($values as $value) {
								if ($value->getSku() && $value->getImage()) {
									$options_combined_image[$option->getId()] = true;
								}
							}
						}
						if ($option->getType() == "textimage") {
							$options_combined_image[$option->getId()] = true;
						}
					}
				}
				$blacklist["options_combined_image"] = $options_combined_image;

				$combined_image_configuration = array();
				if ($template->getCombinedProductImage()) {

					$combined_image_configuration['base_image'] = $template->getBaseImage();

					foreach ($options as $option) {
						$combined_image_configuration[$option->getId()] = array();
						if (in_array($option->getType(), array("textimage","selectimage", "overlayimage", "overlayimagecombi", "listimage","radiobuttons","select","selectcombi","listimagecombi"))) {
							$values = Mage::getModel("configurator/value")->getCollection();
							$values->addFieldToFilter("option_id", $option->getId());
							foreach ($values as $value) {
								if ($value->getSku() && $value->getImage()) {
									$combined_image_configuration[$option->getId()][$value->getId()]['thumbnail'] = $value->getThumbnail();
									$combined_image_configuration[$option->getId()][$value->getId()]['thumbnail_size_x'] = $value->getThumbnailSizeX();
									$combined_image_configuration[$option->getId()][$value->getId()]['thumbnail_size_y'] = $value->getThumbnailSizeY();
									$combined_image_configuration[$option->getId()][$value->getId()]['thumbnail_alt'] = $value->getThumbnailAlt();
									$combined_image_configuration[$option->getId()][$value->getId()]['image'] = $value->getImage();
									$combined_image_configuration[$option->getId()][$value->getId()]['image_size_x'] = $value->getImageSizeX();
									$combined_image_configuration[$option->getId()][$value->getId()]['image_size_y'] = $value->getImageSizeY();
									$combined_image_configuration[$option->getId()][$value->getId()]['image_offset_x'] = $value->getImageOffsetX();
									$combined_image_configuration[$option->getId()][$value->getId()]['image_offset_y'] = $value->getImageOffsetY();
									$combined_image_configuration[$option->getId()][$value->getId()]['value'] = $value->getImageOffsetY();
									$combined_image_configuration[$option->getId()]['sort_order'] = $option->getSortOrder();
									$combined_image_configuration[$option->getId()]['sort_order_combiimage'] = $option->getSortOrderCombiimage();
									$combined_image_configuration[$option->getId()]['frontend_type'] = $option->getFrontendType();
									$combined_image_configuration[$option->getId()]['type'] = $option->getType();
									$combined_image_configuration[$option->getId()]['template_id'] = $option->getTemplateId();
									$combined_image_configuration[$option->getId()]['option_id'] = $option->getId();
                                    $optionValueValue = $value->getValue();
									if(isset($optionValueValue) && $this->optionvalueValueIsColor($optionValueValue)){
										$combined_image_configuration[$option->getId()][$value->getId()]['color'] = $optionValueValue;
									}else{
										$combined_image_configuration[$option->getId()][$value->getId()]['color'] = null;
									}
								}
							}
							if ($option->getType() == "textimage") {
								$combined_image_configuration[$option->getId()]['sort_order'] = $option->getSortOrder();
								$combined_image_configuration[$option->getId()]['sort_order_combiimage'] = $option->getSortOrderCombiimage();
								$combined_image_configuration[$option->getId()]['frontend_type'] = $option->getFrontendType();
								$combined_image_configuration[$option->getId()]['type'] = $option->getType();
								$combined_image_configuration[$option->getId()]['template_id'] = $option->getTemplateId();
								$combined_image_configuration[$option->getId()]['option_id'] = $option->getId();
								$combined_image_configuration[$option->getId()]['font'] = $option->getFont();
								$combined_image_configuration[$option->getId()]['font_size'] = $option->getFontSize();
								$combined_image_configuration[$option->getId()]['font_angle'] = $option->getFontAngle();
								$combined_image_configuration[$option->getId()]['font_color'] = $option->getFontColor();
								$combined_image_configuration[$option->getId()]['font_pos_x'] = $option->getFontPosX();
								$combined_image_configuration[$option->getId()]['font_pos_y'] = $option->getFontPosY();
								$combined_image_configuration[$option->getId()]['font_width_x'] = $option->getFontWidthX();
								$combined_image_configuration[$option->getId()]['font_width_y'] = $option->getFontWidthY();
								$combined_image_configuration[$option->getId()]['text_alignment'] = $option->getTextAlignment();
							}
						}
					}
				}
				$blacklist["combined_image_configuration"] = $combined_image_configuration;

				$combined_image_frontend_active = 1;
				if (Mage::getStoreConfig("productconfigurator/general/combinedimage") !== null) {
					$combined_image_frontend_active = Mage::getStoreConfig("productconfigurator/general/combinedimage");
				}
				$blacklist["combined_image_frontend_active"] = $combined_image_frontend_active;

				Mage::helper("configurator")->writeToCache(
					$blacklist,
					$cache_key,
					array("PRODCONF","PRODCONF_TEMPLATE_".$templateid)
				);
			}
			return json_encode($blacklist);
		}
		return false;
	}

    public function optionvalueValueIsColor($value){
        if(preg_match("/^#(?:[0-9a-f]{3}){1,2}$/i", $value)){
            return true;
        }else{
            return false;
        }
    }

}