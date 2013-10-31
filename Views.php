<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

class Views extends \dependencies\BaseViews
{

  protected
    $permissions = array(
      'gallery' => 0
    );

  protected function gallery($options)
  {
    
    //Get page ID.
    $page_id = tx('Data')->get->pid;

    if($page_id->get() <= 0){
      $arr_url = explode('/', tx('Data')->get->rest);
      $page_id = $arr_url[count($arr_url)-1];
    }

    //Get gallery info.
    $gallery = $this->table('Galleries')->where('page_id', $page_id)->execute_single();

    return array(
      'gallery' => $gallery,
      'items' => tx('Component')->helpers('simple_gallery')->get_items(),
      'categories' => $this->table('Categories')->add_absolute_depth('depth')->join('CategoryInfo', $info)->select("$info.title", 'title')->where('gallery_id', $gallery->id)->order('lft')->execute(),
      'category_list' => $this->section('category_list', $options)
    );
    
  }

}
