<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

class Json extends \dependencies\BaseViews
{

  protected
    $permissions = array(
      'get_category' => 0,
      'get_categories' => 0
    );

  /**
   * Get a page's timeline filters, or resort to the defaults.
   */
  protected function get_gallery($data, $params)
  {
    
    return tx('Sql')
      ->table('simple_gallery', 'Galleries')
      ->where('page_id', $params->{0})
      ->execute_single()
      ->otherwise(array(
        'page_id' => $params->{0}->get('int'),
        'id'=>'NEW'
      ));
    
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
      ->order('lft')
      ->execute();
    
  }

  protected function update_categories_hierarchy($data, $params)
  {
    
    $data->questions->each(function($q){
      
      tx('Sql')->model('wizard', 'Categories')->merge($q->having(array(
        'id' => 'item_id',
        'lft' => 'left',
        'rgt' => 'right'
      )))
      
      ->save();
      
    });
    
    return $this->get_questions(Data(), $params);
    
  }



}
