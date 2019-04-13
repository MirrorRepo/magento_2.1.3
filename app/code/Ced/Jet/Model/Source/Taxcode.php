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

class Taxcode extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	/**
  * @return array
  */
    public function getAllOptions()
    {
 		return [
 				['label' =>'', 'value' => ''],
				['label' =>'Toilet Paper', 'value' => 'Toilet Paper'],
				['label'=>'Thermometers', 'value'=>'Thermometers'],
				['label'=>'Sweatbands', 'value'=>'Sweatbands'],
				['label'=>'SPF Suncare Products', 'value'=>'SPF Suncare Products'],
				['label'=>'Sparkling Water', 'value'=>'Sparkling Water'],
				['label'=>'Smoking Cessation', 'value'=>'Smoking Cessation'],
				['label'=>'Shoe Insoles', 'value'=>'Shoe Insoles'],
				['label'=>'Safety Clothing', 'value'=>'Safety Clothing'],
				['label'=>'Pet Foods', 'value'=>'Pet Foods'],
				['label'=>'Paper Products', 'value'=>'Paper Products'],
				['label'=>'OTC Pet Meds', 'value'=>'OTC Pet Meds'],
				['label'=>'OTC Medication', 'value'=>'OTC Medication'],
				['label'=>'Oral Care Products', 'value'=>'Oral Care Products'],
				['label'=>'Non-Motorized Boats', 'value'=>'Non-Motorized Boats'],
				['label'=>'Non Taxable Product', 'value'=>'Non Taxable Product'],
				['label'=>'Mobility Equipment', 'value'=>'Mobility Equipment'],
				['label'=>'Medicated Personal Care Items', 'value'=>'Medicated Personal Care Items'],
				['label'=>'Infant Clothing', 'value'=>'Infant Clothing'],
				['label'=>'Helmets', 'value'=>'Helmets'],
				['label'=>'Handkerchiefs', 'value'=>'Handkerchiefs'],
				['label'=>'Generic Taxable Product', 'value'=>'Generic Taxable Product'],
				['label'=>'General Grocery Items', 'value'=>'General Grocery Items'],
				['label'=>'General Clothing', 'value'=>'General Clothing'],
				['label'=>'Fluoride Toothpaste', 'value'=>'Fluoride Toothpaste'],
				['label'=>'Durable Medical Equipment', 'value'=>'Durable Medical Equipment'],
				['label'=>'Drinks under 50 Percent Juice', 'value'=>'Drinks under 50 Percent Juice'],
				['label'=>'Disposable Wipes', 'value'=>'Disposable Wipes'],
				['label'=>'Disposable Infant Diapers', 'value'=>'Disposable Infant Diapers'],
				['label'=>'Dietary Supplements', 'value'=>'Dietary Supplements'],
				['label'=>'Diabetic Supplies', 'value'=>'Diabetic Supplies'],
				['label'=>'Costumes', 'value'=>'Costumes'],
				['label'=>'Contraceptives', 'value'=>'Contraceptives'],
				['label'=>'Contact Lens Solution', 'value'=>'Contact Lens Solution'],
				['label'=>'Carbonated Soft Drinks', 'value'=>'Carbonated Soft Drinks'],
				['label'=>'Car Seats', 'value'=>'Car Seats'],
				['label'=>'Candy with Flour', 'value'=>'Candy with Flour'],
				['label'=>'Candy', 'value'=>'Candy'],
				['label'=>'Breast Pumps', 'value'=>'Breast Pumps'],
				['label'=>'Braces and Supports', 'value'=>'Braces and Supports'],
				['label'=>'Bottled Water Plain', 'value'=>'Bottled Water Plain'],
				['label'=>'Beverages with 51 to 99 Percent Juice', 'value'=>'Beverages with 51 to 99 Percent Juice'],
				['label'=>'Bathing Suits', 'value'=>'Bathing Suits'],
				['label'=>'Bandages and First Aid Kits', 'value'=>'Bandages and First Aid Kits'],
				['label'=>'Baby Supplies', 'value'=>'Baby Supplies'],
				['label'=>'Athletic Clothing', 'value'=>'Athletic Clothing'],
				['label'=>'Adult Diapers', 'value'=>'Adult Diapers'],
		];
    }
 
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * Get jet Taxcode
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
     * Get jet Taxcode array for option element
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
     * Get jet Taxcode labels array with empty value
     *
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }
}
