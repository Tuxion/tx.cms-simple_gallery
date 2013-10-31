<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

?>
<h2><span title="Category ID: <?php echo $item_list->category_info->id; ?>"><?php echo $item_list->category_info->title; ?></span> <a id="edit-category"  class="button grey"href="<?php echo url('section=simple_gallery/category_edit&category_id='.$item_list->category_info->id); ?>"><?php __('edit'); ?></a></h2>

<ul id="gallery" class="gallery-item-list clearfix">
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

<script type="text/javascript">

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
