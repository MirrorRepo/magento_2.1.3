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
namespace Ced\Jet\Controller\Adminhtml\OrderReturn;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Fetchreturn extends \Magento\Backend\App\Action
{
	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */

    public $resultPageFactory;

    /**
     * Fetchreturn constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
	
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
    	parent::__construct($context);
    	$this->resultPageFactory = $resultPageFactory;    
    }

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @return void
     */

	public function _construct(
			\Magento\Framework\Message\ManagerInterface $messageManager
			)
	{
		$this->messageManager = $messageManager;
	}
	
	/**
     * Fetch Order Return page 
	 * @return \Magento\Backend\Model\View\Result\Page | \Magento\Backend\Model\View\Result\Redirect
     */

	public function execute()
	{	
		if ($this->_objectManager->get('Ced\Jet\Helper\Order')->jetreturn()) {
			$this->messageManager->addSuccess('Jet Return Fetched');
			$this->_redirect('jet/orderreturn/index');
		} else {
			$this->messageManager->addError('Error in Fetching Return');
			$this->_redirect('jet/orderreturn/index');
		}
	}
	/**
     * Check admin permissions for this controller
     *
     * @return boolean
     */

	public function _isAllowed()
	{
		return $this->_authorization->isAllowed('Ced_Jet::jet_return');
	}
}
