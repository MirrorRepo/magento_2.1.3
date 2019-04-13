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
namespace Ced\Ebay\Model\Source;

class Barcode extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
  * @return array
  */
    public function getAllOptions()
    {
        return [
            [
                'value' => '',
                'label' => __('Select Barcode Type'),
            ],
            [
                'value' => 'upc',
                'label' => __('UPC'),
            ],
            [
                'value' => 'ean',
                'label' => __('EAN'),
            ],
            [
                'value' => 'asin',
                'label' => __('ASIN'),
            ],
            [
                'value' => 'isbn-10',
                'label' => __('ISBN-10'),
            ],
            [
                'value' => 'isbn-13',
                'label' => __('ISBN-13'),
            ],
            [
                'value' => 'gtin-14',
                'label' => __('GTIN-14'),
            ]
        ];
    }
}
