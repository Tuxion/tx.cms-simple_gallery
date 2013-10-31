<?php namespace components\simple_gallery\models; if(!defined('TX')) die('No direct access.');

class Items extends \dependencies\BaseModel
{
  
  protected static
  
    $table_name = 'simple_gallery__items',
  
    $relations = array(
      'ItemInfo' => array('id' => 'ItemInfo.item_id'),
      'CategoriesToItems' => array('id' => 'CategoriesToItems.item_id'),
      'Images' => array('image_id' => 'Media.Images.id')
    );

  public function get_categories(){
    return tx('Sql')
      ->table('gallery', 'Categories')
      ->join('CategoriesToItems', $catlink)
      ->where("$catlink.item_id", $this->id)
      ->execute();
  }

  public function get_image(){
    return tx('Sql')
      ->table('media', 'Images')
      ->pk($this->file_id)
      ->execute_single();
  }

}
