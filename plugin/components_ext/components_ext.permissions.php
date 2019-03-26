<?php 

$users_api->define_permissions(
  'access_components_ext_plugin'
);

function components_ext_plugin_access($current_user, $style) {
	if ($current_user->cannot('create_components'))
	  $style .= '.edit-nav #btn-new { display: none; }';
	if ($current_user->cannot('delete_components'))
	  $style .= '.compdiv td.comp-edit, .compdiv td.comp-active  { padding-right: 0; }';
	if (function_exists('components_page_access'))
    $style = components_page_access($current_user, $style);	
	
	return $style;
} 