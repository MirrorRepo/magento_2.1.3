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

use Magento\Backend\App\Action;

/**
 * massDelete category Controller
 */

class massDelete extends \Magento\Backend\App\Action

{
	/**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Ced_Jet::view_failed_imported_jet_orders_log';
    
	/**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */

	public function execute()
	{
		$data = $this->getRequest()->getParam('id');
		if (isset($data)) {
			$logDeleted = 0;

			foreach ($data as $val) {
				$model = $this->_objectManager->create('Ced\Jet\Model\Failedorders')->load($val)->delete();
				$logDeleted++;
			}
			$this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $logDeleted));
			$this->_redirect('jet/failed/orders');
		}
	}
}  
