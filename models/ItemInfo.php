<?php namespace components\simple_gallery\models; if(!defined('TX')) die('No direct access.');

class ItemInfo extends \dependencies\BaseModel
{
  
  protected static
  
    $table_name = 'simple_gallery__item_info',
  
    $relations = array(
      'Items' => array('item_id' => 'Items.id'),
      'Languages' => array('language_id' => 'Languages.id')
    );
    
}