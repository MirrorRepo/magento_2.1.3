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
namespace Ced\Jet\Controller\Adminhtml\Refund;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action;

class ExportRefundCsv extends \Magento\Backend\App\Action
{

	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */

    public $resultPageFactory;
    /**
     * @var
     */
    public $fileFactory;

    /**
     * ExportRefundCsv constructor.
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

	public function execute() {
		$heading = ['Refund Id','Magento Order Id','Refund Order Id','Refund Ststus'];
		$outputFile = "refundorders.csv";
		$handle = fopen($outputFile, 'w');
		fputcsv($handle, $heading);
	
		$this->downloadCsv($outputFile);
	}
	/**
	* @return void
	*/
	public function downloadCsv($file) {
		if (file_exists($file)) {
			//set appropriate headers
			header('Content-Description: File Transfer');
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename='.basename($file));
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();flush();
			readfile($file);
		}
	}
	
	/**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
	public function _isAllowed()
	{
		return true;
	}
}
