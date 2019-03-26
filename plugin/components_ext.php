<?php 

/**
 * Plugin Name : Components Extended
 * Description : Augmented functionality for cleaner and more powerful GS components.
 * Version     : 0.9.3
 * Release date: 2019-03-22
 * Author      : Kevin Van Lierde
 * Author URI  : https://webketje.com
 * License     : The MIT License (MIT)
 *
 * Copyright (c) 2016  Kevin Van Lierde
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), 
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 **/

// init
require_once(GSPLUGINPATH . 'components_ext/components_ext.class.php');
global $components_ext;
$components_ext = new componentsExt();

// lang
i18n_merge(componentsExt::$file) || i18n_merge(componentsExt::$file, 'en_US');

// register
register_plugin(
	componentsExt::$file,
  i18n_r(componentsExt::$file . '/PLUGIN_NAME'),
  componentsExt::$ver,
  componentsExt::$author,
  componentsExt::$website,
  i18n_r(componentsExt::$file . '/PLUGIN_DESC'),
  'theme',
  array('componentsExt', 'render')
);

// public API
function get_ext_component($id, $params = null) 
{
	global $components_ext;
	$components_ext->get_ext_component($id, $params);
}

function plugin_redirect() {
	global $SITEURL, $GSADMIN;
	header('Location: ' .$SITEURL . $GSADMIN . '/load.php?id=components_ext');
}

if (myself(false) === 'plugins.php' && @$_GET['set'] === 'components_ext.php') { 
	add_action('common', array($components_ext, 'to_componentsxml'));
} else if (myself(false) === 'components.php') {
	add_action('common', 'plugin_redirect');
}

// page load
if ($components_ext->action()) {
	// if it's an AJAX request
	if (requestIsAjax()) { 
	
		// protect against CSRF, basic check 
		if (!isset($USR) || $USR != get_cookie('GS_ADMIN_USERNAME')) 
			die();
			
		// only continue if the request comes from the same domain & nonces match
		if (empty($_GET['nonce']) || !check_nonce($_GET['nonce'], 'components_ext_action', 'components_ext.php'))
		  die();
		
		// save new/ existing component
		if ($components_ext->action('edit') || $components_ext->action('add')) {
		  
		  $success = $components_ext->save_single_ext_component(array(
		    'slug'    => $_POST['slug'],
		    'oldslug' => @$_POST['oldslug'],
		    'title'   => $_POST['title'],
		    'value'   => $_POST['val'],
		    'user'    => $_POST['user']
		  ));
		  
		  if ($success) {
		    $response = $_POST;
		    $response['modified_dt'] = $success;
			  $components_ext->response(200, $response);
			} else
			  $components_ext->response(403, i18n_r('components_ext/ER_PERM_DENIED'), 'Permission denied');
		// delete component
		} else if ($components_ext->action('delete')) {
		
			if (isset($_GET['slug']))
		    $success = $components_ext->delete_ext_component($_GET['slug']);
		  
		  if ($success)
			  $components_ext->response(200, 'OK');
			else
				$components_ext->response(404, i18n_r('components_ext/ER_COMP_NOT_FOUND'), 'ComponentNotFoundError');
				
		} 
	} else {	
	  // CodeMirror integration
		if (!getDef('GSNOHIGHLIGHT', false)){
			$codemirror_path = 'template/js/codemirror/lib/codemirror-compressed.js';
			// GS 3.4 & up
			if (!file_exists(GSADMINPATH . $codemirror_path))
				$codemirror_path = 'template/js/codemirror/lib/codemirror.min.js';

			register_script('codemirror'      , $codemirror_path, GSVERSION, FALSE);
			register_style ('codemirror-css'  , 'template/js/codemirror/lib/codemirror.css'          , 'screen',FALSE);
			register_style ('codemirror-theme', 'template/js/codemirror/theme/default.css'           , 'screen',FALSE);
			
			queue_script('codemirror'      , GSBACK);
			queue_style ('codemirror-css'  , GSBACK);
			queue_style ('codemirror-theme', GSBACK);	
		}
		
		register_style ('components-ext-css', '../plugins/components_ext/components-ext.css','1','screen');
		register_script('components-ext-js' , '../plugins/components_ext/components-ext.js' ,'1', true);
		
		queue_style ('components-ext-css', GSBACK);
		queue_script('components-ext-js', GSBACK);
		
		add_action('footer', array('componentsExt', 'modify_components_link'), array(strpos($_SERVER['REQUEST_URI'], 'id=components_ext')));
	}
}