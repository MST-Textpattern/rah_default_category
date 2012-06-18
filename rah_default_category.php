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
		rah_default_category::install();
		add_privs('plugin_prefs.rah_default_category', '1,2');
		register_callback(array('rah_default_category', 'prefs'), 'plugin_prefs.rah_default_category');
		register_callback(array('rah_default_category', 'install'), 'plugin_lifecycle.rah_default_category');
		register_callback(array('rah_default_category', 'head'), 'admin_side', 'head_end');
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
		
		$current = isset($prefs['rah_default_category_version']) ? 
			(string) $prefs['rah_default_category_version'] : 'base';
		
		if($current === self::$version)
			return;
			
		$default = 
			array(
				'default_category_1' => '',
				'default_category_2' => ''
			);
		
		/*
			Migrate preferences format from <= 0.3 to >= 0.4
		*/
		
		if($current == 'base') {
			@$rs = 
				safe_rows(
					'name, value',
					'rah_default_category',
					'1=1'
				);
		
			if($rs && is_array($rs)) {
				foreach($rs as $a) {
					if(isset($default[$a['name']])) {
						$default[$a['name']] = $a['value'];
					}
				}
			
				@safe_query(
					'DROP TABLE IF EXISTS '.safe_pfx('rah_default_category')
				);
			}
		}
		
		/*
			Add preference strings
		*/
		
		$position = 245;
		
		foreach($default as $name => $val) {
			if(!isset($prefs['rah_'.$name])) {
				safe_insert(
					'txp_prefs',
					"prefs_id=1,
					name='rah_{$name}',
					val='".doSlash($val)."',
					type=1,
					event='rah_defcat',
					html='rah_default_category_list',
					position={$position}"
				);
				
				$prefs['rah_'.$name] = $val;
			}
			
			$position++;
		}
		
		set_pref('rah_default_category_version', self::$version, 'rah_defcat', 2, '', 0);
		$prefs['rah_default_category_version'] = self::$version;
	}

	/**
	 * Adds the selection script to Write panel's <head>
	 */

	static public function head() {
		
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

	static public function prefs() {
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
		
		$out = array();
		
		$rs = 
			safe_rows(
				'name, title',
				'txp_category',
				"type = 'article' AND name != 'root' ORDER BY name asc"
			);
		
		$out[''] = '';
		
		foreach($rs as $a) {
			$out[$a['name']] = $a['title'];
		}
		
		return selectInput($name, $out, $val, '', '', $name);
	}
?>