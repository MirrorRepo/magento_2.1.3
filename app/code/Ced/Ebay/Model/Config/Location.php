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

class Location implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [   'label' => 'Please Select Store Location', 'value' => '' ],

            [   'label'=>'Australia', 'value'=> 15 ],

            [   'label' => 'Canada',  'value' => 2 ],

            [   'label'=>'CanadaFrench', 'value'=> 210 ],
            
            [   'label' => 'HongKong',  'value' => 201 ],

            [   'label'=>'Malaysia', 'value'=> 207 ],
            
            [   'label' => 'Philippines',  'value' => 211 ],

            [   'label'=>'US', 'value'=> '0' ],

            [   'label'=>'Singapore', 'value'=> 216 ],

            [   'label' => 'UK',  'value' => 3 ]
        ];
    }
}
