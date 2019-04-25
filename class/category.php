<?php
class Category
{
  public $categoryData;
  private $exceptParentCategories = array('50007053', '50007054', '50007057', '50007061', '50007058', '50007059', '9999');

	public function __construct()
	{
		$this->categoryData = json_decode(file_get_contents('../data/category.json'), true);
  }

  public function getAllChildCategoriesById($categoryId, $categories = array())
  {
    if ($categoryId == 0) return 0;                

    if (empty($this->categoryData['categoryDepthId'][$categoryId])) {            
      $categories[] = $categoryId;
    } else {
      for ($i = 0; $i < sizeof($this->categoryData['categoryDepthId'][$categoryId]); $i++) {
        $categories = $this->getAllChildCategoriesById($this->categoryData['categoryDepthId'][$categoryId][$i], $categories);
      }
    }

    return $categories;
  }

  public function getExceptCategories()
  {
    $exceptCategoryAll = array();

    foreach ($this->exceptParentCategories as $value) {
        $exceptCategoryAll = array_merge($exceptCategoryAll, $this->getAllChildCategoriesById($value));
    }

    return $exceptCategoryAll;
  }
}