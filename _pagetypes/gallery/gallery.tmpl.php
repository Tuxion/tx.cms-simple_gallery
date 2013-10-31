<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.');

echo load_plugin('to_hierarchy');

?>

<div class="gallery-pagetype-wrapper">

  <!-- Main content ((edit) category and item view) -->
  <div id="config-column-2" class="admin-box-wrapper">
    <div class="admin-box"></div>
  </div>

  <!-- Category list -->
  <div id="config-column-1" class="admin-nav clearfix">
    <?php echo $data->category_list; ?>
  </div>

</div>

<?php /*
<script id="tx-gallery-category-li" type="text/x-jquery-tmpl">
  <li class="category{{if !title}} untitled{{/if}}" data-id="${id}">
    <div>
      <a href="#" data-id="${id}" >{{if title}}${title}{{else}}<?php __($names->component, "Untitled", "ucfirst"); ?>{{/if}}</a>
    </div>
  </li>
</script>
*/ ?>
