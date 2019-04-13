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

class Index extends \Magento\Backend\App\Action
{
	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */

    public $resultPageFactory;

    /**
     * Index constructor.
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
     */
	

	public function _construct(
			\Magento\Framework\Message\ManagerInterface $messageManager
				)
	{
		$this->messageManager = $messageManager;
		}

	/**
     * Index Action 
	 * @return \Magento\Backend\Model\View\Result\Page
     */

	public function execute()
	{
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Ced_Jet::jet_return');
		$resultPage->addBreadcrumb(__('Add Return'), __('Jet Return'));
		$resultPage->getConfig()->getTitle()->prepend(__('Jet Return'));
		return $resultPage;
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
