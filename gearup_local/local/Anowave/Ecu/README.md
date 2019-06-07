GENERAL INFORMATION

We have included this file to allow developers to add/modify the module behaviour without changing the code module (Anowave_Ec). It provides 2 important rewrites

a) Anowave_Ecu_Helper_Data extends Anowave_Ec_Helper_Data
b) Anowave_Ecu_Model_Observer extends Anowave_Ec_Model_Observer

EVENTS

The module hooks to the following events

a) ec_checkout_products_get_after 	- Anowave_Ecu_Model_Observer::getCheckoutProductsAfter()
b) ec_order_products_get_after		- Anowave_Ecu_Model_Observer::getOrderProductsAfter()
c) ec_get_impression_data_after 	- Anowave_Ecu_Model_Observer::getImpressionDataAfter()

If you want to add custom features such as custom dimensions you should modify this module in order keep the code module (Anowave_Ec) updateable in future. 

For more information please contact us through our help desk.