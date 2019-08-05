# Magento 2 ShippyPro Shipping Estimate


## Prerequisites

Before installing the ShippyPro module you should setup a
test environment as you will need to put the module in front-end which will certainly
take a while for instalation, configuring and testing. If you directly roll out this
solution to your production server you might experience issues that could
affect your normal business.

Ensure that your Magento2 store is running without any
problems in your environment as debugging Magento2 issues with ShippyPro in front-end
might be difficult.

In order the module to work you will need to enable the following cronjobs: (skip this if your magento main cronjobs are already enabled):

* * * * * /usr/bin/php /<path_to_magento_store>/www/bin/magento cron:run > /dev/null 2>&1
* * * * * /usr/bin/php /<path_to_magento_store>/www/update/cron.php > /dev/null 2>&1
* * * * * /usr/bin/php /<path_to_magento_store>/www/bin/magento setup:cron:run > /dev/null 2>&1



## Installation (from zip file)

1. Decompress the module on your desktop.
2. Log in to your Magento store server as the Magento filesystem owner and navigate to the Magento Home directory.
3. Create a directory `<path_to_magento_store>/app/code/ShippyPro/ShippyPro/` and change directory to the new directory.
4. Move the all module files to `<path_to_magento_store>/app/code/ShippyPro/ShippyPro/`.
5. At this point, it is possible to install with either the:
   5.1. Web Setup Wizard's Component Manager (only Magento 2.0.x versions, for 2.1.x use command line)
      5.1.1. To install in the Web Setup Wizard. Open a browser and log in to the Magento 
             admin section with administrative privileges.
      5.1.2. Navigate to 'System > Web Setup Wizard'.
      5.1.3. Click 'Component Manager' scroll down and locate 'ShippyPro_ShippyPro'. Click enable on the actions.
      5.1.4. Follow the on screen instructions ensuring to create backups.

   5.2. Command line
      5.2.1. To enable the module on the command line change directory to the Magento
       Home directory. Ensure you are logged in as the Magento filesystem owner.
      5.2.2. Verify that 'ShippyPro_ShippyPro' is listed and shows as disabled: `php bin/magento module:status`.
      5.2.3. Enable the module with: `php bin/magento module:enable ShippyPro_ShippyPro`.
      5.2.4. Then we need to ensure the configuration tasks are run: `php bin/magento setup:upgrade`.
      5.2.5. After setup upgrade, run compile command: `php bin/magento setup:di:compile`.
      5.2.6. Finally on the command line to clear Magento's cache run: `php bin/magento cache:clean`.

6. Once this has been completed log in to the Magento Admin panel and proceed to Configuring the Module: 
    Admin panel -> Stores -> Configuration -> Sales -> Shipping methods -> ShippyPro