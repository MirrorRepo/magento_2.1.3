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
namespace Ced\Jet\Model\Source;

class Productstatus extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
    * @return array
    */
    public function getAllOptions()
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
