<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

echo load_plugin('nestedsortable');

?>

<script type="text/javascript">

  function resizeCatalogMenu(){
    var docHeight = $(document).height();
    $('.category_list').css('height' , docHeight-'205')
  }
  $(window).bind('resize', function () {         
    resizeCatalogMenu();
  });

  $(function(){

		$('#gallery-category-list ul.nestedsortable').nestedSortable({
			disableNesting: 'no-nest',
			forcePlaceholderSize: true,
			handle: 'div',
			helper:	'clone',
			listType: 'ul',
			items: 'li',
			maxLevels: 5,
			opacity: .6,
			placeholder: 'placeholder',
			revert: 250,
			tabSize: 25,
			tolerance: 'pointer',
			toleranceElement: '> div'
		})
    
    .on('sortupdate', function(){
      
      $('#gallery-category-list .save').show();
      
    });

		$('#gallery-category-list .save').click(function(e){
      
      var target = this, message='Something went wrong.';
      
      e.preventDefault();
      
      $(target).text('<?php __('Saving'); ?>...');

      $.ajax({
        url: $(target).attr('href'),
        type: 'POST',
        dataType: 'JSON',
        data: {
          categories: $('#gallery-category-list ul.nestedsortable').nestedSortable('toArray', {startDepthCount: 0, attribute: 'rel', expression: (/()([0-9]+)/), omitRoot: true})
        }
      })
      
      .done(function(data){
        message = data.message;
        $(target).hide();
        $(target).text('<?php __('Save'); ?>');
      })
      
      .then(function(){
        $('#gallery-category-list .message').text(message).show().delay(1000).fadeOut();
      });

    });
    
    resizeCatalogMenu();
    
	});
    
</script>

<div id="gallery-category-list">
  <div class="ctrlHolder clearfix">
    <div class="message button"></div>
    <a class="button black" href="<?php echo url('section=simple_gallery/category_edit', true); ?>" id="new-category"><?php __('New category'); ?></a>
    <button class="save black" hidden href="<?php echo url('section=simple_gallery/json_update_categories', true) ?>"><?php __('Save'); ?></button>
  </div>

  <?php

  //display list
  echo $category_list->as_hlist('category_list nestedsortable', function($cat, $key, $delta, &$properties){
    return
      '<div>'.
      '  <a class="category" href="'.url('category_id='.$cat->id.'&section=simple_gallery/item_list', true).'">'.$cat->title.'</a>'.
      '  <a href="'.url('action=simple_gallery/category_delete&category_id='.$cat->id, true).'" class="small-icon icon-delete"></a>'.
      '</div>';
  });

  ?>
</div>
  
