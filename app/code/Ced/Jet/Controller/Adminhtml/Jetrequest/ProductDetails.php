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
namespace Ced\Jet\Controller\Adminhtml\Jetrequest;

class ProductDetails extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';

    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Jet Product Detail Page
     *
     * @return String
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
        $sku = $product->getSku();
        $data_helper = $this->_objectManager->get('Ced\Jet\Helper\Data');
        $productStatus = $product->getJetProductStatus();
        $response = $data_helper->CGetRequest('/merchant-skus/'.rawurlencode($sku));
        $result = json_decode($response);
        if (isset($result) && isset($result->Message) && $result->Message == 'Authorization has been denied for this request.') {
            return $this->getResponse()->setBody('<div class="message message-error">Please check API User/API Secret/Fulfillment Node Id Under Jet->Configuration.</div><br></br>');
        }
        if (isset($result) && isset($result->error) && $result->error == 'merchant sku unavailable') {
            return $this->getResponse()->setBody('<div class="message message-error">Merchant Sku Not Existed</div><br></br>');
        }
        if ((!empty($result)) &&  ($result != '') &&  isset($result->merchant_sku)) {
            if (isset($result->status)) {
                $code = $this->getCode($result->status);
                if ($code != '') {
                    $product->setJetProductStatus($code);
                    $product->save();
                }
            }

            $substatus = '';
            if (!empty($result->sub_status)) {
                $substatus = implode(',', $result->sub_status);
            }
            $collectionData = [
                'sku'=>isset($result->merchant_sku) ? $result->merchant_sku : "",
                'title'=>isset($result->product_title) ? $result->product_title : "",
                'description'=>isset($result->product_description) ? $result->product_description : "",
                'merchant_id'=>isset($result->merchant_id) ? $result->merchant_id : "",
                'merchant_sku_id'=>isset($result->merchant_sku_id) ? $result->merchant_sku_id : "",
                'multipack_quantity'=>isset($result->multipack_quantity) ? $result->multipack_quantity : "",
                'sku_last_update'=>isset($result->sku_last_update) ? $result->sku_last_update : "",
                'inventory_last_update'=>isset($result->inventory_last_update) ? $result->inventory_last_update : "",
                'qty'=>isset($result->inventory_by_fulfillment_node[0]) ? $result->inventory_by_fulfillment_node[0]->quantity : "",
                'price'=>isset($result->price) ? $result->price : "",
                'status'=> isset($result->status) ? $result->status : 'No status Response form Jet.com' ,
                'sub_status'=>isset($substatus) ? $substatus : 'No Sub status from Jet',
                'fulfillment_price' => isset($result->price_by_fulfillment_node) ? $result->price_by_fulfillment_node[0] : '',
                'fulfillment_qty' =>isset($result->inventory_by_fulfillment_node[0]) ? $result->inventory_by_fulfillment_node[0] : '',
                'main_image_url' =>isset($result->main_image_url) ? $result->main_image_url : '',
                'manufacturer' => isset($result->manufacturer) ? $result->manufacturer : '',
                'safety_warning' => isset($result->safety_warning) ? $result->safety_warning : '',
                'brand' => isset($result->brand) ? $result->brand : ""
            ];
            $html = '<table style="width:100%; text-align:center; border-collapse:separate; border-spacing:1em;">';
            $html .= '<tbody class="admin__fieldset">';
            foreach ($collectionData as $key => $value) {
                $key_value = str_replace('_', ' ', $key);
                $key_value = ucfirst($key_value);
                $html .= '<tr><td class="admin__field"><label class="admin__field-label">'.$key_value.'</label></td>';
                $html .= '<td class="value admin__field-control">';
                $html .= $this->getHtml($key, $value) ;
            }
            $html .= '</tbody></table>';
            return $this->getResponse()->setBody($html);
        } else {
            if ($productStatus == 'not_uploaded') {
                return $this->getResponse()->setBody('<div class="message message-error">Product not uploaded on Jet.com</div><br></br>');
            } else {
                return $this->getResponse()->setBody('<div class="message message-error">Either product just uploaded(just uploaded product status will be visible when jet.com processed finish Processing for that product) OR Product does not uploaded at jet.com yet.</div><br></br>');
            }
        }
    }

    /**
     * get Code
     * @param $result
     * @return string
     */

    public function getCode($result)
    {
        switch($result) {
            case 'Available For Purchase':
                $code = 'available_for_purchase';
                break;
            case 'Archived':
                $code = 'archived';
                break;
            case 'Missing Listing Data':
                $code = 'missing_listing_data';
                break;
            case 'Under Jet Review':
                $code = 'under_jet_review';
                break;
            case 'Excluded':
                $code = 'excluded';
                break;
            case 'Unauthorized':
                $code = 'unauthorized';
                break;
            default:
                $code = '';
        }
        return $code;
    }

    /**
     * get Html
     * @param $key
     * @param $value
     * @return string
     */

    public function getHtml($key, $value)
    {
        $html = "";
        if ($key == 'fulfillment_price') {
            $real_value = $key == 'fulfillment_price' ? $value->fulfillment_node_price : "";
            $fulfillment_node_id = isset($value->fulfillment_node_id) ? $value->fulfillment_node_id : "";
            $html .= '<table class="admin__control-table"><thead><tr><th>Fulfillment Node</th><th>Value</th></tr></thead>';
            $html .= '<tbody><tr><td>'.$fulfillment_node_id.'</td><td>'.$real_value.'</td></tr></tbody></table></td></tr>';
        } else if ($key == 'fulfillment_qty') {
            $fulfillment_node_id = isset($value->fulfillment_node_id) ? $value->fulfillment_node_id : "";
            $real_value = isset($value->quantity) ? $value->quantity : "";
            $html .= '<table class="admin__control-table"><thead><tr><th>Fulfillment Node</th><th>Value</th></tr></thead>';
            $html .= '<tbody><tr><td>'.$fulfillment_node_id.'</td><td>'.$real_value.'</td></tr></tbody></table></td></tr>';
        }  else {
            if ($key == 'description') {
                $html .= '<textarea class="admin__control-text" cols="40" rows="6" readonly="1">'.$value.'</textarea></td></tr>';
            } else {
                $html .= '<input class="admin__control-text" type="text" readonly="1" value="'.$value.'"></td></tr>';
            }
        }
        return $html;
    }
}
