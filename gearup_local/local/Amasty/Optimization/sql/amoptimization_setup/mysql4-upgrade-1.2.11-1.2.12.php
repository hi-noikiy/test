<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


$this->startSetup();

$commands = array(
    'jpegoptim' => 'jpeg_cmd',
    'optipng' => 'png_cmd',
    'gifsicle' => 'gif_cmd'
);

foreach ($commands as $key => $cmd) {
    $oldSettings = Mage::getModel('core/config_data')->getCollection();
    $oldSettings->getSelect()->where('path = ?', 'amoptimization/images/' . $cmd);

    if (0 < $oldSettings->getSize()) {
        foreach ($oldSettings as $setting) {
            $value = $setting->getValue();
            if (false !== strpos($setting->getValue(), $key)) {
                $value = strtoupper($key);
            } else {
                Mage::getConfig()
                    ->saveConfig(
                        'amoptimization/images/bak_' . $cmd,
                        $value,
                        $setting->getScope(),
                        $setting->getScopeId()
                    );
                $value = 'NOTHING';
            }

            Mage::getConfig()
                ->saveConfig(
                    'amoptimization/images/' . $cmd,
                    $value,
                    $setting->getScope(),
                    $setting->getScopeId()
                );
        }
    }
}

$this->endSetup();
