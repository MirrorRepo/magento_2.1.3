<?php
namespace Paper\News\Api;

interface AllnewsRepositoryInterface
{
	public function save(\Paper\News\Api\Data\AllnewsInterface $news);

    public function getById($newsId);

    public function delete(\Paper\News\Api\Data\AllnewsInterface $news);

    public function deleteById($newsId);
}
?>
