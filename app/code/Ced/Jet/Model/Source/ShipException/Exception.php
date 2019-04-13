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

class Exception implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @return array
     */

 public function toOptionArray()
 {		
  return [
   [
    'value' => "N/A",
    'label' => __('Please Select an Option'),
   ],
   [
    'value' => "exclusive",
    'label' => __('exclusive'),
   ],
   [
    'value' => "restricted",
    'label' => __('restricted'),
   ],
   [
    'value' => "include",
    'label' => __('include'),
   ],
  ];

 }
}
