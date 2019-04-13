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

class Restocking implements \Magento\Framework\Option\ArrayInterface
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
    	$restocking = $this->objectManager->get('Ced\Ebay\Helper\Data')->returnPolicyValue();
        $options = [];
        if (!empty($restocking)) {
            foreach ($restocking['ReturnPolicyDetails']['RestockingFeeValue'] as $value) {
                $options[]=[
                    'value'=>$value['RestockingFeeValueOption'],
                    'label'=>$value['Description']
                ];
            }
        }
        return $options;
   	}
}