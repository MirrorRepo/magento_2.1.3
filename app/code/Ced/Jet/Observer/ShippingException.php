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
 * @category    Ced
 * @package     Ced_Jet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Jet\Observer;

use \Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Magento\Framework;
use \Magento\Framework\ObjectManagerInterface;

class ShippingException implements ObserverInterface
{
    /**
     *
     * @var ObjectManagerInterface
     */
    public $_objectManager;
    /**
     * @var ManagerInterface
     */
    public $messageManager;

    /**
     * ShippingException constructor.
     * @param ObjectManagerInterface $objectManager
     * @param RequestInterface $request
     * @param ScopeConfig $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        RequestInterface $request, ScopeConfig $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager) {
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->registry  = $registry;
    }

    /**
     * catalog product save after event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $autoUpdate = $this->scopeConfig->getValue('jetconfiguration/product_edit/auto_syn');
        $dataRequest = $this->request->getParam('product');
        $product = $observer->getEvent()->getProduct();
        if ($autoUpdate) {
            $this->autoSync($product);
        }
        $sku = $this->registry->registry('prev_sku');
        $newSku = "";
        if (empty($sku)) {
            $sku = $product->getSku();
        } else {
            $newSku = $product->getSku();
        }
        $skuExist = $this->_objectManager->get(
            'Ced\Jet\Helper\Data')->CGetRequest('/merchant-skus/' . $sku);
        $response = json_decode($skuExist, true);
        if ($this->ProductOnJet($response)) {
            // for Archive Request on SKU change
            if ((!empty($newSku) && $sku != $newSku) || $dataRequest['status'] == 2) {
                $this->ArchiveProduct($sku, $newSku);
            }

            // shipping exception save code start
            try {
                $fulfillmentnodeid = $this->scopeConfig->getValue('jetconfiguration/jetsetting/fulfillment_node_id'
                );
                // if shipping exception generate for any sku
                $exceptiontype = $this->request->getPost('shipping_excep');
                if ($this->AddShipException($exceptiontype)) {
                    $chargeamount = $this->request->getPost(
                     'shipping_charge');
                    $shippinglevel = $this->request->getPost(
                        'shipping_carrier');
                    $shippingmethod = $this->request->getPost(
                     'shipping_method');
                    $overridetype = $this->request->getPost(
                        'shipping_override');

                    $shipping = $this->PostShipData($chargeamount, 
                        $shippinglevel,$shippingmethod, $overridetype, 
                        $exceptiontype, $fulfillmentnodeid);


                    //  put request for shipping-exception API
                    $data = $this->_objectManager->get('Ced\Jet\Helper\Data'
                    )->CPutRequest('/merchant-skus/' . rawurlencode($sku)
                     . '/shippingexception', json_encode($shipping));

                    // success in response...
                    if ($data == '') {
                        // for save shipping exception data
                        $this->SaveData($sku, $shipping);
                    } else {
                        // Error raised while add shipping exception
                        $error = json_decode($data, true);
                        $this->showError($error);
                    }
                }
                //shipping exception save code end


                // code for return exception
                if ($this->request->getPost('locations' )){
                    $timeToReturn = $this->request->getPost(
                     'time_to_return');
                    $location = $this->request->getPost('locations');
                    $ship_method = $this->request->getPost('ship_methods');
                    $this->ReturnException($timeToReturn, $location,
                     $ship_method);
                }
                // return exception code end
            } catch(\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->registry->unregister('prev_sku');
    }

    /**
     * @param $response
     * @return boolean
     */
    public function ProductOnJet($response)
    {
        if (!empty($response) && !isset($response['Message'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $exceptiontype
     * @return boolean
     */
    public function AddShipException($exceptiontype)
    {
        if ($exceptiontype != "N/A") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * show error message on frontend
     * @param $error
     * @return void
     */
    public function showError($error)
    {
        $msg = 'Exception Failed.<br/>';
        if (isset($error['errors']) && ! empty($error['errors'])) {
            $count = count($error ['errors']);
            for($i = 0; $i < $count; $i ++) {
                $msg = $msg . $error ['errors'] [$i] . '</br>';
            }
            $this->messageManager->addError($msg);
        } else {
            $this->messageManager->addError(
                "There is an error in exception processing");
        }
    }

    /**
     * @param $chargeamount
     * @param $shippinglevel
     * @param $shippingmethod
     * @param $overridetype
     * @param $exceptiontype
     * @param $fulfillmentnodeid
     * @return array
     */
    public function PostShipData($chargeamount, $shippinglevel, $shippingmethod, $overridetype, $exceptiontype, $fulfillmentnodeid)
    {
        // when shipping-level and shipping-method both get in params
        if (isset($shippinglevel) && isset($shippingmethod )) {
            $shipping = [];
            // only for restricted exception type
            if ($exceptiontype == "restricted") {
                $shippingException = $this->RestrictedType($shippingmethod, $shippinglevel);
                $shipping ['fulfillment_nodes'] [] = [
                    'fulfillment_node_id' => "$fulfillmentnodeid",
                    'shipping_exceptions' => $shippingException];
            } else {
                $shipping ['fulfillment_nodes'] [] = ['fulfillment_node_id' => "$fulfillmentnodeid",
                    'shipping_exceptions' => [
                        [ 'shipping_method' => trim($shippingmethod[0]),
                            'override_type' => $overridetype,
                            'shipping_charge_amount' =>(float) $chargeamount,
                            'shipping_exception_type' => $exceptiontype]]];
            }
        } // when shipping level gets in params
        else if (isset($shippinglevel) && empty($shippingmethod)) {
            $shipping = [];
            // only for restricted exception type
            if ($exceptiontype == "restricted") {
                $shippingException = $this->RestrictedType($shippinglevel);
                $shipping ['fulfillment_nodes'] [] = [
                    'fulfillment_node_id' => "$fulfillmentnodeid",
                    'shipping_exceptions' => $shippingException
                ];
            } else {
                $shipping ['fulfillment_nodes'] [] = ['fulfillment_node_id' => "$fulfillmentnodeid",
                    'shipping_exceptions' => [
                        [ 'service_level' => $shippinglevel[0],
                            'override_type' => $overridetype,
                            'shipping_charge_amount' =>(float) $chargeamount,
                            'shipping_exception_type' => $exceptiontype]
                    ]];
            }

        } // when shipping-method gets in params
        else if (isset($shippingmethod) && empty($shippinglevel)) {
            $shipping = [];
            // only for restricted exception type
            if ($exceptiontype == "restricted") {
                $shippingException = $this->RestrictedType($shippingmethod);
                $shipping ['fulfillment_nodes'] [] =[
                    'fulfillment_node_id' => "$fulfillmentnodeid",
                    'shipping_exceptions' => $shippingException
                ];

            } else  {
                $shipping ['fulfillment_nodes'] [] = [
                    'fulfillment_node_id' => "$fulfillmentnodeid",
                    'shipping_exceptions' => [[
                        'shipping_method' => trim($shippingmethod[0]),
                        'override_type' => $overridetype,
                        'shipping_charge_amount' =>(float) $chargeamount,
                        'shipping_exception_type' => $exceptiontype]]
                ];
            }
        }
        return $shipping;
    }


    /**
     * @param $shippingmethod
     * @param $shippinglevel
     * @return array
     */

    public function RestrictedType($shippingmethod = [], $shippinglevel = [])
    {
        $shippingException = [];
        if (empty($shippinglevel)) {
            foreach ($shippingmethod as $method) {
                $shippingException[] = [
                    'shipping_method' => $method,
                    'shipping_exception_type' => "restricted"];
            }
        } else	if (empty($shippingmethod)) {
            foreach ($shippinglevel as $level) {
                $shippingException[] = [
                    'shipping_method' => $level,
                    'shipping_exception_type' => "restricted"];
            }
        } else {
            foreach ($shippingmethod as $method) {
                $shippingException[] = [
                    'shipping_method' => $method,
                    'shipping_exception_type' => "restricted"];
            }
            foreach ($shippinglevel as $level) {
                $shippingException[] = [
                    'service_level' => $level,
                    'shipping_exception_type' => "restricted"];
            }
        }
        return $shippingException;
    }

    /**
     * @param $sku
     * @param $newSku
     * @return void
     */

    public function ArchiveProduct($sku,$newSku)
    {
        $request_sent =  $this->_objectManager->get(
            'Ced\Jet\Helper\Data')->CPutRequest(
            '/merchant-skus/'.$sku.'/status/archive',json_encode(
                ['is_archived'=>true]));
        if (empty($request_sent)) {
            $this->messageManager->addSuccess(
                'Archive Request for '.$sku.' has been sent to jet, 
			 Please re-upload current product Sku '.$newSku.' on jet');
        } else {
            $request_sent = json_decode($request_sent,true);
            $request_sent = empty($request_sent) ? [] : $request_sent;
            foreach ($request_sent as $value) {
                $this->messageManager->addError($value);
            }
        }
    }

    /**
     * @param $timeToReturn
     * @param $ship_method
     * @param $location
     * @return void
     */
    public function ReturnException($timeToReturn, $ship_method, $location)
    {
        if (isset($timeToReturn) && trim($timeToReturn) != "") {
            $timeToReturn =(int) $timeToReturn;
            if ($timeToReturn > 30 || $timeToReturn < 0) {
                $this->messageManager->addError(
                    'Please enter return time between 1 to 30.');
                return;
            }
            $return ['time_to_return'] = $timeToReturn;

        }
        $locations = explode(',',$location);
        $return ['return_location_ids'] = $locations;

        if (isset($ship_method)) {
            $return ['return_shipping_methods'] = $ship_method;
        }
        // put request for return exception API
        if (!empty($return)) {
            $url = '/merchant-skus/' . rawurlencode($sku) .
            '/returnsexception';
            $data = $this->_objectManager->get(
                'Ced\Jet\Helper\Data')->CPutRequest(
                 $url, json_encode($return));
            if (!empty($data) || $data != '') {
                // Error in response
                $error = json_decode($data,true);
                $this->showError($error);

            } else {
                // showing success message in forntend
                $this->messageManager->addSuccess(
                    'Return Exception has been saved successfully');
            }
        }
    }

    /**
     * @param $sku
     * @param $shipping
     * @return void
     */
    public function SaveData($sku, $shipping)
    {
        $shippingObj = $this->_objectManager->get(
            'Ced\Jet\Model\ShippingException');
        $collectionload = $shippingObj->getCollection()->addFieldToFilter(
            'sku', $sku);
        foreach ($collectionload as $value) {
            $id = $value ['id'];
            break;
        }
        if ($collectionload->getSize() > 0) {
            $shippingObj->load($id)->setData(
                'sku', $sku)
                ->setData(
                    'fulfillment_node_id',
                    $shipping['fulfillment_nodes'][0]['fulfillment_node_id'])
                ->setData(
                    'shipping_exception', json_encode($shipping));
            $shippingObj->save();
            $this->messageManager->addSuccess(
                'Shipping Exception already exist for current SKU. Now Updated');
        } else {
            $shippingObj->setData(
                'sku', $sku)
                ->setData(
                    'fulfillment_node_id',
                    $shipping['fulfillment_nodes'][0]['fulfillment_node_id'])
                ->setData(
                    'shipping_exception', json_encode($shipping));
            $shippingObj->save();
            $this->messageManager->addSuccess(
                'Shipping Exception has been saved successfully');
        }
    }

    /**
     * @param $product
     * @return void
     */
    public function autoSync($product)
    {
        $response = $this->_objectManager->get('Ced\Jet\Helper\Jet')->updateonjet($product);
        if (!empty($response)) {
            $response = str_replace(" ", ",", $response);
            $msg = $response." Successfully Sync With Jet";
            $this->messageManager->addSuccess($msg);
        }
    }
}
