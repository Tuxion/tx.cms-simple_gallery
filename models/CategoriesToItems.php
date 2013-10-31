<?php namespace components\simple_gallery\models; if(!defined('TX')) die('No direct access.');

class CategoriesToItems extends \dependencies\BaseModel
{

  protected static

    $table_name = 'simple_gallery__categories_to_items',

    $relations = array(
      'Items' => array('item_id' => 'Items.id'),
      'Categories' => array('category_id' => 'Categories.id')
    );

}