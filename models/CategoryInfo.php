<?php namespace components\simple_gallery\models; if(!defined('TX')) die('No direct access.');

class CategoryInfo extends \dependencies\BaseModel
{
  
  protected static
  
    $table_name = 'simple_gallery__category_info',
  
    $relations = array(
      'Categories' => array('category_id' => 'Categories.id'),
      'Languages' => array('language_id' => 'Languages.id')
    );
    
}