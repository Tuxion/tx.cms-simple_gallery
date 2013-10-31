<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

class Helpers extends \dependencies\BaseComponent
{

  protected
    $permissions = array(
      'get_items' => 0
    );

  public function get_items($options = null){

    $options = Data($options);

    return
      $this->table('Items')
      ->join('ItemInfo', $info)
      ->select("$info.title", 'title')
      ->join('CategoriesToItems', $catlink)
      ->is($options->page_id->is('set')->and_not('empty'), function($q)use($options){
        $q
        ->join('Categories', $cat)->inner()
        ->workwith($cat)
        ->join('Galleries', $gallery)->inner()
        ->where("$gallery.page_id", $options->page_id);
      })
      ->is($options->category_id->is('set')->and_not('empty'), function($q)use($options){
        $q
        ->where("$catlink.category_id", $options->category_id);
      })
      ->execute();

  }

}
