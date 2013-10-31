<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.'); ?>

<h2><?php ($data->item->id->get() > 0 ? __('Edit category').': '.$data->item->title : __('New category')); ?></h2>

<form id="gallery-category-form" method="post" action="<?php echo url('action=simple_gallery/save_category/post'); ?>" class="form">

  <input type="hidden" name="id" value="<?php echo $data->item->id ?>" />
  <input type="hidden" name="gallery_id" value="<?php echo $data->gallery->id ?>" />

  <div class="ctrlHolder">
    <label for="l_title" accesskey="t"><?php __('Title'); ?></label>
    <input autofocus class="big large" type="text" id="l_title" name="title" value="<?php echo $data->item->title; ?>" />
  </div>
  
  <div class="ctrlHolder">
    <label for="l_description" accesskey="d"><?php __('Description'); ?></label>
    <textarea class="big large" id="l_description" name="description"><?php echo $data->item->description; ?></textarea>
  </div>
  
  <fieldset class="fieldset-rights">

    <legend><?php __('User rights'); ?></legend>

    <!-- Toegang voor: -->
    <?php __('Access to'); ?>:

    <ul>
      <li><label><input type="radio" name="access_level" value="0"<?php echo ($data->item->access_level->get('int') <= 0 ? ' checked="checked"' : ''); ?> /> <?php __('Everyone'); ?></label></li>
      <li><label><input type="radio" name="access_level" value="1"<?php echo ($data->item->access_level->get('int') == 1 ? ' checked="checked"' : ''); ?> /> <?php __('Logged in users'); ?></label></li>
      <!--<li><label><input type="radio" name="access_level" value="2"<?php echo ($data->item->access_level->get('int') == 2 ? ' checked="checked"' : ''); ?> class="members" /> Groepsleden</label></li>-->
      <!--<li><label><input type="radio" name="access_level" value="3"<?php echo ($data->item->access_level->get('int') == 3 ? ' checked="checked"' : ''); ?> /> Beheerders</label></li>-->
    </ul>

    <fieldset class="fieldset-groups" style="display:none;">

      <legend><?php __('Groups with access to this category'); ?></legend>

      <ul>
        <li><label><input type="checkbox" name="permission_groups[]" value="id" /> <?php __('Group title'); ?></label></li>
      </ul>

    </fieldset>

  </fieldset>
  
  <div class="buttonHolder">
    <input class="button grey" type="button" id="btn-cancel" value="<?php __('Cancel'); ?>" />
    <input class="primaryAction button black" type="submit" value="<?php __('Save'); ?>" />
  </div>
  
</form>

<script type="text/javascript">

  $(function(){
    
    //Fill in gallery ID. Ugly, sorry.
    var gallery_id = $('#form-simple-gallery input[type="hidden"][name="id"]').val();
    var page_id = $('#form-simple-gallery input[type="hidden"][name="page_id"]').val();
    $('#gallery-category-form input[name="gallery_id"]').val(gallery_id);
    
    $('#btn-cancel').on('click', function(e){
      e.preventDefault();
      $('#config-column-2 .admin-box').html('<?php echo ___('Select').' '.___('A', 'l').' '.___('Category', 'l'); ?>.');
    });
    
    $('#gallery-category-form').submit(function(e){
      
      e.preventDefault();
      $(this).ajaxSubmit(function(data){
        $('#config-column-2 .admin-box').html('<?php echo ___('Category saved'); ?>.');
        $('.com-simple_gallery--gallery .admin-nav').load('<?php echo url('section=simple_gallery/category_list', true); ?>&pid='+page_id);
      });
      
    });
    
  });

</script>

