<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

//Make sure we have the things we need for this class.
tx('Component')->check('update');
tx('Component')->load('update', 'classes\\BaseDBUpdates', false);

class DBUpdates extends \components\update\classes\BaseDBUpdates
{
  
  protected
    $component = 'simple_gallery',
    $updates = array(
      '1.0'=>'1.1'
    );

  public function update_to_1_1($current_version, $forced)
  {
    
    try{
      
      tx('Sql')->query('
        ALTER TABLE `#__simple_gallery__categories`
          ADD COLUMN `gallery_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
          ADD INDEX `gallery_id` (`gallery_id`);
      ');
      
      tx('Sql')->query('
        CREATE TABLE `#__simple_gallery__galleries` (
          `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `page_id` INT(10) UNSIGNED NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          INDEX `page_id` (`page_id`)
        )
        COLLATE=\'utf8_general_ci\'
        ENGINE=InnoDB;
      ');

    } catch(\Exception $ex) {
      if(!$forced) throw $ex;
    }
    
  }

  public function install_1_0($dummydata, $forced)
  {
    
    if($forced === true){
      tx('Sql')->query('DROP TABLE IF EXISTS `#__simple_gallery__categories_to_items`');
      tx('Sql')->query('DROP TABLE IF EXISTS `#__simple_gallery__category_info`');
      tx('Sql')->query('DROP TABLE IF EXISTS `#__simple_gallery__galleries`');
      tx('Sql')->query('DROP TABLE IF EXISTS `#__simple_gallery__items`');
      tx('Sql')->query('DROP TABLE IF EXISTS `#__simple_gallery__item_info`');
    }
    
    tx('Sql')->query('
      CREATE TABLE `#__simple_gallery__categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `lft` int(11) NOT NULL,
        `rgt` int(11) NOT NULL,
        `access_level` tinyint(3) NOT NULL DEFAULT \'0\',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ');
    
    tx('Sql')->query('
      CREATE TABLE `#__simple_gallery__categories_to_items` (
        `item_id` int(11) NOT NULL,
        `category_id` int(11) NOT NULL,
        KEY `item_id` (`item_id`,`category_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ');
    
    tx('Sql')->query('
      CREATE TABLE `#__simple_gallery__category_info` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `category_id` int(11) NOT NULL,
        `language_id` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` longtext,
        PRIMARY KEY (`id`),
        KEY `item_id` (`category_id`),
        KEY `language_id` (`language_id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ');
        
    tx('Sql')->query('
      CREATE TABLE `#__simple_gallery__items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `dt_created` datetime NOT NULL,
        `dt_modified` datetime DEFAULT NULL,
        `file_id` int(11) DEFAULT NULL,
        `filename` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ');
        
    tx('Sql')->query('
      CREATE TABLE `#__simple_gallery__item_info` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `item_id` int(11) NOT NULL,
        `language_id` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` longtext NOT NULL,
        PRIMARY KEY (`id`),
        KEY `item_id` (`item_id`),
        KEY `language_id` (`language_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ');
    
    //Queue self-deployment with CMS component.
    $this->queue(array(
      'component' => 'cms',
      'min_version' => '1.2'
      ), function($version){
          
          //Look for the component in the CMS tables.
          $component = tx('Sql')
            ->table('cms', 'Components')
            ->where('name', "'menu'")
            ->limit(1)
            ->execute_single()
            
            //If it's not there, create it.
            ->is('empty', function(){
              
              return tx('Sql')
                ->model('cms', 'Components')
                ->set(array(
                  'name' => 'simple_gallery',
                  'title' => 'Simple gallery component'
                ))
                ->save();
              
            });
          
          //Look for the gallery view.
          tx('Sql')
            ->table('cms', 'ComponentViews')
            ->where('com_id', $component->id)
            ->where('name', "'gallery'")
            ->limit(1)
            ->execute_single()
            
            //If it's not there, create it.
            ->is('empty', function()use($component){
              
              $view = tx('Sql')
                ->model('cms', 'ComponentViews')
                ->set(array(
                  'com_id' => $component->id,
                  'name' => 'gallery',
                  'tk_title' => 'GALLERY_VIEW_TITLE',
                  'tk_description' => 'GALLERY_VIEW_DESCRIPTION',
                  'is_config' => '0'
                ))
                ->save();
              
            });
          
        }); //END - Queue CMS 1.2+
    
  }
  
}
