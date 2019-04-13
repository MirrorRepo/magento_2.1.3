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

namespace Ced\Jet\Controller\Adminhtml\Failed;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Orders extends \Magento\Backend\App\Action
{
  /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Orders constructor.
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
     * @return \Magento\Backend\Model\View\Result\Page
     */
	public function execute()
	{
			$resultPage = $this->resultPageFactory->create();
			$resultPage->setActiveMenu('Ced_Jet::view_failed_imported_jet_orders_log');
			$resultPage->getConfig()->getTitle()->prepend(__('Failed Jet Orders Import Log'));
			return $resultPage;
	}
}
