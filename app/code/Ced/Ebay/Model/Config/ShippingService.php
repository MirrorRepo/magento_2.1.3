<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Ebay
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Ebay\Model\Config;

class ShippingService implements \Magento\Framework\Option\ArrayInterface
{
	/**
     * Objet Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $location = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ebay_config/ebay_setting/location');
        $locationList = $this->objectManager->get('Ced\Ebay\Model\Config\Location')->toOptionArray();
        foreach ($locationList as $value) {
          if ($value['value'] == $location) {
              $locationName = $value['label'];
          }
        }
    	$mediaDirectory = $this->objectManager->get('\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $folderPath = $mediaDirectory->getAbsolutePath('code/Ced/Ebay/Setup/json/');
        $path = $folderPath .$locationName. '/shippingDetails.json';
        if (file_exists($folderPath .$locationName)) {
            $shippingService = $this->objectManager->get('Ced\Ebay\Helper\Data')->loadFile($path, '', '');
            foreach ($shippingService['ShippingServiceDetails'] as $value) {
              if (!isset($value['InternationalService'])) {
                  $options1[]=[
                      'value' => $value['ShippingService'],
                      'label' => $value['Description']
                  ];
              } else {
                  $options2[]=[
                      'value' => $value['ShippingService'],
                      'label' => $value['Description']
                  ];
              }
          }
          $optionsDom[] = [
                      'disabled' => 'disabled',
                      'value' => "",
                      'label' => "-----Domestic Shipping Services-----"
                  ];
          $optionsInt[] =  [
                      'value' => "",
                      'label' => "-----International Shipping Services-----"
                  ];
          $options = array_merge($optionsDom, $options1, $optionsInt, $options2);
        }
        return $options;
   	}
}