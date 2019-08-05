<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Paysafe
 * @copyright   Copyright (c) 2017 Paysafe
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Paysafe\Paysafe\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\RequestInterface;

class PaymentConfigProvider implements ConfigProviderInterface
{
    protected $paymentHelper;
    protected $assetRepo;
    protected $request;

    protected $methodCodes = [
        'paysafe_creditcard'
    ];

    protected $brands = [
        'Visa',
        'MasterCard',
        'Maestro',
        'AmericanExpress',
        'Diners',
        'JCB'
    ];
    /**
     *
     * @param PaymentHelper    $paymentHelper
     * @param Repository       $assetRepo
     * @param RequestInterface $request
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Repository $assetRepo,
        RequestInterface $request
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->assetRepo = $assetRepo;
        $this->request = $request;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * get configurations
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methodCodes as $code) {
            $methodInstance = $this->paymentHelper->getMethodInstance($code);
            if ($methodInstance->isAvailable()) {
                $selectedBrands = explode(',',$this->methods[$code]->getBrand());
                foreach ($this->brands as $brand) {
                    if (in_array($brand, $selectedBrands)) {
                        $display = 'block';
                    } else {
                        $display = 'none';
                    }
                    $asset = $this->createAsset('Paysafe_Paysafe::images/' . strtolower($brand) . '.png');
                    $config['payment']['paysafe']['logos'][$code][$brand] = [
                        'url' => $asset->getUrl(),
                        'height' => '35px',
                        'display' => $display
                    ];
                }
            }
        }
        
        return $config;
    }

    /**
     * create an asset
     * @param  string $fileId
     * @param  array  $params
     * @return object
     */
    public function createAsset($fileId, array $params = [])
    {
        $params = array_merge(['_secure' => $this->request->isSecure()], $params);
        return $this->assetRepo->createAsset($fileId, $params);
    }
}
