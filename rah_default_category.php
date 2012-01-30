<?php

/**
 * Rah_default_caterogry plugin for Textpattern CMS
 *
 * @author Jukka Svahn
 * @date 2011-
 * @license GNU GPLv2
 * @link http://rahforum.biz/plugins/rah_default_category
 *
 * Requires Textpattern v4.0.7 or newer.
 *
 * Copyright (C) 2011 Jukka Svahn <http://rahforum.biz>
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

	if(@txpinterface == 'admin') {
		rah_default_category_install();
		add_privs('plugin_prefs.rah_default_category', '1,2');
		register_callback('rah_default_category_prefs', 'plugin_prefs.rah_default_category');
		register_callback('rah_default_category_install', 'plugin_lifecycle.rah_default_category');
		register_callback('rah_default_category', 'admin_side', 'head_end');
	}

/**
 * Installer and uninstaller. Keeps it tidy.
 * @param string $event The admin-side event.
 * @param string $step The admin-side, plugin-lifecycle step.	
 */

	function rah_default_category_install($event='', $step='') {
		
		if($step == 'deleted') {
			
			safe_delete(
				'txp_prefs',
				"name like 'rah_default_category_%'"
			);
			
			return;
		}
		
		global $prefs;
		
		$version = '0.6';
		
		$current = 
			isset($prefs['rah_default_category_version']) ? 
				$prefs['rah_default_category_version'] : 'base';
		
		if($current == $version)
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
		
			if(!empty($rs) && is_array($rs)) {
				foreach($rs as $a)
					if(isset($default[$a['name']]))
						$default[$a['name']] = $a['value'];
			
				@safe_query(
					'DROP TABLE IF EXISTS '.safe_pfx('rah_default_category')
				);
			}
		}
		
		/*
			Add preference strings
		*/
		
		foreach($default as $key => $val) {
			if(!isset($prefs['rah_' . $key])) {
				safe_insert(
					'txp_prefs',
					"prefs_id=1,
					name='rah_".$key."',
					val='".doSlash($val)."',
					type=1,
					event='rah_defcat',
					html='rah_default_category_list',
					position=". ($key == 'default_category_1' ? 245 : 246)
				);
				
				$prefs['rah_' . $key ] = $val;
			}
		}
		
		set_pref('rah_default_category_version',$version,'rah_defcat',2,'',0);
		$prefs['rah_default_category_version'] = $version;
	}

/**
 * Adds the selection script to Write panel
 */

	function rah_default_category() {
		
		global $event, $prefs;
		
		if(
			$event != 'article' || 
			!isset($prefs['rah_default_category_1']) ||
			!isset($prefs['rah_default_category_2'])
		)
			return;
		
		/*
			It's posted, don't reselect.
		*/
		
		if((isset($_POST) && !empty($_POST) && isset($_POST['event']) && $_POST['event'] == 'article') || gps('ID'))
			return;
		
		$js = 
			(!empty($prefs['rah_default_category_1']) ? 
				'$("#category-1 option[value=\''.str_replace("'","\'",$prefs['rah_default_category_1']).'\']").attr("selected","selected");' : ''
			).
			(!empty($prefs['rah_default_category_2']) ? 
				'$("#category-2 option[value=\''.str_replace("'","\'",$prefs['rah_default_category_2']).'\']").attr("selected","selected");' : ''
			)
		;
		
		if(empty($js))
			return;

		echo <<<EOF

			<script type="text/javascript">
				<!--
				$(document).ready(function() {
					{$js}
				});
				-->
			</script>
EOF;
	}

/**
 * Lists all available categories
 * @param string $name Preferences field's name.
 * @param string $val Currently save value
 * @return string HTML select field.
 */

	function rah_default_category_list($name, $val) {
		
		$out = array();
		
		$rs = 
			safe_rows(
				'name,title',
				'txp_category',
				"type = 'article' and name != 'root' order by name asc"
			);
			
		$out[''] = gTxt('none');
		
		foreach($rs as $a)
			$out[$a['name']] = $a['title'];
		
		return selectInput($name, $out, $val, '', '', $name);
	}

/**
 * Redirects to the preferences panel
 */

	function rah_default_category_prefs() {
		header('Location: ?event=prefs&step=advanced_prefs#prefs-rah_default_category_1');
		echo 
			'<p id="message">'.n.
			'	<a href="?event=prefs&amp;step=advanced_prefs#prefs-rah_default_category_1">'.gTxt('continue').'</a>'.n.
			'</p>';
	}
?>