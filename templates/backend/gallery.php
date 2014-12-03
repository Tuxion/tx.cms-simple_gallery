<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

$arr_url = explode('/', tx('Data')->get->rest);
$page_id = $arr_url[count($arr_url)-1];

?>

<link rel="stylesheet" href="<?php echo URL_COMPONENTS; ?>simple_gallery/includes/gallery-backend.css" type="text/css" />

<div class="page-item com-simple_gallery--gallery">

  <h3><?php __('Gallery'); ?></h3>
  
  <form id="form-simple-gallery" class="form" method="post" action="<?php echo url('action=simple_gallery/save_gallery/post', 1); ?>">
    <input type="hidden" name="id" value="<?php echo $data->gallery->id; ?>" />
    <div class="ctrlHolder" style="border:none">
      <label>
        <input type="checkbox" name="flux_app" value="1" <?php if($data->gallery->flux_app->is_true()) echo 'checked="checked"'; ?> />
        Render as flux application.
      </label>
    </div>
    
  </form>
  
  <div id="config-column-2" class="admin-box-wrapper">
    <div class="admin-box">
      <?php echo ___('Select').' '.___('A', 'l').' '.___('Category', 'l'); ?>.
    </div>
  </div>

  <div id="config-column-1" class="admin-nav clearfix">
    <?php echo $data->category_list; ?>
  </div><!-- eof:#sidebar -->

</div>

<script type="text/javascript">
  $(function(){

    $('.com-simple_gallery--gallery')

      //Add or edit category handler.
      .on('click', '.category_list a.category, #new-category', function(e){

        e.preventDefault();

        //Add css class 'active' to clicked category.
        if($(e.target).hasClass('category')){
          $('a.category').removeClass('active');
          $(e.target).addClass('active');
        }

        //Load section.
        $.ajax({
          url: $(this).attr('href')
        }).done(function(data){
          $("#config-column-2 > .admin-box").html(data);
        });

      })
      
      //Delete item handler.
      .on('click', '.category_list .icon-delete', function(e){

        e.preventDefault();

        //First confirm.
        if(confirm("<?php __('Are you sure you want to delete this category?'); ?>")){
          $(this).closest('li').fadeOut();
          $.ajax({ url: $(this).attr('href') });
        }

      });

    app.Page.subscribe('save', function(e, page_id){
      $('#form-simple-gallery').ajaxSubmit({
        data: {page_id:page_id}
      });
    });

  });
</script>
