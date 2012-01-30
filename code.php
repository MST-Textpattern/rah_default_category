<?php	##################
	#
	#	rah_default_category-plugin for Textpattern
	#	version 0.3
	#	by Jukka Svahn
	#	http://rahforum.biz
	#
	###################

	if(@txpinterface == 'admin') {
		register_callback('rah_default_category','admin_side','head_end');
		register_callback('rah_default_category_css','admin_side','head_end');
		add_privs('rah_default_category','1,2');
		register_tab('extensions','rah_default_category','Default Category');
		register_callback('rah_default_category_page','rah_default_category');
	}

/**
	Installer
*/

	function rah_default_category_install() {
		safe_query(
			"CREATE TABLE IF NOT EXISTS ".safe_pfx('rah_default_category')." (
				`name` VARCHAR(255) NOT NULL,
				`value` VARCHAR(255) NOT NULL,
				PRIMARY KEY(`name`)
			)"
		);
		
		rah_default_category_add_prefs(
			array(
				'default_category_1' => '',
				'default_category_2' => ''
			)
		);
	}

/**
	Adds new prefs from an array
*/

	function rah_default_category_add_prefs($array) {
		
		foreach($array as $key => $val)
			if(
				safe_count(
					'rah_default_category',
					"name='".doSlash($key)."'"
				) == 0
			)
				safe_insert(
					'rah_default_category',
					"name='".doSlash($key)."',value='".doSlash($val)."'"
				);

	}

/**
	Gets the preferences
*/

	function rah_default_category_prefs() {
		
		$out = array();
		
		$rs = 
			safe_rows(
				'name,value',
				'rah_default_category',
				'1=1'
			);
		
		foreach($rs as $a)
			$out[$a['name']] = $a['value'];
			
		return 
			$out;
		
	}

/**
	Adds the selection script to Write panel
*/

	function rah_default_category() {
		
		global $event;
		
		if($event != 'article')
			return;
		
		@$prefs = rah_default_category_prefs();
		
		if(!isset($prefs['default_category_2']))
			return;
		
		/*
			It's posted, don't reselect.
		*/
		
		if((isset($_POST) && !empty($_POST)) || gps('ID'))
			return;
		
		extract($prefs);
		
		$js = 
			(!empty($default_category_1) ? 
				'$("#category-1 option[value=\''.str_replace("'","\'",$default_category_1).'\']").attr("selected","selected");' : ''
			).
			(!empty($default_category_2) ? 
				'$("#category-2 option[value=\''.str_replace("'","\'",$default_category_2).'\']").attr("selected","selected");' : ''
			)
		;
		
		if(empty($js))
			return;

		echo <<<EOF

			<script language="javascript" type="text/javascript">
				$(document).ready(function() {
					{$js}
				});
			</script>
EOF;
	}

/**
	Delivers the panels
*/

	function rah_default_category_page() {
		require_privs('rah_default_category');
		global $step;
		
		if($step == 'rah_default_category_save')
			rah_default_category_save();
		else
			rah_default_category_edit();
	}

/**
	Adds styles for the panels
*/

	function rah_default_category_css() {
		global $event;
		if($event != 'rah_default_category')
			return;
		echo <<<EOF
			<style type="text/css">
				#rah_default_category_container {
					width: 650px;
					margin: 0 auto;
				}
				#rah_default_category_container select {
					width: 640px;
				}
			</style>
EOF;
	}

/**
	Feel my main pain... wait of, I mean, the main pane
*/

	function rah_default_category_edit($message='') {
		global $event;
		
		/*
			Check if the table exists, if not,
			run the installer.
		*/
		
		@$prefs = rah_default_category_prefs();
		
		if(!isset($prefs['default_category_2'])) {
			rah_default_category_install();
			$prefs = rah_default_category_prefs();
		}
		
		extract($prefs);
		
		pagetop('rah_default_category',$message);
		
		echo 
			'	<form method="post" action="index.php" id="rah_default_category_container">'.n.
			'		<h1><strong>rah_default_category</strong> | Select your default article categories</h1>'.n.
			'		<p>&#187; <a href="?event=plugin&amp;step=plugin_help&amp;name=rah_default_category">Documentation</a></p>'.n.
			'		<p>'.n.
			'			<label>'.n.
			'				Category1:<br />'.n.
			'				<select name="default_category_1">'.n.
								rah_default_category_listing($default_category_1).n.
			'				</select>'.n.
			'			</label>'.n.
			'		</p>'.n.
			'		<p>'.n.
			'			<label>'.n.
			'				Category2:<br />'.n.
			'				<select name="default_category_2">'.n.
								rah_default_category_listing($default_category_2).n.
			'				</select>'.n.
			'			</label>'.n.
			'		</p>'.n.
			'		<p><input type="submit" value="Save" class="publish" /></p>'.n.
			'		<input type="hidden" name="event" value="'.$event.'" />'.n.
			'		<input type="hidden" name="step" value="rah_default_category_save" />'.n.
			'	</form>'.n;
	}

/**
	Lists categories
*/

	function rah_default_category_listing($default='') {

		$out = array();
		
		$rs = 
			safe_rows(
				'name,title',
				'txp_category',
				"type = 'article' and name != 'root' order by name asc"
			);
		
		$out[] = '<option value="">None</option>';
		
		foreach($rs as $a) {
			extract($a);
			$out[] = 
				'<option value="'.htmlspecialchars($name).'"'.
					(($name == $default) ? ' selected="selected"' : '').
				'>'.$title.'</option>';
		}
		
		return implode('',$out);
	}

/**
	Saves the preferences
*/

	function rah_default_category_save() {
		extract(
			doSlash(
				gpsa(
					array(
						'default_category_1',
						'default_category_2'
					)
				)
			)
		);
		
		safe_update(
			'rah_default_category',
			"value='$default_category_1'",
			"name='default_category_1'"
		);
		safe_update(
			'rah_default_category',
			"value='$default_category_2'",
			"name='default_category_2'"
		);

		rah_default_category_edit('Preferences saved.');
	}