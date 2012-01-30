<?php	##################
	#
	#	rah_default_category-plugin for Textpattern
	#	version 0.1
	#	by Jukka Svahn
	#	http://rahforum.biz
	#
	###################

	if (@txpinterface == 'admin') {
		register_callback('rah_default_category','article');
		add_privs('rah_default_category_page', '1,2');
		register_tab("extensions", "rah_default_category_page", "Default Category");
		register_callback("rah_default_category_page", "rah_default_category_page");
	}

	function rah_default_category_install() {
		safe_query(
			"CREATE TABLE IF NOT EXISTS ".safe_pfx('rah_default_category')." (
				`name` VARCHAR(255) NOT NULL DEFAULT '',
				`value` VARCHAR(255) NOT NULL DEFAULT '',
				PRIMARY KEY(`name`)
			)"
		);
		if(safe_count('rah_default_category',"name='default_category_1'") == 0) safe_insert("rah_default_category","name='default_category_1',value=''");
		if(safe_count('rah_default_category',"name='default_category_2'") == 0) safe_insert("rah_default_category","name='default_category_2',value=''");
	}

	function rah_default_category() {
		rah_default_category_install();
		$default_category_1 = fetch('value','rah_default_category','name','default_category_1');
		$default_category_2 = fetch('value','rah_default_category','name','default_category_2');
		$line_1 = (!gps('ID') && !ps('ID') && !ps('Category1') && $default_category_1) ? '		$("#category-1 option[value='.str_replace('"','\"',$default_category_1).']").attr("selected","selected");'.n : '';
		$line_2 = (!gps('ID') && !ps('ID') && !ps('Category2') && $default_category_2) ? '		$("#category-2 option[value='.str_replace('"','\"',$default_category_2).']").attr("selected","selected");'.n : '';
		echo ($line_1 or $line_2) ?
			'<script language="javascript" type="text/javascript">'.n.
			'	$(document).ready(function() {'.n.$line_1.$line_2.'	});'.n.
			'</script>' : '';
	}

	function rah_default_category_page() {
		require_privs('rah_default_category_page');
		global $step;
		rah_default_category_install();
		if($step == 'rah_default_category_save') rah_default_category_save();
		else rah_default_category_edit();
	}

	function rah_default_category_edit($message='') {
		global $event;
		pagetop('rah_default_category',$message);
		$default_category_1 = fetch('value','rah_default_category','name','default_category_1');
		$default_category_2 = fetch('value','rah_default_category','name','default_category_2');
		echo 
			'	<form method="post" action="index.php" style="width:900px;margin:0 auto;">'.n.
			'		<h1><strong>rah_default_category</strong> | Select your default article categories</h1>'.n.
			'		<p>&#187; <a target="_blank" href="?event=plugin&amp;step=plugin_help&amp;name=rah_default_category">Documentation</a></p>'.n.
			'		<table border="0" cellspacing="0" cellpadding="0" style="width:100%;">'.n.
			'			<tr>'.n.
			'				<td>'.n.
			'					<label for="rah_default_category_1">Category1:</label><br />'.n.
			'					<select id="rah_default_category_1" name="default_category_1">'.n.rah_default_category_listing($default_category_1).'					</select>'.n.
			'				</td>'.n.
			'				<td>'.n.
			'					<label for="rah_default_category_2">Category2:</label><br />'.n.
			'					<select id="rah_default_category_1" name="default_category_2">'.n.rah_default_category_listing($default_category_2).'					</select>'.n.
			'				</td>'.n.
			'			</tr>'.n.
			'		</table>'.n.
			'		<p><input type="submit" value="'.gTxt('save').'" class="publish" /></p>'.n.
			'		<input type="hidden" name="event" value="'.$event.'" />'.n.
			'		<input type="hidden" name="step" value="rah_default_category_save" />'.n.
			'	</form>'.n;
	}

	function rah_default_category_listing($default='') {
		$out = array();
		$rs = safe_rows_start('name,title','txp_category',"type = 'article' and name != 'root' order by name asc");
		if ($rs and numRows($rs) > 0){
			$out[] = '						<option value="">None</option>'.n;
			while ($a = nextRow($rs)) {
				extract($a);
				$out[] = '						<option value="'.htmlspecialchars($name).'"'.(($name == $default) ? ' selected="selected"' : '').'>'.$title.'</option>'.n;
			}
		} else $out[] = '						<option value="">No categories created yet.</option>'.n;
		return implode('',$out);
	}

	function rah_default_category_save() {
		extract(doSlash(gpsa(array('default_category_1','default_category_2'))));
		safe_update('rah_default_category',"value='$default_category_1'","name='default_category_1'");
		safe_update('rah_default_category',"value='$default_category_2'","name='default_category_2'");
		rah_default_category_edit('Preferences saved.');
	}