<?php

class MageWorkshop_Core_Helper_ModuleManager extends Mage_Core_Helper_Abstract
{
    const ENABLED_STATUS = 'enabled';
    const DISABLED_STATUS = 'disabled';


    /**
     * @param $moduleConfig
     * @param $moduleName
     * @param $moduleConfigPath
     */
    public function enableModule($moduleConfig, $moduleName, $moduleConfigPath)
    {
        if (strtolower($moduleConfig->getModuleName()) == strtolower($moduleName)) {
            $storeId = Mage::app()->getStore()->getId();
            $coreConfig = Mage::getModel('core/config');
            if(Mage::getStoreConfig($moduleConfigPath, $storeId)) {
                $coreConfig->saveConfig($moduleConfigPath, 0);
                $moduleConfig->setEnabled(self::DISABLED_STATUS);
            } else {
                $coreConfig->saveConfig($moduleConfigPath, 1);
                $moduleConfig->setEnabled(self::ENABLED_STATUS);
            }

        }
    }

    /**
     * @param $moduleContainer
     * @param $moduleName
     * @param $moduleConfigPath
     */
    public function checkIfModuleEnabled($moduleContainer, $moduleName, $moduleConfigPath)
    {
        if (strtolower($moduleContainer->getModule()) == strtolower($moduleName)) {
            $storeId = Mage::app()->getStore()->getId();
            $moduleContainer->setEnabled(Mage::getStoreConfig($moduleConfigPath, $storeId));
        }
    }

    public function uninstallModule($moduleConfig, $moduleName, $packageFile, $uninstallModelPath)
    {
        if (strtolower($moduleConfig->getModuleName()) == strtolower($moduleName)) {
            $uninstaller = Mage::getModel('drcore/uninstall');
            $helper = Mage::helper('drcore');
            if ($uninstaller->checkPackageFile($packageFile)) {
                try {
                    Mage::getModel($uninstallModelPath)->clearDatabaseInformation();
                    $moduleConfig->setModuleName($moduleName);
                    if ($dependent = $moduleConfig->getPackageName()) {
                        $moduleConfig->setDependentPackage($dependent);
                        $moduleConfig->setParentPackage($packageFile);
                    }
                    $moduleConfig->setPackageName($packageFile);

                } catch (Mage_Core_Exception $e) {
                    $moduleConfig->setException($e->getMessage());
                } catch (Exception $e) {
                    $moduleConfig->setException($helper->__('There was a problem with uninstalling.'));
                }
            } else {
                $moduleConfig->setException($helper->__('Cannot find package file for $s plugin.', $packageFile));
            }
        }
    }
}