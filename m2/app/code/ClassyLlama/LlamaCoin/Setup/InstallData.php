<?php

namespace ClassyLlama\LlamaCoin\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface { 
    
    protected $_errorcodeFactory;

    public function __construct(\ClassyLlama\LlamaCoin\Model\ErrorcodeFactory $errorcodeFactory)
    {
            $this->_errorcodeFactory = $errorcodeFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $prefix = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR;
        $csvs = array(
            'webservices' => 'webservices-codes-0.3.3.csv',
        );

        foreach ($csvs as $key => $name) {
            $path = $prefix . $name;
            $fh = fopen($path, 'r');

            while (($row = fgetcsv($fh, 999999, ',')) !== false) {
                list($code, $message) = $row;
                $post = $this->_errorcodeFactory->create();
                $data = array(
                    'code' => $code,
                    'message' => $message,
                    'active' => 0,
                    
                );
                $post->addData($data)->save();
            }
        }
    }
}
