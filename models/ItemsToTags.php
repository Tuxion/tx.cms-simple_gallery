<?php namespace components\simple_gallery\models; if(!defined('TX')) die('No direct access.');

class ItemsToTags extends \dependencies\BaseModel
{

  protected static

    $table_name = 'simple_gallery__items_to_tags',

    $relations = array(
      'Items' => array('item_id' => 'Items.id'),
      'Tags' => array('tag_id' => 'Tags.id')
    );

}