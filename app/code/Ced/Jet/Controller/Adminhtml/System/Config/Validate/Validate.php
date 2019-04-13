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
namespace Ced\Jet\Controller\Adminhtml\System\Config\Validate;

use Magento\Framework\Controller\Result\JsonFactory;

class Validate extends \Magento\Backend\App\Action
{
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check whether vat is valid
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
    	$secretKey= $this->getRequest()->getParam('secretKey');
    	$userId= $this->getRequest()->getParam('userId');
    	$response = \Magento\Framework\App\ObjectManager::getInstance()->get('Ced\Jet\Helper\Data')->JrequestTokenCurl($secretKey,$userId);
    	
    	if (is_object($response) && isset($response->id_token)){
    		$valid = 1;
    		$message= "successfully validated details.";
    	} else {
    		$valid = 0;
    		$message= "validation error";
    	}
    	/** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'valid' => $valid,
            'message' => $message,
        ]);
    }
}
