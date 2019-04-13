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
 * @package   Ced_Jet
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Jet\Model\Source\ShipException;

class OverrideType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $_objectManager;

    public $model;

    public $payment_data;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager

    ) {

        $this->_objectManager=$objectManager;

    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options[]= ['value'=>"",'label'=>"Please Select an Option"];
        $options[]= ['value'=>"Override charge",'label'=>"Override charge"];
        $options[]= ['value'=>"Additional charge",'label'=>"Additional charge"];
        return $options;

    }
}
