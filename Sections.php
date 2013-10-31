<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

class Sections extends \dependencies\BaseViews
{

  protected
    $permissions = array(
      'category_list' => 0,
      'categories' => 0,
      'item_list' => 2,
      'json_load_items' => 0,
      'image' => 0,
      'get_items' => 0,
      'get_item' => 0
    );

  /* frontend */

  protected function category_list()
  {

    //Get page ID.
    $page_id = tx('Data')->get->pid;

    if($page_id->is_empty()){
      $arr_url = explode('/', tx('Data')->get->rest);
      $page_id = $arr_url[count($arr_url)-1];
    }

    //Get gallery info.
    $gallery = $this->table('Galleries')->where('page_id', $page_id)->execute_single();

    return
      $this
      ->table('Categories')
      ->where('gallery_id', $gallery->id)

      ->where(tx('Sql')->conditions()
        ->add('1', array('access_level', '<=', tx('Account')->user->level))
        ->add('2', array('access_level', 'NULL'))
        ->combine('3', array('1', '2'), 'OR')
        ->utilize('3'))

      ->add_hierarchy()
      ->join('CategoryInfo', $ci)
      ->select("$ci.title", 'title')
      ->execute();
  }

  protected function categories($options)
  {
    return $this->section('get_categories', $options);
  }

  protected function item_list()
  {
    
    return array(
      
      'all_items' => $this->table('Items', $i)
        ->join('ItemInfo', $ii)->left()
        ->join('CategoriesToItems', $catlink)->left()
        ->group('id')
        ->select("$ii.title", 'title')
        ->where("$catlink.category_id", tx('Data')->get->category_id)
        ->execute(),
      
      'category_info' => $this->table('Categories')
        ->join('CategoryInfo', $ci)
        ->select("$ci.title", 'title')
        ->select("$ci.id", 'category_id')
        ->where('id', tx('Data')->get->category_id)
        ->execute_single(),
      
      'categories' => $this->table('Categories')
        ->join('CategoryInfo', $ci)
        ->select("$ci.title", 'title')
        ->order('lft')
        ->execute(),

      'image_uploader' => 
        tx('Component')->available('media') ?
          tx('Component')->modules('media')->get_html('image_uploader', array(
            'insert_html' => array(
              'header' => '',
              'drop' => 'Sleep de afbeelding(en).',
              'upload' => 'Uploaden',
              'browse' => 'Bladeren'
            ),
            'auto_upload' => true,
            'callbacks' => array(
              'ServerFileIdReport' => 'plupload_image_file_id'
            )))
        : 'Invalid site configuration: media component is not available.'
      
    );
    
  }
  
  protected function item_edit()
  { 

    return array(
      'item' => $this->table('Items')->join('ItemInfo', $ii)->left()->select("$ii.title", 'title')->select("$ii.description", 'description')->pk(tx('Data')->get->item_id)->execute_single(),
      'item_categories' => $this->table('Categories', $c)->join('CategoriesToItems', $cti)->inner()->join('CategoryInfo', $ci)->select("$ci.title", 'title')->where("$cti.item_id", tx('Data')->get->item_id)->execute()->map(function($row){return $row->id;})->as_array(),
      'categories' => $this->table('Categories')->join('CategoryInfo', $ci)->select("$ci.title", 'title')->order('lft')->execute()
    );
    
  }
  
  protected function category_edit()
  { 

    return array(

      'gallery' => $this->table('Galleries')
        ->where('page_id', tx('Data')->get->pid)
        ->execute_single(),

      'item' => $this->table('Categories')
        ->join('CategoryInfo', $ci)->left()
        ->select("$ci.title", 'title')
        ->select("$ci.description", 'description')
        ->pk(tx('Data')->get->category_id)
        ->execute_single()
        ->not('empty', function($item){
          $item->child_count->set(
            tx('Sql')
              ->table('simple_gallery', 'Categories')
              ->where('lft', '>', $item->lft)
              ->where('rgt', '<', $item->rgt)
              ->count()
          );
        })

    );
    
  }

  protected function json_load_items($options)
  {
#    ini_set("memory_limit", -1);//tmp
    
    //handle options
    $options = $this->filters()->having('cat', 'collection', 'page', 'page_size', 'search', 'tags');
    
    $options = $options->merge($options)
      ->page_size
        ->set(function($page_size){
          return $page_size->is_empty() ? 32 : $page_size->get('int');
        })
        ->validate('Item per page', array('number'))->back()
      ->tags
        ->not('empty', function($tags){
          $tags->convert('split', ',');
        })->back();
    
    $options->search = str_replace(',', '', $options->search);
    $options->search = str_replace(' ', '|', $options->search);

    //build query
    $query = $this->table('Items', $i)
      ->select('UNIX_TIMESTAMP(`dt_created`)', 'created')
      ->join('ItemInfo', $ii)->left()
      ->select("$ii.title", 'title')
      ->select("$ii.description", 'description')
      ->where(tx('Sql')->conditions()
        ->add('1', array("$ii.language_id", LANGUAGE))
        ->add('2', array("$ii.language_id", 'NULL'))
        ->add('3', array("$ii.language_id", 0))
        ->combine('4', array('1', '2', '3'), 'OR')
        ->utilize('4'))
      ->order('dt_created', 'DESC')
      ->group("$i.id")
      ->is($options->search->is('set')->and_not('empty'), function($q)use($options, $ii, $i){
        $q
        ->join('TagsToItems', $taglink)->left()
        ->workwith($taglink)
        ->join('Tags', $tag)
        ->workwith($i)
        ->where(tx('Sql')->conditions()
          ->add('1', array("$tag.title", '', "REGEXP('".$options->search."')" ))
          ->add('2', array("$ii.title", '', "REGEXP('".$options->search."')" ))
          ->add('3', array("$ii.description", '', "REGEXP('".$options->search."')" ))
          ->add('4', array("$i.name", '', "REGEXP('".$options->search."')" ))
          ->combine('combined', array('1', '2', '3', '4'), 'OR')
          ->utilize('combined'));
      })

      ->is($options->cat->is('set')->and_is('numeric'), function($q)use($options, $i){
        //get selected category
        $cat = tx('Sql')
          ->table('gallery', 'Categories')
          ->pk($options->cat)
          ->execute_single();

        $cats = tx('Sql')
          ->table('gallery', 'Categories')
          ->where('lft', '>=', $cat->lft)
          ->where('rgt', '<=', $cat->rgt)
          ->execute();
        
        $catIds = $cats->map(function($node){
          return $node->id;
        });
        
        //resume query with join
        $q
          ->select($cat->force_original->otherwise(0), 'force_original')
          ->join('CategoriesToItems', $catlink)
          ->where("$catlink.category_id", $catIds);
        
      })->failure(function($q)use($options, $i){
        
        if(tx('Account')->user->level->get('int') <= 0){
          $q->join('CategoriesToItems', $catlink)
          ->workwith($catlink)
          ->join('Categories', $cat)
          
          ->where(tx('Sql')->conditions()
            ->add('1', array("$cat.access_level", '<=', tx('Account')->user->level))
            ->add('2', array("$cat.access_level", 'NULL'))
            ->combine('3', array('1', '2'), 'OR')
            ->utilize('3'))
          
          ->workwith($i);
        }
        
      })
      ->is($options->collection->is('set')->and_is('numeric'), function($q)use($options){
        $q
          ->join('CollectionsToItems', $collection_link)
          ->where("$collection_link.collection_id", $options->collection);
      });
    
    //execute query to get total items found
    $pages = $query->count()->get('int');
    tx('Logging')->log('Count', 'Found', $pages);
    
    //add LIMIT to query based on page number
    $items = $query->is($options->page->not('empty'))
      ->success(function($q)use($options){
        $q->limit($options->page_size, $options->page_size->get('int') * ($options->page->get('int') -1));
      })
      ->failure(function($q){
        $q->limit(500);
      })
    
    //execute new query to get all items
    ->execute()
      
      // ask model to fill tags
      ->each(function($item){$item->tags;})
      
      // filter on tags
      ->is($options->tags->not('empty'), function($items)use($options){
        $items->convert('filter', function($item)use($options){
          $found = true;
          $options->tags->each(function($tag)use(&$found, $item){
            if($item->tags->map(function($tag){return $tag->title;})->keyof($tag->get()) === false){
              $found = false;
              return false;
            }
          });
          return $found;
        });
      });
    
    header('Content-type: application/json');
    
    return Data(array(
   
      'total_pages' => ceil( $pages / $options->page_size->get('int') ),
      
      'total_items' => $items->size(),
      
      'items' => $items
        
    ))->as_json();
    
  }

  protected function json_update_categories()
  {
    
    $return = tx('Updating categories', function(){
    
      return tx('Data')->post->categories->each(function($cat){
        
        tx('Sql')->model('simple_gallery', 'Categories')->merge($cat->having(array(
          'id' => 'item_id',
          'lft' => 'left',
          'rgt' => 'right'
        )))
        
        ->save();
        
      });
    
    });
    
    return Data(array(
      
      'return' => $return->return_value,
      
      'success' => $return->success(),
      
      // 'message' => $return->get_user_message()
      'message' => __('Updating was succesful', 1)
      
    ))->as_json();
    
  }

  protected function image()
  {
    
    //get the image
    $img = tx('File')->image(data_of($img))->use_cache();
    $options = Data($options)->as_array();

    //resize?
    if(array_key_exists('resize', $options) && is_array($options['resize'])){
      $width = (array_key_exists('width', $options['resize']) ? $options['resize']['width'] : 0);
      $height = (array_key_exists('height', $options['resize']) ? $options['resize']['height'] : 0);
      $img->resize($width, $height);
    }

    //crop?
    if(array_key_exists('crop', $options) && is_array($options['crop'])){
      $x = (array_key_exists('x', $options['crop']) ? $options['crop']['x'] : 0);
      $y = (array_key_exists('y', $options['crop']) ? $options['crop']['y'] : 0);
      $width = (array_key_exists('width', $options['crop']) ? $options['crop']['width'] : 0);
      $height = (array_key_exists('height', $options['crop']) ? $options['crop']['height'] : 0);
      $img->crop($x, $y, $width, $height);
    }
    
    //output or download to client
    if(array_key_exists('download', $options) && $options['download'] == true){
      $img->download();
    }else{
      $img->output();
    }
    
  }

  private function get_items($data)
  {
    
    $that = $this;
    
    return tx('Fetching gallery items.', function()use($data, $that){
      
      $items = $that->table('Items')
        ->is($data->category_id->is('set')->and_not('empty'), function($q)use($data){
          $q
            ->join('CategoriesToItems', $catlink)
            ->where("$catlink.category_id", $data->category_id);
        })
        ->execute()
        ->is('empty', function(){
          throw new \exception\EmptyResult('Could not find gallery items.');
        });
      
      return $items;
      
    });
    
  }
    
  private function get_item($data)
  {
    
    $that = $this;
    
    return tx('Fetching gallery item.', function()use($data, $that){
      
      $item = $that->table('Items')
        ->join('ItemInfo', $ii)
        ->select("$ii.title", 'title')
        ->select("$ii.description", 'description')
        ->pk($data->item_id)
        ->execute_single()
        ->is('empty', function(){
          throw new \exception\EmptyResult('Could not find gallery item.');
        });
      
      return $item;
      
    });
    
  }
  
}
