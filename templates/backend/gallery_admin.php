<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.'); ?>

<h1><?php __('Gallery') ?></h1>

<div id="config-column-2" class="admin-box-wrapper gallery-admin">
  <div class="admin-box">
    <?php echo ___('Select').' '.___('A', 'l').' '.___('Category', 'l'); ?>.
  </div>
</div>

<div id="config-column-1" class="admin-nav clearfix">
  <?php echo $gallery_admin->category_list; ?>
</div><!-- eof:#sidebar -->

<script type="text/javascript">
  $(function(){

    $(".category_list a.category, #new-category, #edit-category").on('click', function(e){
      e.preventDefault();
      $.ajax({
        url: $(this).attr('href')
      }).done(function(data){
        $("#config-column-2 > .admin-box").html(data);
      });
    });
    
    $(".category_list a.category").on('click', function(e){
      e.preventDefault();
      $('a.category').removeClass('active');
      $(this).addClass('active');
    });

    $(".category_list .icon-delete").on("click", function(e){
      e.preventDefault();
      if(confirm("Are you sure you want to delete this category?")){

        var category = $(this).closest('li');

        $.ajax({
          url: $(this).attr('href')
        }).done(function(){
          category.fadeOut();
        });

      }
    });

  });
</script>

<style>
#config_app, body{
  background-color:#fff;
}
</style>
