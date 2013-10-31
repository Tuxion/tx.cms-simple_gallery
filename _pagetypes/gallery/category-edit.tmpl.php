<?php namespace components\simple_gallery; if(!defined('TX')) die('No direct access.'); ?>

<form method="{{if data.id}}PUT{{else}}POST{{/if}}" action="?rest=simple_gallery/category/${data.id}" id="simple-gallery-category-form" class="form">
  
  <input type="hidden" name="page_id" value="${page_id}" />
  
  <div class="ctrlHolder">
    <label for="l_title" accesskey="t"><?php __($component, 'Title'); ?></label>
    <input autofocus class="big large" type="text" id="l_title" name="title" value="${data.title}" />
  </div>
  
  <div class="ctrlHolder">
    <label for="l_description" accesskey="d"><?php __($component, 'Description'); ?></label>
    <textarea class="big large" id="l_description" name="description">${data.description}</textarea>
  </div>
  
  <fieldset class="fieldset-rights">

    <legend><?php __($component, 'User rights'); ?></legend>

    <?php __($component, 'Access to'); ?>:

    <ul>
      <li><label><input type="radio" name="access_level" value="0"{{if data.access_level == 0}} checked="checked"{{/if}} /> <?php __('Everyone'); ?></label></li>
      <li><label><input type="radio" name="access_level" value="1"{{if data.access_level == 1}} checked="checked"{{/if}} /> <?php __('Logged in users'); ?></label></li>
    </ul>

  </fieldset>
  
  <div class="buttonHolder">
    <input type="button" class="button grey cancel" value="<?php __('Cancel'); ?>" />
    <input type="submit" class="button black primaryAction"  value="<?php __('Save'); ?>" />
  </div>
  
</form>
