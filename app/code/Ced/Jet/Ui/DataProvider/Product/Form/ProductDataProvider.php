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
namespace Ced\Jet\Ui\DataProvider\Product\Form;

/**
 * DataProvider for product edit form
 */
class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider
{
    /**
     * @return array
     */
    public function getData()
    {
        parent::getData();
        $getProductData = $this->data;
        foreach ($getProductData as  $key => $value) {
            $sku = isset($value['product']['sku']) ? $value['product']['sku'] : "";
            break;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $shipping = $objectManager->get('Ced\Jet\Model\ShippingException');
        $shipData = $shipping->getCollection()->addFieldToFilter(
            'sku', $sku)->getData();
        if (!empty($shipData)) {
            $prepareData = json_decode($shipData[0]['shipping_exception'],
                true);
            $setData = $prepareData['fulfillment_nodes'][0][
            'shipping_exceptions'];
            if ($setData[0]['shipping_exception_type']  == "restricted") {
                $shipping_method = "";
                $shipping_carrier = "";
                foreach ($setData as $key => $value) {
                    $shipping_exception=$value['shipping_exception_type'];
                    $shipping_method .=  isset($value['shipping_method']) ?
                        $value['shipping_method'] : "";
                    $shipping_carrier .= isset($value['service_level']) ?
                        $value['service_level'] : "";
                    $shipping_method .= isset($value['shipping_method']) ? ","
                        : "";
                    $shipping_carrier .= isset($value['service_level']) ? ","
                        : "";
                }
                $string = $objectManager->get(
                    '\Magento\Framework\Stdlib\StringUtils') ;
                $shipping_method = $string->substr($shipping_method, 0,
                    $string->strlen($shipping_method)-1);
                $shipping_carrier = $string->substr($shipping_carrier, 0,
                    $string->strlen($shipping_carrier)-1);
                $shipping_charge = $shipping_override = "";
            } else {
                $shipping_exception = $setData[0]['shipping_exception_type'];
                $shipping_override = isset($setData[0]['override_type']) ?
                    $setData[0]['override_type'] : "";
                $shipping_charge = isset($setData[0]['shipping_charge_amount']) ?
                    $setData[0]['shipping_charge_amount'] : "";
                $shipping_method = isset($setData[0]['shipping_method']) ?
                    $setData[0]['shipping_method'] : "";
                $shipping_carrier = isset($setData[0]['service_level']) ?
                    $setData[0]['service_level'] : "";
            }
            foreach ($getProductData as $key => $value) {
                $getProductData[$key]['shipping_charge'] = $shipping_charge;
                $getProductData[$key]['shipping_excep'] = $shipping_exception;
                $getProductData[$key]['shipping_carrier'] = $shipping_carrier;
                $getProductData[$key]['shipping_method'] = $shipping_method;
                $getProductData[$key]['shipping_override'] = $shipping_override;
            }

        }
        $this->data = $getProductData;
        return $this->data;
    }
}
