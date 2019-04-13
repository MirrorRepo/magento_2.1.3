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
namespace Ced\Jet\Model\Source\Category;

class Leveltwo implements \Magento\Framework\Option\ArrayInterface
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
        $collection=$this->_objectManager->create('Ced\Jet\Model\Categories')->getCollection()->addFieldToFilter('level',2);
        $options = [];
        foreach ($collection as $key => $value) {

            $options[]= [];

        }


        return $options;

    }
}
