<?php

/**
 * Rah_default_caterogry plugin for Textpattern CMS
 *
 * @author Jukka Svahn
 * @date 2008-
 * @license GNU GPLv2
 * @link http://rahforum.biz/plugins/rah_default_category
 *
 * Copyright (C) 2011 Jukka Svahn <http://rahforum.biz>
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

	if(@txpinterface == 'admin') {
		new rah_default_category();
	}

class rah_default_category {

	static public $version = '0.6';

	/**
	 * Installer
	 * @param string $event The admin-side event.
	 * @param string $step The admin-side, plugin-lifecycle step.	
	 */

	static public function install($event='', $step='') {
		
		global $prefs;
		
		if($step == 'deleted') {
			
			safe_delete(
				'txp_prefs',
				"name like 'rah\_default\_category\_%'"
			);
			
			return;
		}
		
		if((string) get_pref(__CLASS__.'_version') === self::$version) {
			return;
		}
		
		$opt = 
			array(
				'default_category_1' => '',
				'default_category_2' => ''
			);
		
		@$rs = safe_rows('name, value', 'rah_default_category', '1=1');
		
		if($rs && is_array($rs)) {
			foreach($rs as $a) {
				if(isset($opt[$a['name']])) {
					$opt[$a['name']] = $a['value'];
				}
			}
			
			@safe_query('DROP TABLE IF EXISTS '.safe_pfx(__CLASS__));
		}
	
		$position = 245;
		
		foreach($opt as $name => $value) {
			$name = 'rah_'.$name;
			
			if(!isset($prefs[$name])) {
				set_pref($name, $value, 'rah_defcat', PREF_ADVANCED, 'rah_default_category_list', $position);
				$prefs[$name] = $value;
			}
			
			$position++;
		}
		
		set_pref(__CLASS__.'_version', self::$version, 'rah_defcat', 2, '', 0);
		$prefs[__CLASS__.'_version'] = self::$version;
	}

	/**
	 * Constructor
	 */

	public function __construct() {
		add_privs('plugin_prefs.'.__CLASS__, '1,2');
		register_callback(array(__CLASS__, 'install'), 'plugin_lifecycle.'.__CLASS__);
		register_callback(array($this, 'prefs'), 'plugin_prefs.'.__CLASS__);
		register_callback(array($this, 'head'), 'admin_side', 'head_end');
	}

	/**
	 * Adds the selection script to Write panel's <head>
	 */

	public function head() {
		
		global $event, $prefs;
		
		if($event != 'article' || ps('event') || gps('ID'))
			return;
		
		$js = array();
		
		if(!empty($prefs['rah_default_category_1'])) {
			$js[] =
				'$("#category-1 option[value=\''.
					escape_js($prefs['rah_default_category_1']).
				'\']").attr("selected","selected");';
		}
		
		if(!empty($prefs['rah_default_category_2'])) {
			$js[] = 
				'$("#category-2 option[value=\''.
					escape_js($prefs['rah_default_category_2']).
				'\']").attr("selected","selected");';
		}
		
		if(!$js)
			return;

		echo 
			script_js(
				'$(document).ready(function() {'.n.
					implode(n, $js).n.
				'});'
			);
	}

	/**
	 * Redirects to the preferences panel
	 */

	public function prefs() {
		header('Location: ?event=prefs&step=advanced_prefs#prefs-rah_default_category_1');
		echo 
			'<p>'.n.
			'	<a href="?event=prefs&amp;step=advanced_prefs#prefs-rah_default_category_1">'.gTxt('continue').'</a>'.n.
			'</p>';
	}
}

/**
 * Lists all available categories
 * @param string $name Preferences field's name.
 * @param string $val Currently saved value
 * @return string HTML select field.
 */

	function rah_default_category_list($name, $val) {
		return treeSelectInput($name, getTree('root', 'article'), $val, $name, 35);
	}
?>