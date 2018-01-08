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
  
  public function get_videos(){

    //file_id
    //id
    //dt_created
    //filename
    //thumbnail
    //full

    if($this->video_urls->get() == '') return;

    $lines = explode("\n", $this->video_urls);

    foreach($lines as $line){
      $vidId = str_replace('https://vimeo.com/', '', $line);
      $hash = unserialize(file_get_contents("https://vimeo.com/api/v2/video/$vidId.php"));
      $embed_url = str_replace('vimeo.com', 'player.vimeo.com/video', $hash[0]['url']);
      $arr[] = array_merge(
        $hash[0],
        array(
          'thumbnail' => $hash[0]['thumbnail_large'],
          'full' => $hash[0]['thumbnail_large'],
          'embed_url' => $embed_url,
          'embed_code' => '<iframe src="'.$embed_url.'" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
          'is_video' => true
        )
      );
    }
    
    return $arr;
    
  }

}
