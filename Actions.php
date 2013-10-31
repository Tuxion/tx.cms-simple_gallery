<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

class Actions extends \dependencies\BaseComponent
{

  protected function save_item($data)
  {

    tx($data->id->get('int') > 0 ? 'Updating a gallery item.' : 'Adding a new gallery item', function()use($data){
      
      //Update.
      if($data->id->get('int') > 0)
      {
      
        $item = tx('Sql')->table('simple_gallery', 'Items')->pk($data->id)->execute_single()->is('empty', function()use($data){
          throw new \exception\User('Could not update because no entry was found in the database with id %s.', $data->id);
        })
        ->merge($data->having('category_id'))
        ->push('datetime_modified', date("Y-m-d H:i:s"))
        ->save();

        $item_id = $item->id->get('int');
        
        tx('Sql')->table('simple_gallery', 'ItemInfo')->where('item_id', $item->id)->execute_single()->is('empty')
          ->success(function()use($data, $item){
            tx('Sql')->model('simple_gallery', 'ItemInfo')->merge($data->having('title', 'description'))->merge($item->having(array('item_id'=>'id')))->merge(array('dt_created' => date("Y-m-d")))->save();
          })
          ->failure(function($item_info)use($data){
            $item_info->merge($data->having('title', 'description'))->save();
          });

        //Save item categories.
        if(count($data->category_id->as_array()) > 0)
        {
          //Delete all existing links to categories.
          tx('Sql')->execute_non_query("
            DELETE FROM #__simple_gallery__categories_to_items
            WHERE item_id = ".$item_id."
          ");

          //Save category-item link.
          $data->having('category_id')->each(function($category_id)use($item_id){

            $item_categories = $category_id->as_array();
            foreach($item_categories as $cat)
            {

              tx('Sql')->execute_non_query("
                INSERT INTO #__simple_gallery__categories_to_items
                (item_id, category_id)
                VALUES (".$item_id.", ".$cat.")
              ");
              
            }

          });
        }
        
        //save item tags
        //delete all existing links to tags from db
        tx('Sql')->execute_non_query("
          DELETE FROM tx__simple_gallery__tags_to_items
          WHERE item_id = ".$item_id."
        ");
        
      }
      
      //or insert
      else{
        
        $item = tx('Sql')->model('simple_gallery', 'Items')->set($data)->merge(array(
          'datetime_created'  => date("Y-m-d H:i:s")
        ))->save();
        
        $item_id = mk('Sql')->get_insert_id();
        
        //save category-item link
        tx('Sql')->execute_non_query("
          INSERT INTO #__simple_gallery__categories_to_items
          (item_id, category_id)
          VALUES (".$item_id.", ".$data->category_id.")
        ");

        die($item_id);
        
      }
      
    })
    
    ->failure(function($info){
      tx('Session')->new_flash('error', $info->get_user_message());
    });
    
    tx('Url')->redirect(url('item_id=NULL'));
  }

  protected function item_delete($data)
  {
    
    $item = tx('Sql')->table('simple_gallery', 'Items')->pk($data->item_id)->execute_single()->is('empty', function()use($data){
      throw new \exception\User('Could not delete this item, because no entry was found in the database with id %s.', $data->id);
    })
    ->delete();

  }

  protected function save_gallery($data)
  {

    tx($data->id->get('int') > 0 ? 'Updating a gallery.' : 'Adding a new gallery', function()use($data){

      tx('Sql')->table('simple_gallery', 'Galleries')->pk($data->id)->execute_single()->is('empty')
        ->success(function($info)use($data){
          tx('Sql')->model('simple_gallery', 'Galleries')->set($data->having('page_id'))->save();
        })
        ->failure(function($info)use($data){
          $info->merge($data->having('page_id'))->save();
        });
    
    })
    
    ->failure(function($info){
      throw $info->exception;
    });
    
    exit;

  }

  protected function save_category($data)
  {
    
    tx($data->id->get('int') > 0 ? 'Updating a category.' : 'Adding a new category', function()use($data, &$category){
      
      //Update.
      if($data->id->get('int') > 0)
      {
        
        //Check if category exists in database.
        $category = tx('Sql')->table('simple_gallery', 'Categories')->pk($data->id)->execute_single()->is('empty', function()use($data){
          throw new \exception\User('Could not update because no entry was found in the database with id %s.', $data->id);
        })
        ->merge($data->having('access_level'))
        ->save();
        
        //Only existing categories can have subcategories, so use recursion if requested.
        $data->force_original_recursive->gt(0, function()use($data, $category){
          tx('Sql')
            ->table('simple_gallery', 'Categories')
            ->where('lft', '>', $category->lft)
            ->where('rgt', '<', $category->rgt)
            ->execute()
            ->each(function($subcat)use($data){
              $subcat->save();
            });
        });
        
        //Update category info.
        tx('Sql')->table('simple_gallery', 'CategoryInfo')->where('category_id', $category->id)->execute_single()->is('empty')
          ->success(function($category_info)use($data, $category){
            tx('Sql')->model('simple_gallery', 'CategoryInfo')->set($data->having('title', 'description')->merge($category->having(array('category_id'=>$category->id))))->save();
          })
          ->failure(function($category_info)use($data){
            $category_info->merge($data->having('title', 'description'))->save();
          });
        
      }
      
      //Or insert.
      else{
        
        $category = tx('Sql')->model('simple_gallery', 'Categories')->set($data->having('gallery_id', 'access_level'))->hsave(null, 0);
        $category_info = tx('Sql')->model('simple_gallery', 'CategoryInfo')->set($data->having('title', 'description')->merge(array('category_id'=>$category->id)))->save();
        
        // trace($category->dump(), $category_info->dump());
        // exit;
        
      }
      
    })
    
    ->failure(function($info){
      throw $info->exception;
      tx('Session')->new_flash('error', $info->get_user_message());
    });
    
    tx('Url')->redirect(url('section=simple_gallery/item_list', true));
    
  }

  protected function category_delete($data)
  {
    //delete category
    $cat = tx('Sql')->table('simple_gallery', 'Categories')->pk($data->category_id)->execute_single()
    ->is('empty', function()use($data){
      throw new \exception\User('Could not delete this category, because no entry was found in the database with id %s.', $data->id);
    })
    ->hdelete();

    //delete category info
    //TODO:
    tx('Sql')
      ->table('simple_gallery', 'CategoryInfo')
      ->where('category_id', $data->category_id)
      ->execute()
      ->each(function($row){
        $row->delete();
      });
    
    //disconnect items from deleted category
    // $cat =
    //   tx('Sql')
    //   ->table('simple_gallery', 'CategoriesToItems')
    //   ->where('category_id', $data->category_id)
    //   ->execute()
    //   ->each(function($row){
    //     $row->delete();
    //   });

  }

  protected function category_lock($data)
  {
    
    $that = $this;
    
    return;
    
    tx('Locking categories.', function()use($data, $that){
    
      $data->cat->validate('Category #ID', array('number'));
      
      $that->table('Categories')->is($data->cat->is_set(), function($q)use($data){$q->pk($data->id);})->execute()->each(function($v){
        
        $v->locked->set(true)->back()->save();
        
      });
      
    });
    
  }
  
}
