<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

?>
<h2><span title="Category ID: <?php echo $item_list->category_info->id; ?>"><?php echo $item_list->category_info->title; ?></span> <a id="edit-category"  class="button grey"href="<?php echo url('section=simple_gallery/category_edit&category_id='.$item_list->category_info->id); ?>"><?php __('edit'); ?></a></h2>

<br />
<div class="video-editor">

  <form id="gallery-category-form" method="post" action="<?php echo url('action=simple_gallery/save_category/post'); ?>" class="form">

    <input type="hidden" name="id" value="<?php echo $data->category_info->id ?>" />
    <input type="hidden" name="gallery_id" value="<?php echo $data->category_info->gallery_id ?>" />

    <label>
      <strong>Paste video URLs, one video per line:</strong><br />
      <textarea class="video_urls" name="video_urls" placeholder="Paste video URLs, one video per line."><?php echo $data->category_info->video_urls ?></textarea>
    </label>

    <input type="submit" value="Save">

  </form>
  
</div>

<br />
<strong>Photos (uploader at the bottom of this page):</strong><br />

<ul id="gallery" class="gallery-item-list clearfix" data-id="foto">
  <?php
  $item_list->all_items->each(function($item)use($item_list){
    ?>
      <li rel="<?php echo $item->id; ?>" class="clearfix">
        <div class="thumbnail-wrapper">
          <a class="thumbnail" href="<?php echo url('section=simple_gallery/item_edit&item_id='.$item->id); ?>">
            <img src="<?php echo $item->image->generate_url(array('resize_height'=>75)); ?>" />
          </a>
          <a title="<?php __('Delete this image'); ?>" href="<?php echo url('action=simple_gallery/item_delete&item_id='.$item->id); ?>" class="small-icon icon-delete"></a>
        </div>
      </li>
    <?php
  });
  ?>
</ul>

<?php echo $data->image_uploader; ?>

<style>
.video_urls{
  font-size:11px;
  width:500px;
  height:60px;
}
</style>

<script type="text/javascript">

$(function(){
  
  //Fill in gallery ID. Ugly, sorry.
  var gallery_id = $('#form-simple-gallery input[type="hidden"][name="id"]').val();
  var page_id = $('#form-simple-gallery input[type="hidden"][name="page_id"]').val();
  $('#gallery-category-form input[name="gallery_id"]').val(gallery_id);
  
  $('#gallery-category-form').submit(function(e){
    
    e.preventDefault();
    $(this).ajaxSubmit(function(data){
      $('#gallery-category-form').find('input[type="submit"]').val('Saved!');
      setTimeout(function(){
        $('#gallery-category-form').find('input[type="submit"]').val('Save');
      }, 2500);
    });
    
  });
  
});


$(function(){

  //On uploaded file.
  window.plupload_image_file_id = function(up, ids, file_id){
    
    //Save item in database.
    $.ajax({
      url: '<?php echo url('action=simple_gallery/save_item'); ?>',
      data: {
        file_id: file_id,
        filename: false,
        category_id: <?php echo abs(tx('Data')->get->category_id->get()); ?>
      }
    }).done(function(item_id){
      
      $.rest('GET', "?rest=media/generate_url/"+item_id+"&filters[resize_height]=75")
        .done(function(thumbnail){
          
          //Apend item to item list.
          $('.gallery-item-list')
            .append(
              '<li rel="'+item_id+'" class="clearfix">'+
              '  <div class="thumbnail-wrapper">'+
              '    <a class="thumbnail" href="<?php echo url('section=simple_gallery/item_edit'); ?>&item_id='+item_id+'">'+
              '      <img src="'+thumbnail.url+'" />'+
              '    </a>'+
              '    <a title="<?php __('Delete this image'); ?>" href="<?php echo url('action=simple_gallery/item_delete'); ?>&item_id='+item_id+'" class="small-icon icon-delete"></a>'+
              '  </div>'+
              '</li>'
            );
            
        });

    });
    
  }

});

$(function(){

  $('.com-simple_gallery--gallery')
    
    //Edit item handler.
    .on('click', '.gallery-item-list a.thumbnail, #edit-category', function(e){

      e.preventDefault();

      $.ajax({
        url: $(e.target).closest('a').attr('href')
      }).done(function(data){
        $(".com-simple_gallery--gallery .admin-box").html(data);
      }).fail(function(){
        alert('Couldn\'t load the requested page.');
      });

    })

    //Delete item handler.
    .one('click', '.gallery-item-list .icon-delete', function(e){
      e.preventDefault();
      if(confirm("Are you sure you want to delete this image?")){
        $(e.target).closest('li').fadeOut();
        $.ajax({ url: $(e.target).attr('href') });
      }
    });
  
  });

</script>
