<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

$form_id = 'gallery-item-form';

?>

<h2><?php __('Edit item'); echo (!empty($item_edit->item->title) ? ': '.$item_edit->item->title : ''); ?></h2>

<form id="<?php echo $form_id; ?>" method="post" action="<?php echo url('action=simple_gallery/save_item/post'); ?>" class="form edit-item-form">

  <input type="hidden" name="id" value="<?php echo $item_edit->item->id ?>" />
  
  <div class="ctrlHolder">
    <a target="_blank" title="Open origineel" href="<?php echo $data->item->image->generate_url(); ?>">
      <img src="<?php echo $data->item->image->generate_url(array('resize_height'=>150)); ?>" />
    </a>
  </div>
  
  <div class="ctrlHolder">
    <label for="l_title" accesskey="t"><?php __('Title'); ?></label>
    <input autofocus class="big large" type="text" id="l_title" name="title" value="<?php echo $item_edit->item->title; ?>" />
  </div>
  
  <div class="ctrlHolder" hidden>
    <label for="l_description" accesskey="d"><?php __('Description'); ?></label>
    <textarea class="big large" id="l_description" name="description"><?php echo $item_edit->item->description; ?></textarea>
  </div>

  <div class="ctrlHolder wrap_category_list" style="display:none">
    <label><?php __('Belongs to categories'); ?></label>
    <?php
    
    $item_edit->categories->each(function($row)use($item_edit){

      echo
        '<div class="category-row" style="clear:both;">'.
        '  <input style="float:left" id="c'.$row->id.'" type="checkbox" class="select-row" name="category_id[]" value="'.$row->id.'" '.(in_array($row->id->get('int'), $item_edit->item_categories->as_array()) ? ' checked' : '').' />'.
        '  <label style="float:left" for="c'.$row->id.'">'.$row->title.'</label>'.
        '</div>';

    });

    ?>
  </div>
 
  <div class="buttonHolder">
    <input class="primaryAction button black" type="submit" value="<?php __('Save'); ?>" />
  </div>
  
</form>

<script type="text/javascript">

/* =Submit form ajax call
-------------------------------------------------------------- */

$(function(){
  
  $('#<?php echo $form_id; ?>').on("submit", function(e){
    
    e.preventDefault();
    
    $(this).ajaxSubmit();
    
    var active_cat = $('#gallery-category-list a.category.active');

    if(active_cat.size() > 0)
      active_cat.trigger('click');
    else
      $("#config-column-2 .admin-box").html("<?php __('Item saved'); ?>");
    
  });
  
});

</script>
