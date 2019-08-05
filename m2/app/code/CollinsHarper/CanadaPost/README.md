The Collins Harper Measure Unit module extends base magento for more accurate Shipping Rates.
Please read about our Boxing module on collinsharper.com.


Extract this package into the root of your magento installation

manual 
add to composer.json under require
"collinsharper/module-measureunit": "2.*",
        "collinsharper/module-canadapost": "2.*"

		

 php bin/magento module:enable   CollinsHarper_CanadaPost --clear-static-content
  php bin/magento setup:upgrade
rm -rf   var/page_cache/* var/generation/* var/di/* var/cache/mage-*

 chown -R daemon:daemon app var

 TODO  put perm fixes in here

 