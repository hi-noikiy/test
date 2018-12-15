<?php
class EM_Apiios_Model_Api2_Stores_Rest_Abstract extends Mage_Api2_Model_Resource
{
    /**
     * Get store collection as json
     * @return array
     */
    protected function _retrieveCollection(){
        //Mage::getSingleton('core/session', array('name' => 'frontend'))->start();
        $websites = array();
        foreach (Mage::app()->getWebsites() as $website) {
            $groups = array();
			$defaultGroupId = $website->getDefaultGroup()->getId();
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStoreCollection()->addFieldToFilter('is_active',1);
                $storeList = array();
				$defaultStoreId = $group->getDefaultStore()->getId();
                foreach ($stores as $store) {
                    //$store is a store object
					if($store->getId() == $defaultStoreId){
						$storeList[] = array(
							'id'    =>  $store->getId(),
							'code'  =>  $store->getCode(),
							'name'  =>  $store->getName(),
							'default'=>	1
						);
					} else {
						$storeList[] = array(
							'id'    =>  $store->getId(),
							'code'  =>  $store->getCode(),
							'name'  =>  $store->getName()
						);
					}
                }
				if($group->getId() == $defaultGroupId){
					$groups[] = array(
						'id'    =>  $group->getId(),
						'name'  =>  $group->getName(),
						'stores'=>  $storeList,
						'default'=> 1
					);
				} else {
					$groups[] = array(
						'id'    =>  $group->getId(),
						'name'  =>  $group->getName(),
						'stores'=>  $storeList
					);
				}
            }
            $websites[] =  array(
                'id'    =>  $website->getId(),
                'name'  =>  $website->getName(),
                'groups'=>  $groups
            );
        }
        return $websites;
    }

    /**
     * Get currency list. Method : GET, Operation : entity
     */
    protected function _retrieve(){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $method = $this->getRequest()->getParam('method');
        $methodName = 'get'.ucfirst($method);
        $result = $this->$methodName();
        return $result;
    }

    /**
     * Change currency. Method : PUT.
     */
    protected function _update($data){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $this->_getStore()->setCurrentCurrencyCode($data['currency']);
    }

    /**
     * Retrieve currencies array
     * Return array: code => currency name
     * Return empty array if only one currency
     *
     * @return array
     */
    public function getCurrencies()
    {
        $currencies = array();
        $codes = Mage::app()->getStore()->getAvailableCurrencyCodes(true);
        if (is_array($codes) && count($codes) > 1) {
            $rates = Mage::getModel('directory/currency')->getCurrencyRates(
                $this->_getStore()->getBaseCurrency(),
                $codes
            );

            foreach ($codes as $code) {
                if (isset($rates[$code])) {
                    $currencies[$code] = Mage::app()->getLocale()
                        ->getTranslation($code, 'nametocurrency');
                }
            }
        }
        $result = array();
        foreach($currencies as $code => $name){
            $result[] = array(
                'value'  =>  $code,
                'label'  =>  $name
            );
        }
        return array('currencies' => $result,'current'=>Mage::app()->getStore()->getCurrentCurrencyCode());
    }

    /**
     * Get translate text
     *
     * @return array
     */
    public function getText(){
        $helper = Mage::helper('apiios');
        $text = array(
            'text_list' =>  array(
                "title_loading"     => $helper->__("Loading"),
                "title_tab_home"    => $helper->__("Home"),
                "title_tab_shop"    => $helper->__("Shop"),
                "title_tab_cart"    => $helper->__("My Cart"),
                "title_tab_search"  => $helper->__("Search"),
                "title_tab_account" => $helper->__("Account"),
                "title_tab_settings"=> $helper->__("Settings"),
                "title_btn_login"   => $helper->__("Login"),
                "title_btn_logout"  => $helper->__("Log Out"),
                "title_btn_back"    => $helper->__("Back"),
                "title_btn_cancel"  => $helper->__("Cancel"),
                "title_btn_done"    => $helper->__("Done"),
                "title_btn_yes"     => $helper->__("Yes"),
                "title_btn_no"      => $helper->__("No"),
                "title_btn_submit"  => $helper->__("Submit"),
                "title_btn_forgot"  => $helper->__("Forgot Password?"),
                "title_btn_create_acc"=> $helper->__("Create new account"),
                "title_btn_select_options"=> $helper->__("Select Option"),
                "title_btn_buy"     => $helper->__("Add to cart"),
                "title_btn_next"    => $helper->__("Next"),
                "title_btn_prev"    => $helper->__("Previous"),
                "text_all_products" => $helper->__("All Products"),
                "text_recent_products"=> $helper->__("Recent Products"),
                "text_clear_all"    => $helper->__("Clear All"),
                "text_refresh"      => $helper->__("Refresh"),
                "text_in_stock"     => $helper->__("In Stock"),
                "text_out_stock"    => $helper->__("Out of Stock"),
                "text_more"=> $helper->__("More"),
                "text_delete_recent"=> $helper->__("Do you want delete all recent products?"),
                "text_no_items"=> $helper->__("No item available"),
                "text_asc"=> $helper->__("Ascending"),
                "text_des"=> $helper->__("Descending"),
                "text_sort"=> $helper->__("Sort"),
                "text_filter"=> $helper->__("Filter"),
                "text_rate"=> $helper->__("Tap on blank star to rate"),
                "text_logging_out"=> $helper->__("Logging Out…"),
                "text_logging_in"=> $helper->__("Logging In…"),
                "text_feature_products"=> $helper->__("Feature Product"),
                "text_new_products"=> $helper->__("New Product"),
                "text_authenticating"=> $helper->__("Authenticating..."),
                "text_coupon_code"=> $helper->__("Enter your coupon code if you have one"),
                "text_also_interested"=> $helper->__("You may also interested in"),
                "text_create_acc"=> $helper->__("New to Our Store?"),
                "text_from"=> $helper->__("From:"),
                "text_acc_info"=> $helper->__("Account Information"),
                "text_my_orders"=> $helper->__("My Orders"),
                "text_address_book"=> $helper->__("Address Book"),
                "text_new_address"=> $helper->__("New Address"),
                "text_order_status"=> $helper->__("Status"),
                "text_filter_select"=> $helper->__("Select a filter"),
                "text_filter_actived"=> $helper->__("Activated"),
                "text_quanlity"=> $helper->__("Quantity"),
                "text_upsell"=> $helper->__("Up-sell Products"),
                "text_rating_title"=> $helper->__("Rating & Reviews"),
                "text_share_title"=> $helper->__("Share"),
                "text_desc"=> $helper->__("Description"),
                "text_add_info"=> $helper->__("Additional Info"),
                "text_bundel_config"=> $helper->__("Bundle Configure"),
                "text_custom_config"=> $helper->__("Custom Configure"),
                "text_see_all"=> $helper->__("See all"),
                "text_no_larger_image"=> $helper->__("No larger image"),
                "text_order_sku"=> $helper->__("SKU"),
                "text_total"=> $helper->__("Total"),
                "text_date"=> $helper->__("Date"),
                "text_max_char"=> $helper->__("Maximum Character"),
                "text_select_item"=> $helper->__("-Select Item-"),
                "text_select_att"=> $helper->__("Select Attribute to Configure"),
                "text_select_link"=> $helper->__("Select Link Options"),
                "text_downloadable_info"=> $helper->__("Downloadable Information"),
                "text_connection_error"=> $helper->__("Connection error"),
                "text_currency_tittle"=> $helper->__("Currency Setting"),
                "text_paypal_select_ship"=> $helper->__("Select a shipping method..."),
                "text_paypal_review"=> $helper->__("Review your order"),
                "text_paypal_ship_methods"=> $helper->__("Shipping Methods"),
                "text_paypal_result"=> $helper->__("Result"),
                "title_btn_more"=> $helper->__("More"),
                "title_btn_edit"=> $helper->__("Edit"),
                "title_btn_Checkout"=>"Checkout",
                "title_btn_more"=> $helper->__("More"),
                "title_btn_edit"=> $helper->__("Edit"),
                "title_btn_checkout"=> $helper->__("Checkout"),
                "text_btn_select"=> $helper->__("Select"),
                "text_btn_change"=> $helper->__("Change"),
                "title_btn_update_order"=> $helper->__("Update Order"),
				"title_btn_place_order"=> $helper->__("Place Order"),
                "text_subtotal"=> $helper->__("Subtotal"),
                "text_unit_price"=> $helper->__("Unit Price"),
                "title_btn_add_to_wishlist"=> $helper->__("Add to Wish List"),
                "text_my_wishlist"=> $helper->__("My Wish List"),
                "title_btn_delete"=> $helper->__("Delete"),
                "k_text_input_error_message"=> $helper->__("Please re-enter the invalid input\n*is required"),
                "title_btn_apply"=> $helper->__("Apply"),
                "text_excl_tax"=> $helper->__("Excl. Tax"),
                "text_incl_tax"=> $helper->__("Incl. Tax"),
                "text_order_review"=> $helper->__("Order Review"),
                "text_billing_info"=> $helper->__("Billing Info"),
                "text_payment_methods"=> $helper->__("Payment Methods"),
                "text_shipping_info"=> $helper->__("Shipping Info"),
                "text_best_seller"=> $helper->__("Best Seller"),
                "text_most_popular"=> $helper->__("Most Popular"),
                "text_new_products"=> $helper->__("New Products"),
                "text_language"=> $helper->__("Language"),
                "title_btn_login_facebook"=> $helper->__("Facebook Login"),
                "title_btn_login_google"=> $helper->__("Google+ Login"),
                "title_btn_login_linkedin"=> $helper->__("Linkedin Login"),
                "title_btn_login_ossso"=> $helper->__("OSSSO Login"),
                "title_btn_login_others"=> $helper->__("Other Social accounts Login"),
                "title_btn_create_account"=> $helper->__("Create new account"),
                "text_no_account"=> $helper->__("You have no account?"),
                "text_search_products"=> $helper->__("Search entire store here"),
                "title_tab_wishlist"=> $helper->__("Wish List"),
				"text_step"    =>    $helper->__("Step"),
				"text_of"    =>    $helper->__("of"),
				"text_thanks_for_ordering"    =>    $helper->__("Thanks for ordering from OSSSO STORE!"),
				"text_thanks_for_ordering_email"    =>    $helper->__("We have sent the order information to your email address."),
				"text_internet_error"    =>    $helper->__("Please check your Internet connection and try again"),
				"text_try_again"    =>    $helper->__("Try again"),
				"text_write_review"    =>    $helper->__("Write a review"),
				"text_internet_offline"    =>    $helper->__("The Internet connection appears to be offline"),
				"text_network_error"    =>    $helper->__("Network Error"),
				"text_description"    =>    $helper->__("Description"),
				"text_read_more"    =>    $helper->__("Read more"),
				"text_preview"    =>    $helper->__("Preview"),
				"text_up_to"    =>    $helper->__("Up to "),
				"text_character"    =>    $helper->__("character(s)"),
				"title_categories"    =>    $helper->__("Categories"),
				"text_discount_code"    =>    $helper->__("Discount code"),
				"title_btn_continue"    =>    $helper->__("Continue"),
				"text_barcode_instruction_1"    =>    $helper->__("Centre barcode between the arrows"),
				"text_barcode_instruction_2"    =>    $helper->__("Barcode will scan automatically. Try to avoid shadows and glare."),
				"text_thanks_for_ordering"    =>    $helper->__("Thanks for ordering from our STORE!"),
				"title_btn_login_ossso"    =>    $helper->__("Email Login")
            )
        );
        return $text;
    }
}
?>