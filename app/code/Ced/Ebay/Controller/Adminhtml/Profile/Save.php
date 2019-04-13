<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Ebay
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Ebay\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{
	public $_coreRegistry;
    public  $_cache;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
			Context $context,
			\Magento\Framework\Registry $coreRegistry,
            \Ced\Ebay\Helper\Cache $cache
	) {
		parent::__construct($context);
		$this->_coreRegistry     = $coreRegistry;
        $this->_cache = $cache;
	}
    /**
     *
     * @param string $idFieldName
     * @return mixed
     */
    protected function _initProfile($idFieldName = 'pcode')
    {

        $profileCode = $this->getRequest()->getParam($idFieldName);
        $profile = $this->_objectManager->get('Ced\Ebay\Model\Profile');


        if ($profileCode) {
            $profile->loadByField('profile_code',$profileCode);
        }

        $this->getRequest()->setParam('is_ebay',1);
        $this->_coreRegistry->register('current_profile', $profile);
        return $this->_coreRegistry->registry('current_profile');
    }


    public function execute()
	{
        $optAttribute = $ebayAttribute = $ebayReqOptAttribute = [];
		$data=$this->_objectManager->create('Magento\Config\Model\Config\Structure\Element\Group')->getData();
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->_context = $this->_objectManager->get('Magento\Framework\App\Helper\Context');
		$redirectBack   = $this->getRequest()->getParam('back', false);
		$tab   			= $this->getRequest()->getParam('tab', false);
		$pcode        = $this->getRequest()->getParam('pcode', false);
		$profileData  = $this->getRequest()->getPostValue();
        $category[] = isset($profileData['level_0']) ? $profileData['level_0'] : "";
        $category[] = isset($profileData['level_1']) ? $profileData['level_1'] : "";
        $category[] = isset($profileData['level_2']) ? $profileData['level_2'] : "";
        $category[] = isset($profileData['level_3']) ? $profileData['level_3'] : "";
		$entity_id =array();
        $profileData=json_decode(json_encode($profileData),1);

            $inProfile        = $this->getRequest()->getParam('in_profile');
			$profileProducts  = $this->getRequest()->getParam('in_profile_product', null);
			parse_str($profileProducts, $profileProducts);
            $profileProducts = array_keys($profileProducts);
			$oldProfileProducts = $this->getRequest()->getParam('in_profile_product_old');
			parse_str($oldProfileProducts, $oldProfileProducts);
            $oldProfileProducts = array_keys($oldProfileProducts);

            $profileData=json_decode(json_encode($profileData),1);

            $resource   =  $this->getRequest()->getPost('resource', false);

        try {
            $profile = $this->_initProfile('pcode');
            if (!$profile->getId() && $pcode) {
                $this->messageManager->addError(__('This Profile no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }

            $pname = $profileData['profile_name'];
            if (isset($profileData['profile_code'])) {
                $pcode = $profileData['profile_code'];
                $profileCollection = $this->_objectManager->get('Ced\Ebay\Model\Profile')->getCollection()->
                    addFieldToFilter('profile_code', $profileData['profile_code']);
                if (count($profileCollection) > 0) {
                    $this->messageManager->addError(__('This Profile Already Exist Please Change Profile Code'));
                    $this->_redirect('*/*/new');
                    return;
                }
            }

            // check for category save
            if (empty($pcode)) {
                $checkCategory = $this->_objectManager->get('Ced\Ebay\Model\Profile')->getCollection()->addFieldToFilter('profile_category', json_encode($category));
                if ($checkCategory->getSize() > 0) {
                    $this->messageManager->addError(__('Category Already Exist For Other Profile Please Change Category'));
                    $this->_redirect('*/*/new');
                    return;
                }
            } else {
                $checkCategory = $this->_objectManager->get('Ced\Ebay\Model\Profile')->getCollection()->addFieldToFilter('profile_category', json_encode($category))->getData();
                    foreach ($checkCategory as $value) {
                        if ($value['profile_code'] != $pcode) {
                            $this->messageManager->addError(__('Category Already Exist For Other Profile Please Change Category'));
                            $this->_redirect('*/*/edit' , array('pcode' => $pcode));
                            return;
                        }
                    }
            }
            $profile->addData($profileData);
            $profile->setProfileCategory(json_encode($category));
            
            // save attribute
            $requriedAttributes = [];   
            if (isset($profileData['ebay_attributes']))
                $requriedAttributes['ebay_attributes'] = $this->unique_multidim_array($profileData['ebay_attributes'], 'ebay_attribute_name');

            if (!empty($requriedAttributes['ebay_attributes'])) {
                $temp = [];
                foreach ($requriedAttributes['ebay_attributes'] as $item) {
                    $temp['ebay_attribute_type'] = $item['ebay_attribute_type'];
                    $temp['magento_attribute_code'] = $item['magento_attribute_code'];
                    $ebayAttribute[$item['ebay_attribute_name']] = $temp;
                }
                $profile->setProfileCatAttribute(json_encode($ebayAttribute));
            } else {
                $this->messageManager->addError(__('Please map all ebay attributes.'));
                $this->_redirect('*/*/new');
                return;
            }

            // save required and optional attribute
            $reqOptAttribute = [];
            if (!empty($profileData['required_attributes'])) {
                $temAttribute = $this->unique_multidim_array($profileData['required_attributes'], 'ebay_attribute_name');
                $temp1 = $temp2 = [];
                foreach ($temAttribute as $item) {
                    if ($item['required']) {
                        $temp1['ebay_attribute_name'] = $item['ebay_attribute_name'];
                        $temp1['ebay_attribute_type'] = $item['ebay_attribute_type'];
                        $temp1['magento_attribute_code'] = $item['magento_attribute_code'];
                        $temp1['required'] = $item['required'];
                        $reqAttribute[] = $temp1;
                    } else {
                        $temp2['ebay_attribute_name'] = $item['ebay_attribute_name'];
                        $temp2['ebay_attribute_type'] = $item['ebay_attribute_type'];
                        $temp2['magento_attribute_code'] = $item['magento_attribute_code'];
                        $temp2['required'] = 0;
                        $optAttribute[] = $temp2;
                    }
                }
                $ebayReqOptAttribute['required_attributes'] = $reqAttribute;
                $ebayReqOptAttribute['optional_attributes'] = $optAttribute;

                $profile->setProfileReqOptAttribute(json_encode($ebayReqOptAttribute));
            } else {
                $profile->setProfileReqOptAttribute('');
            } 

            // save category features
            if (isset($profileData['feature']))
                $profile->setProfileCatFeature($profileData['feature']);

            //save profile
            $profile->save();

            //cache values
            $this->_cache->setValue(\Ced\Ebay\Helper\Cache::PROFILE_CACHE_KEY.$profile->getId(), $profile->getData());

            foreach ($oldProfileProducts as $oUid) {
                $this->_deleteProductFromProfile($oUid);
                $this->_cache->removeValue(\Ced\Ebay\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY.$oUid);
            }

            foreach ($profileProducts as $nRuid) {
                $this->_addProductToProfile($nRuid, $profile->getId());
                $this->_cache->setValue(\Ced\Ebay\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY.$nRuid, $profile->getId());
            }


            if ($redirectBack && $redirectBack=='edit') {
                $this->messageManager->addSuccess(__('
		   		You Saved The Ebay Profile And Its Products.
		   			'));
                $this->_redirect('*/*/edit', array(
                    'pcode' => $pcode,
                ));
            }else if ($redirectBack && $redirectBack=='upload') {
                $this->messageManager->addSuccess(__('
		   		You Saved The Ebay Profile And Its Products. Upload Product Now.
		   			'));
                $this->_redirect('ebay/products/index', array(
                    'profile_id' => $profile->getId()
                ));
            } else {
                $this->messageManager->addSuccess(__('
		   		You Saved The Ebay Profile And Its Products.
		   		'));
                $this->_redirect('*/*/');
            }
        }catch (\Exception $e){
            $this->messageManager->addError(__('
		   		Unable to Save Profile Please Try Again.
		   			'. $e->getMessage()));
            $this->_redirect('*/*/edit', array(
                'pcode' => $pcode
            ));
        }

		return;
	}

	protected function _addProductToProfile($productId, $profileId)
	{
	    $profileproduct = $this->_objectManager->create("Ced\Ebay\Model\Profileproducts")
            ->deleteFromProfile($productId);

        if( $profileproduct->profileProductExists($productId, $profileId) === true ) {
            return false;
        } else {
            $profileproduct->setProductId($productId);
            $profileproduct->setProfileId($profileId);
            $profileproduct->save();
            return true;
        }
	}
	protected function _deleteProductFromProfile($productId)
	{
		try {
			$this->_objectManager->create("Ced\Ebay\Model\Profileproducts")
                ->deleteFromProfile($productId);
		} catch (\Exception $e) {
			throw $e;
			return false;
		}
		return true;
	}
    /* Identify unique Ebay attributes
   */
    function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if($val['delete']==1)
                continue;

            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}