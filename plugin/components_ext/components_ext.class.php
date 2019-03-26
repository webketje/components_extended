<?php 
if (class_exists('componentsExt'))
  return;
  
class componentsExt {
	
	public $dir = null;
	public static $file    = 'components_ext';
	public static $ver     = '0.9.3';
	public static $author  = 'Kevin Van Lierde';
	public static $website = 'https://webketje.com';
	public $codeMirror = false;
	
	public function __construct()
	{
	  $this->init();
	}

  private function init()
  {
		define('COMPONENTS_EXT', componentsExt::$ver);
		
    $this->dir = tsl(GSDATAPATH . 'components/');
    
    if (!is_dir($this->dir))
      mkdir($this->dir);
    
		if (file_exists(GSDATAOTHERPATH . 'components.xml')) {
		  $existing_components = $this->from_componentsxml();
		  foreach ($existing_components as $comp)
		    $this->save_single_ext_component($comp, true);
		}
  }
  
  public static function modify_components_link($active = false)
	{ 
		echo '<script type="text/javascript">
		  (function() {
				var compExtMb = document.getElementById(\'nav_components\');
				if (compExtMb) {
					compExtMb.firstElementChild.href = \'load.php?id=components_ext\';
					' . ($active ? 'compExtMb.firstElementChild.className += \' current\';' : '') . '
				}
			}());
		</script>';
	}
  
  public static function render()
  {
		include_once(GSPLUGINPATH . 'components_ext/components_ext.view.php');
  }
  
  private function from_componentsxml()
  {
    global $USR;
    $xml = getXML(GSDATAOTHERPATH . 'components.xml');
    $result = array();
    foreach ($xml->item as $comp) {
      if (!file_exists($this->dir . $comp->slug . '.xml')) {
        $component = array(
	        'slug' => (string) $comp->slug,
	        'title'=> (string) $comp->title,
	        'value'=> (string) $comp->value
	      );
	      if (!array_key_exists('created_dt', $comp))
	        $component['created_dt'] = date('Y-m-d H:i');
	      if (!array_key_exists('modified_by', $comp))
	        $component['user'] = $USR;
        $result[$component['slug']] = $component;
	    }
    }
    return $result;
  }
  
  public function to_componentsxml()
  {
    $path  = GSDATAOTHERPATH . 'components.xml';
    $xml   = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
    $comps = $this->return_ext_components();
    
    foreach($comps as $comp) {
      $c = $xml->addChild('item');
			$c_title = $c->addChild('title');
			$c_title->addCData((string) $comp->title);
			$c->addChild('slug', (string) $comp->slug);
			$c_val = $c->addChild('value');
			$c_val->addCData((string) $comp->value);
    }
    
		if (XMLsave($xml, $path))
       return true;
  }
  
  public function return_ext_components()
	{
	  $comps = getFiles($this->dir);
	  $result = array();
	  
	  foreach($comps as $comp) {
	    $xml = getXML($this->dir . $comp);
	    $result[basename($comp, '.xml')] = $xml->item;
	  }
	  
	  return $result;
	}
	
	public function map_ext_components_from_post($post_slugs=null, $post_titles=null, $post_values=null, $post_ids=null)
	{
		$post_slugs = $post_slugs  || $_POST['slug'];
		$post_titles= $post_titles || $_POST['title'];
		$post_values= $post_values || $_POST['val'];
		$post_ids   = $post_ids    || $_POST['id'];
		
		$compArr = array();
		
		for ($ct = 0; $ct < count($post_ids); $ct++) {
			if ($post_titles[$ct] != null && $post_slugs[$ct] != null)	{				
				$compArr[$ct]['id'] = $post_ids[$ct];
				$compArr[$ct]['slug'] = $post_slugs[$ct];
				$compArr[$ct]['title'] = $post_titles[$ct];
				$compArr[$ct]['value'] = $post_values[$ct];
			}
		}
		
		return subval_sort($compArr,'title');
	}
	
	public function save_ext_components()
	{
		$mapped_components = $this->map_ext_components_from_post();
		foreach ($mapped_components as $comp) {
			$this->save_single_ext_component($comp);
	  }
	}
	
	public function action($route = null)
	{
	
		$plugin = strpos($_SERVER['REQUEST_URI'], 'load.php') > -1;
	  $id     = isset($_GET['id']) && $_GET['id'] === 'components_ext';
	  $action = isset($_GET['action']) && $_GET['action'] === $route;
	  
	  return $plugin && $id && (isset($route) ? $action : true);
	}
	
	// generic AJAX response returner
	public function response($statusCode, $message, $error = null) 
	{
	  $response = array(
	    'message'=> $message,
	    'status' => $statusCode,
	  );	
	  
	  if (is_string($error))
	    $response['error'] = $error;
	    
    header('Content-Type: application/json; charset=UTF-8');
    
	  die(json_encode($response));
	}
	
	public function save_single_ext_component($params, $nosanitizing = false) 
	{
		global $USR;
		$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
	  
		$comp = $xml->addChild('item');
		$c_title = $comp->addChild('title');
		$c_title->addCData($nosanitizing ? $params['title'] : safe_slash_html($params['title']));
		$comp->addChild('slug', $nosanitizing ? $params['slug'] : clean_url(to7bit($params['slug'], 'UTF-8')));
		$c_val = $comp->addChild('value');
		$c_val->addCData($nosanitizing ? $params['value'] : safe_slash_html($params['value']));
		
		$file = $this->dir . $params['slug'] . '.xml';
		$timestamp = date('Y-m-d H:i');
		
		if (!file_exists($file)) {
			$oldfile = $this->dir . @$params['oldslug'] . '.xml';
			if (file_exists($oldfile))
				rename($oldfile, $file);
			else {
				$c_created = $comp->addChild('created_dt');
				$c_created->addCData($timestamp);
			}
		}
		
		$c_modified = $comp->addChild('modified_dt');
		$c_modified->addCData($timestamp);
		$comp->addChild('modified_by', array_key_exists('user', $params) ? $params['user'] : $USR);
		
    exec_action('component-save');
		if (XMLsave($xml, $file))
			return $timestamp;
	}
	
	public function delete_ext_component($id) 
	{
		$comps = $this->return_ext_components();
		$file  = $this->dir . $id . '.xml';

	  if (array_key_exists($id, $comps) && file_exists($file)) {
	    unlink($file);
	    $compsxml = $this->from_componentsxml();
	    
	    if (array_key_exists($id, $compsxml)) {
        unset($compsxml[$id]);
        $this->to_componentsxml($compsxml);
      }
        
	    return true;
	  }
		return false;
	}
	
	public function get_ext_component($id, $pms = null) 
	{
	  global $ext_components, $params;
		
		$params = $pms;
	  // normalize id
	  $id = to7bit($id, 'UTF-8');
		$id = clean_url($id);
		$is_active = true;
	
	  if (!$ext_components)
	    $ext_components = $this->return_ext_components();
	 
	  if (array_key_exists($id, $ext_components) && $is_active) {
	    if (is_array($params)) {
	      $params = (object) $params;
	    } else {
	      $params = new stdClass();
	    }
	    $params->title = (string) $ext_components[$id]->title;
	    eval("?>" . strip_decode($ext_components[$id]->value) . "<?php "); 
	  }
	}
}
