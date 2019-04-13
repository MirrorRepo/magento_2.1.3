<?php
namespace Paper\News\Controller\Adminhtml\AllNews;

class Index extends \Magento\Backend\App\Action
{
	protected $allNewsFactory;
	
	protected $resultPageFactory;
	
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Paper\News\Model\AllnewsFactory $allNewsFactory
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->allNewsFactory = $allNewsFactory;
	}

	public function execute()
	{
		$allnews = $this->allNewsFactory->create();
		$newsCollection = $allnews->getCollection();
		
		// echo '<pre>';print_r($newsCollection->getData());
		
		$resultPage = $this->resultPageFactory->create();
		return $resultPage;
	}
}
?>

