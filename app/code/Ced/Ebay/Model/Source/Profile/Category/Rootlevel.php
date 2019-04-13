<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Ebay
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\Ebay\Model\Source\Profile\Category;

class Rootlevel implements \Magento\Framework\Option\ArrayInterface
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
     * To Array
     * @return string|[]
     */
    public function toOptionArray()
    {
        $location = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ebay_config/ebay_setting/location');
        $mediaDirectory = $this->objectManager->get('\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $folderPath = $mediaDirectory->getAbsolutePath('code/Ced/Ebay/Setup/json/');
        $locationList = $this->objectManager->get('Ced\Ebay\Model\Config\Location')->toOptionArray();
        foreach ($locationList as $value) {
            if ($value['value'] == $location) {
                $locationName = $value['label'];
            }
        }
        $path = $folderPath .$locationName. '/categoryLevel-1.json';
        $rootlevel = $this->objectManager->get('Ced\Ebay\Helper\Data')->loadFile($path, '', '');
        $options = [];
        foreach ($rootlevel['CategoryArray']['Category'] as $value) {
            $options[]=[
                'value'=>$value['CategoryID'],
                'label'=>$value['CategoryName']
            ];
        }
        return $options;
    }

}