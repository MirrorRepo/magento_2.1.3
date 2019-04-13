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

class ShippingMethods implements \Magento\Framework\Option\ArrayInterface
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
        $options[] = ['value'=>"",'label'=>"Please Select an Option"];
        $options[] = ['value'=>"DHL Global Mail",'label'=>"DHL Global Mail"];
        $options[] = ['value'=>"FedEx 2 Day",'label'=>"FedEx 2 Day"];
        $options[] = ['value'=>"FedEx Express Saver",'label'=>"FedEx Express Saver"];
        $options[] = ['value'=>"FedEx First Overnight",'label'=>"FedEx First Overnight"];
        $options[] = ['value'=>"FedEx Ground",'label'=>"FedEx Ground"];
        $options[] = ['value'=>"FedEx Home Delivery",'label'=>"FedEx Home Delivery"];
        $options[]= ['value'=>"FedEx Priority Overnight",'label'=>"FedEx Priority Overnight"];
        $options[]= ['value'=>"FedEx Smart Post",'label'=>"FedEx Smart Post"];
        $options[]= ['value'=>"FedEx Standard Overnight",'label'=>"FedEx Standard Overnight"];
        $options[]= ['value'=>"Freight",'label'=>"Freight"];
        $options[]= ['value'=>"Ontrac Ground",'label'=>"Ontrac Ground"];
        $options[]= ['value'=>"UPS 2nd Day Air AM",'label'=>"UPS 2nd Day Air AM"];
        $options[]= ['value'=>"UPS 2nd Day Air",'label'=>"UPS 2nd Day Air"];
        $options[]= ['value'=>"UPS 3 Day Select",'label'=>"UPS 3 Day Select"];
        $options[]= ['value'=>"UPS Ground",'label'=>"UPS Ground"];
        $options[]= ['value'=>"UPS Mail Innovations",'label'=>"UPS Mail Innovations"];
        $options[]= ['value'=>"UPS Next Day Air Saver",'label'=>"UPS Next Day Air Saver"];
        $options[]= ['value'=>"UPS Next Day Air",'label'=>"UPS Next Day Air"];
        $options[]= ['value'=>"UPS SurePost",'label'=>"UPS SurePost"];
        $options[]= ['value'=>"USPS First Class Mail",'label'=>"USPS First Class Mail"];
        $options[]= ['value'=>"USPS Media Mail",'label'=>"USPS Media Mail"];
        $options[]= ['value'=>"USPS Priority Mail Express",'label'=>"USPS Priority Mail Express"];
        $options[]= ['value'=>"USPS Priority Mail",'label'=>"USPS Priority Mail"];
        $options[]= ['value'=>"USPS Standard Post",'label'=>"USPS Standard Post"];
        return $options;

    }
}
