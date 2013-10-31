<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

echo load_plugin('colorbox');
?>

<?php
$data->categories->each(function($cat){
  ?>
  <h1><?php echo $cat->title; ?></h1>
  <ul class="com-simple_gallery--item-list clearfix">
    <?php $cat->items->each(function($item)use($cat){ ?>
    <li>
      <a title="<?php echo $item->title.($item->description->get('bool') ? ' - '.$item->description : ''); ?>" rel="cat-<?php echo $cat->id; ?>" target="_blank" href="<?php echo $item->image->generate_url(array('resize_width' => 1600)); ?>">
        <img src="<?php echo $item->image->generate_url(array('resize_height' => 100)); ?>">
      </a>
      <span><?php echo $item->title->otherwise('&nbsp;'); ?></span>
    </li>
    <?php }); ?>
  </ul>

  <?php
  });
?>

<script type="text/javascript">
$(function(){
  $('.com-simple_gallery--item-list a').colorbox({
    photo: true,
    maxWidth: '100%',
    maxHeight: '100%'
  });
});
</script>

