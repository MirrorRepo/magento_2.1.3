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

class ListingDuration extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Options getter
     *
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'label' =>'Days 1',
                'value' => 'Days_1'
            ],
            [
                'label' => 'Days 10',
                'value' => 'Days_10'
            ],
            [
                'label' => 'Days 120',
                'value' => 'Days_120'
            ],
            [
                'label' => 'Days 14',
                'value' => 'Days_14'
            ],
            [
                'label' => 'Days 21',
                'value' => 'Days_21'
            ],
            [
                'label' => 'Days 3',
                'value' => 'Days_3'
            ],
            [
                'label' => 'Days 30',
                'value' => 'Days_30'
            ],
            [
                'label' => 'Days 7',
                'value' => 'Days_7'
            ],
            [
                'label' => 'Days 90',
                'value' => 'Days_90'
            ],
            [
                'label' => 'Days 60',
                'value' => 'Days_60'
            ],
            [
                'label' => 'Days 5',
                'value' => 'Days_5'
            ],
            [
                'label' => 'Good Till Cancelled',
                'value' =>'GTC'
            ],
        ];
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] =(string)$option['label'];
        }
        return $options;
    }

     /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * Get jet product status labels array with empty value
     *
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Get jet product status labels array for option element
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Get jet product status 
     *
     * @param string $optionId
     * @return null|string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
