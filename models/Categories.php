<?php namespace components\simple_gallery\models; if(!defined('TX')) die('No direct access.');

class Categories extends \dependencies\BaseModel
{
  
  protected static
  
    $table_name = 'simple_gallery__categories',
  
    $relations = array(
      // 'Galleries' => array('gallery_id' => 'Galleries.id'),
      'CategoryInfo' => array('id' => 'CategoryInfo.category_id'),
      'CategoriesToItems' => array('id' => 'CategoriesToItems.category_id')
    ),

    $hierarchy = array(
      'left' => 'lft',
      'right' => 'rgt'
    );

  public function get_items(){
    return $this
      ->table('Items')
      ->join('ItemInfo', $info)
      ->select("$info.title", 'title')
      ->select("$info.description", 'description')
      ->join('CategoriesToItems', $catlink)
      ->where("$catlink.category_id", $this->id)
      ->execute();
  }

}