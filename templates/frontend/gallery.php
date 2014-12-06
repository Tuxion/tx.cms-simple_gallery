<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

if($data->gallery->flux_app->is_true()){
  
  echo '<div id="simple-gallery-controller">Loading...</div>';
  echo load_plugin('simple_gallery_flux');
  
  ?>
  
  <script type="text/javascript">
    
    // Replace content wrapper.
    var $main = jQuery('<div id="simple-gallery-controller">');
    jQuery('.content .col3.float-left .block.text').replaceWith($main);
    jQuery('.content .col3.float-left').removeClass('col3').addClass('col4');
    jQuery('.main-wrapper .content .left').remove();
    
    // Render the main view.
    React.render(
      React.createElement(SimpleGalleryFlux.MainController),
      $main[0]
    );
    
    var $rightButton = jQuery('<div id="right-button-controller">');
    
    // Render side bar.
    React.render(
      React.createElement(SimpleGalleryFlux.RightButtonController),
      $rightButton[0]
    );
    
    // Load required data.
    SimpleGalleryFlux.Tasks.initializeGallery(
      <?php echo $data->gallery->id; ?>,
      <?php echo $data->cid->otherwise('null'); ?>,
      <?php echo $data->iid->otherwise('null'); ?>
    );
    
    jQuery(function($){
      jQuery('#mc_embed_signup').hide();
      jQuery('.main-wrapper > .content').prepend($rightButton);
    });
    
  </script>
  
  <?php
  
}

else{
  
  echo load_plugin('colorbox');
  
  ?>

  <?php $data->categories->each(function($cat)use($data){ ?>
    
    <div class="<?php if(!$data->in_category->is_true()) echo 'com-simple_gallery--category-preview'; ?>">
      <a href="<?php echo $data->in_category->is_true() ? '#' : url('?cid='.$cat->id); ?>">
        <h1><?php echo $cat->title; ?></h1>
      </a>
      <a class="com-simple_gallery--go-back" href="<?php echo url('cid=NULL'); ?>">&lt; Back</a>
      <div class="clip-images clearfix">
        <ul class="com-simple_gallery--item-list clearfix">
          <?php
            $i=0;
            $cat->items->each(function($item)use($cat, $data, &$i){
              if($i>9 && !$data->in_category->is_true())
                return;
              $i++;
            ?>
            
            <li>
              <a title="<?php echo $item->title.($item->description->get('bool') ? ' - '.$item->description : ''); ?>" rel="cat-<?php echo $cat->id; ?>" target="_blank" href="<?php echo $item->image->generate_url(array('resize_width' => 1600)); ?>">
                <img src="<?php echo $item->image->generate_url(array('resize_height' => 100)); ?>">
              </a>
              <span><?php echo $item->title->otherwise('&nbsp;'); ?></span>
            </li>
            
          <?php }); ?>
        </ul>
      </div>
      <a class="com-simple_gallery--go-back" href="<?php echo url('cid=NULL'); ?>">&lt; Back</a>
    </div>
    
  <?php }); ?>

  <script type="text/javascript">
  $(function(){
    $('.com-simple_gallery--item-list a').colorbox({
      photo: true,
      maxWidth: '100%',
      maxHeight: '100%'
    });
  });
  </script>

  <?php
  
}
