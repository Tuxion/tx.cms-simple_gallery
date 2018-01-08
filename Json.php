<?php namespace components\simple_gallery; if(!defined('MK')) die('No direct access.');

class Json extends \dependencies\BaseViews
{

  protected
    $permissions = array(
      'get_gallery' => 0
    );

  /**
   * Get a page's timeline filters, or resort to the defaults.
   */
  protected function get_gallery($data, $params)
  {
    
    // Fit 440x440 = retina thumbnail
    // Fill 1440x## = retina full
    
    $gallery = mk('Sql')
      ->table('simple_gallery', 'Galleries')
      ->where('id', $params->{0})
      ->execute_single();
    
    $categories = mk('Sql')
      ->table('simple_gallery', 'Categories', $C)
      ->join('CategoryInfo', $CI)
      ->select("$CI.title", 'title')
      ->where("$C.gallery_id", $gallery->id)
      ->order('lft', 'ASC')
      ->execute();
    
    // Map the ordered category ID's to a property on the gallery.
    $gallery->merge(array(
      'categories'=>$categories->map(function($cat){ return $cat->id->get('int'); })
    ));
    
    // Collect all the images from all galleries.
    $images = array();
    foreach($categories as $category){
      
      $videos = $category->videos;

      // Fetch the items and merge it with the collection.
      $categoryImages = $category->get_items();
      $categoryImages = $categoryImages->merge($videos->get('array'));
      $images = array_merge($images, $categoryImages->get('array'));

      // Map the items to be listed as ID's in a property.
      $category->merge(array(
        'images' => $categoryImages->map(function($img){ return $img->id->get('int'); })
      ));

      // $category->images->merge($videos->get('array'));
      
    }
    
    // Process the images to add their URL's.
    foreach($images as $item){
      if($item->is_video->get('bool') != true){
        $image = $item->get_image();
        $item->merge(array(
          'thumbnail' => (string)$image->generate_url(array('fill_width'=>440, 'fill_height'=>440)),
          'full' => (string)$image->generate_url(array( 'resize_width'=>(1440/2) ))
        ));
      }
    }
    
    return array(
      'gallery'=>$gallery,
      'categories'=>$categories,
      'images'=>$images
    );
    
  }

  protected function get_categories($options, $params)
  {
    
    $params->{0}->is('set', function($gid)use($options){
      $options->merge(array(
        'gallery_id' => $gid
      ));
    });
    
    $options = $options->having('gallery_id')
      ->gallery_id->validate('Gallery ID', array('number'=>'integer', 'gt'=>0))->back()
    ;
    
    return tx('Sql')
      ->table('simple_gallery', 'Categories')
      ->is($options->gallery_id->is_set(), function($q)use($options){
        $q->where('gallery_id', $options->gallery_id);
      })
      ->order('lft', 'ASC')
      ->execute();
    
  }

}
