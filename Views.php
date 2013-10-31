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
    
    if(mk('Data')->get->cid->is_set()){
      $categories = mk('Sql')->table('simple_gallery', 'Categories')
        ->pk(mk('Data')->get->cid)
        ->join('CategoryInfo', $info)
        ->select("$info.title", 'title')
        ->where('gallery_id', $gallery->id)
        ->execute();
    }else{
      $categories = $this->table('Categories')
        ->add_absolute_depth('depth')
        ->join('CategoryInfo', $info)
        ->select("$info.title", 'title')
        ->where('gallery_id', $gallery->id)
        ->order('lft')
        ->execute();
    }
    
    return array(
      'gallery' => $gallery,
      'in_category' => mk('Data')->get->cid->is_set(),
      'items' => tx('Component')->helpers('simple_gallery')->get_items(),
      'categories' => $categories,
      'category_list' => $this->section('category_list', $options)
    );
    
  }

}
