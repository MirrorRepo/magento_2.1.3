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
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Jet\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * Get product type labels array with empty value
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
     * Get product type labels array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getTypes() as $typeId => $type) {
            $options[$typeId] =(string)$type['label'];
        }
        return $options;
    }

    /**
     * Get product type labels array for option element
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
     * Get product type labels array with empty value for option element
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }

    /**
     * Get product type label
     *
     * @param string $optionId
     * @return null|string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * Get product types
     *
     * @return array
     */
    public function getTypes()
    {
        return [
            [
                'value' => 'not_uploaded',
                'label' => __('Not Uploaded')
            ],
            [
                'value' => 'under_jet_review',
                'label' => __('Under Jet Review')
            ],
            [
                'value' => 'missing_listing_data',
                'label' => __('Missing Listing Data')
            ],
            [
                'value' => 'excluded',
                'label' => __('Excluded')
            ],
            [
                'value' => 'unauthorized',
                'label' => __('Unauthorized')
            ],
            [
                'value' => 'available_for_purchase',
                'label' => __('Available for Purchase')
            ],
            [
                'value' => 'archived',
                'label' => __('Archived')
            ]
        ];
    }


    /**
     * @return array
     */
    public function toOptionArray()
    {
      return $this->getOptions();
    }
}
