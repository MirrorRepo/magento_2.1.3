<?php
/**
 * CedCommerce
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the Academic Free License (AFL 3.0)
  * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
  * It is also available through the world-wide-web at this URL:
  * http://opensource.org/licenses/afl-3.0.php
  *
  * @category    Ced
  * @package     Ced_Ebay
  * @author      CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license     http://cedcommerce.com/license-agreement.txt
  */

namespace Ced\Ebay\Controller\Adminhtml\Profile;
 
class Delete extends \Magento\Customer\Controller\Adminhtml\Group
{
	protected $_objectManager;
	
	protected $_session;

	/**
	 * Delete the Attribute
	 */
	public function execute()
    {
        $code = $this->getRequest()->getParam('pcode');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($code) {
            $model = $this->_objectManager->create('Ced\Ebay\Model\Profile')->getCollection()->addFieldToFilter('profile_code', $code);


            // entity type check
            try {
              foreach ($model as $value) {
                if($code == $value->getData('profile_code')){
                    $value->delete();
                }
              }
                $this->messageManager->addSuccess(__('You deleted the profile.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath(
                    'ebay/profile/edit',
                    ['pcode' => $this->getRequest()->getParam('pcode')]
                );         
                //End
            }
        }
        $this->_redirect('ebay/profile/index');
          return ;
    }
}

