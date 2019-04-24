<?php
class Category
{
	public $categoryData;

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
}