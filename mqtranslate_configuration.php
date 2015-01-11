<?php // encoding: utf-8

/*  Copyright 2008  Qian Qin  (email : mail@qianqin.de)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined('ABSPATH') ) exit;

require_once(dirname(__FILE__)."/admin/import_export.php");
require_once(dirname(__FILE__)."/admin/activation_hook.php");

function qtrans_update_config()
{
	//if(!defined('WP_ADMIN')) return;
	if(!current_user_can('manage_options')) return;
	if(isset($_POST['mqtranslate_reset']) && isset($_POST['mqtranslate_reset2'])) {
		// reset all settings
		delete_option('mqtranslate_language_names');
		delete_option('mqtranslate_enabled_languages');
		delete_option('mqtranslate_default_language');
		delete_option('mqtranslate_flag_location');
		delete_option('mqtranslate_flags');
		delete_option('mqtranslate_locales');
		delete_option('mqtranslate_na_messages');
		delete_option('mqtranslate_date_formats');
		delete_option('mqtranslate_time_formats');
		delete_option('mqtranslate_use_strftime');
		delete_option('mqtranslate_ignore_file_types');
		delete_option('mqtranslate_url_mode');
		delete_option('mqtranslate_detect_browser_language');
		delete_option('mqtranslate_hide_untranslated');
		delete_option('mqtranslate_show_displayed_language_prefix');
		delete_option('mqtranslate_auto_update_mo');
		delete_option('mqtranslate_next_update_mo');
		delete_option('mqtranslate_hide_default_language');
		delete_option('mqtranslate_custom_fields');
		delete_option('mqtranslate_ul_lang_protection');
		delete_option('mqtranslate_allowed_custom_post_types');
		delete_option('mqtranslate_disable_header_css');
		delete_option('mqtranslate_disable_client_cookies');
		delete_option('mqtranslate_use_secure_cookie');
		delete_option('mqtranslate_filter_all_options');
		if(isset($_POST['mqtranslate_reset3'])) {
			delete_option('mqtranslate_term_name');
			delete_option('mqtranslate_widget_css');
		}
	}
}
add_action('qtrans_init_begin','qtrans_update_config',10);

function qtrans_add_admin_js ()
{
	wp_enqueue_script( 'qtranslate-script', plugins_url( '/qtranslate.js', __FILE__ ) );
	
	wp_dequeue_script( 'autosave' );
	wp_deregister_script( 'autosave' ); //autosave script saves the active language only and messes it up later in a hard way
	
	$keys = array('enabled_languages','default_language','language','term_name','custom_fields','custom_field_classes','url_mode');
	foreach ($keys as $key)
		$config[$key]=$q_config[$key];
	$config['url_info_home']=$q_config['url_info']['home'];
	$config['flag_location']=trailingslashit(WP_CONTENT_URL).$q_config['flag_location'];
	$config['flag']=array();
	$config['language_name']=array();
	if (is_array($q_config['enabled_languages'])) {
		foreach($q_config['enabled_languages'] as $lang) {
			$config['flag'][$lang]=$q_config['flag'][$lang];
			$config['language_name'][$lang]=$q_config['language_name'][$lang];
		}
	}
?>
	<script type="text/javascript">
	/* <![CDATA[ */
		var qTranslateConfig=<?php echo json_encode($config); ?>;
		function qtrans_getcookie(cname)
		{
			var nm = cname + "=";
			var ca = document.cookie.split(';');
			for(var i=0; i<ca.length; i++) {
				var c = ca[i];
				var p = c.indexOf(nm);
				if (p >= 0) return c.substring(p+nm.length,c.length);
			}
			return '';
		}
		function qtrans_delcookie(cname)
		{
			var date = new Date();
			date.setTime(date.getTime()-(24*60*60*1000));
			document.cookie=cname+'=; expires='+date.toGMTString();
		}
		function qtrans_readShowHideCookie(id) {
			var e=document.getElementById(id);
			if(!e) return;
			if(qtrans_getcookie(id)){
				e.style.display='block';
			}else{
				e.style.display='none';
			}
		}
		function qtrans_toggleShowHide(id) {
			var e = document.getElementById(id);
			if (e.style.display == 'block'){
				qtrans_delcookie(id);
				e.style.display = 'none';
			}else{
				document.cookie=id+'=1';
				e.style.display='block';
			}
			return false;
		}
	<?php do_action('qtranslate_js'); ?>
	/* ]]> */
	</script>
<?php
}

function qtrans_add_admin_lang_icons ()
{
	global $q_config;
	?>
<style type="text/css" media="screen">
/* <![CDATA[ */
	#wpadminbar #wp-admin-bar-language > div.ab-item {
		background-image: url('<?php echo trailingslashit(WP_CONTENT_URL).$q_config['flag_location'].$q_config['flag'][$q_config['language']]; ?>');
	}
<?php foreach ($q_config['enabled_languages'] as $language) : ?>
	#wpadminbar ul li#wp-admin-bar-<?php echo $language ?> {
		background-image: url('<?php echo trailingslashit(WP_CONTENT_URL).$q_config['flag_location'].$q_config['flag'][$language]; ?>');
	}
<?php endforeach; ?>
/* ]]> */
</style>
<?php 
}

function qtrans_add_admin_css() {
	qtrans_add_admin_lang_icons();
	echo "<style type=\"text/css\" media=\"screen\">\n";
	do_action('qtranslate_css');
	echo "</style>\n";
}

function qtrans_admin_head() {
	wp_enqueue_style( 'qtranslate-admin-style', plugins_url('mqtranslate_configuration.css', __FILE__) );
	qtrans_add_admin_css();
	qtrans_add_admin_js();
	qtrans_optionFilter('disable');
}

/* mqTranslate Management Interface */
function qtrans_adminMenu() {
	/* Configuration Page */
	add_options_page(__('Language Management', 'mqtranslate'), __('Languages', 'mqtranslate'), 'manage_options', 'mqtranslate', 'qtrans_conf');
}

function qtrans_language_form($lang = '', $language_code = '', $language_name = '', $language_locale = '', $language_date_format = '', $language_time_format = '', $language_flag ='', $language_na_message = '', $language_default = '', $original_lang='') {
	global $q_config;
?>
<input type="hidden" name="original_lang" value="<?php echo $original_lang; ?>" />

<div class="form-field">
	<label for="language_code"><?php _e('Language Code', 'mqtranslate') ?></label>
	<input name="language_code" id="language_code" type="text" value="<?php echo $language_code; ?>" size="2" maxlength="2"/>
	<p><?php _e('2-Letter <a href="http://www.w3.org/WAI/ER/IG/ert/iso639.htm#2letter">ISO Language Code</a> for the Language you want to insert. (Example: en)', 'mqtranslate'); ?></p>
</div>
<div class="form-field">
	<label for="language_flag"><?php _e('Flag', 'mqtranslate') ?></label>
	<?php 
	$files = array();
	if($dir_handle = @opendir(trailingslashit(WP_CONTENT_DIR).$q_config['flag_location'])) {
		while (false !== ($file = readdir($dir_handle))) {
			if(preg_match("/\.(jpeg|jpg|gif|png)$/i",$file)) {
				$files[] = $file;
			}
		}
		sort($files);
	}
	if(sizeof($files)>0){
	?>
	<select name="language_flag" id="language_flag" onchange="switch_flag(this.value);"  onclick="switch_flag(this.value);" onkeypress="switch_flag(this.value);">
	<?php
		foreach ($files as $file) {
	?>
		<option value="<?php echo $file; ?>" <?php echo ($language_flag==$file)?'selected="selected"':''?>><?php echo $file; ?></option>
	<?php
		}
	?>
	</select>
	<img src="" alt="Flag" id="preview_flag" style="vertical-align:middle; display:none"/>
	<?php
	} else {
		_e('Incorrect Flag Image Path! Please correct it!', 'mqtranslate');
	}
	?>
	<p><?php _e('Choose the corresponding country flag for language. (Example: gb.png)', 'mqtranslate'); ?></p>
</div>
<script type="text/javascript">
//<![CDATA[
	function switch_flag(url) {
		document.getElementById('preview_flag').style.display = "inline";
		document.getElementById('preview_flag').src = "<?php echo trailingslashit(WP_CONTENT_URL).$q_config['flag_location'];?>" + url;
	}
	
	switch_flag(document.getElementById('language_flag').value);
//]]>
</script>
<div class="form-field">
	<label for="language_name"><?php _e('Name', 'mqtranslate') ?></label>
	<input name="language_name" id="language_name" type="text" value="<?php echo $language_name; ?>"/>
	<p><?php _e('The Name of the language, which will be displayed on the site. (Example: English)', 'mqtranslate'); ?></p>
</div>
<div class="form-field">
	<label for="language_locale"><?php _e('Locale', 'mqtranslate') ?></label>
	<input name="language_locale" id="language_locale" type="text" value="<?php echo $language_locale; ?>"  size="5" maxlength="5"/>
	<p>
		<?php _e('PHP and Wordpress Locale for the language. (Example: en_US)', 'mqtranslate'); ?><br />
		<?php _e('You will need to install the .mo file for this language.', 'mqtranslate'); ?>
	</p>
</div>
<div class="form-field">
	<label for="language_date_format"><?php _e('Date Format', 'mqtranslate') ?></label>
	<input name="language_date_format" id="language_date_format" type="text" value="<?php echo $language_date_format; ?>"/>
	<p><?php _e('Depending on your Date / Time Conversion Mode, you can either enter a <a href="http://www.php.net/manual/function.strftime.php">strftime</a> (use %q for day suffix (st,nd,rd,th)) or <a href="http://www.php.net/manual/function.date.php">date</a> format. This field is optional. (Example: %A %B %e%q, %Y)', 'mqtranslate'); ?></p>
</div>
<div class="form-field">
	<label for="language_time_format"><?php _e('Time Format', 'mqtranslate') ?></label>
	<input name="language_time_format" id="language_time_format" type="text" value="<?php echo $language_time_format; ?>"/>
	<p><?php _e('Depending on your Date / Time Conversion Mode, you can either enter a <a href="http://www.php.net/manual/function.strftime.php">strftime</a> or <a href="http://www.php.net/manual/function.date.php">date</a> format. This field is optional. (Example: %I:%M %p)', 'mqtranslate'); ?></p>
</div>
<div class="form-field">
	<label for="language_na_message"><?php _e('Not Available Message', 'mqtranslate') ?></label>
	<input name="language_na_message" id="language_na_message" type="text" value="<?php echo $language_na_message; ?>"/>
	<p>
		<?php _e('Message to display if post is not available in the requested language. (Example: Sorry, this entry is only available in %LANG:, : and %.)', 'mqtranslate'); ?><br />
		<?php _e('%LANG:&lt;normal_seperator&gt;:&lt;last_seperator&gt;% generates a list of languages seperated by &lt;normal_seperator&gt; except for the last one, where &lt;last_seperator&gt; will be used instead.', 'mqtranslate'); ?><br />
	</p>
</div>
<?php
}

function qtrans_updateSetting($var, $type = QT_STRING) {
	global $q_config;
	if (!isset($_POST['submit']))
		return false;
	switch ($type) {
		case QT_URL:
		case QT_LANGUAGE:
		case QT_STRING:
			if (isset($_POST[$var])) {
				if ($type == QT_URL)
					$_POST[$var] = trailingslashit($_POST[$var]);
				if ($q_config[$var] == $_POST[$var] || ($type == QT_LANGUAGE && !qtrans_isEnabled($_POST[$var])))
					return false;
				$q_config[$var] = $_POST[$var];
				update_option('mqtranslate_'.$var, $q_config[$var]);
				return true;
			}
			break;
		case QT_ARRAY:
			if (isset($_POST[$var])) {
				$val = preg_split('/[\s,]+/',$_POST[$var]);
				if (qtrans_array_compare($q_config[$var], $val))
					return false;
				$q_config[$var] = $val;
				update_option('mqtranslate_'.$var, $q_config[$var]);
				return true;
			}
			break;
		case QT_BOOLEAN:
			if (isset($_POST[$var])) {
				if (empty($q_config[$var]) == empty($_POST[$var]))
					return false;
				$q_config[$var] = !empty($_POST[$var]);
				update_option('mqtranslate_'.$var, empty($q_config[$var]) ? '0' : '1');
				return true;
			}
			break;
		case QT_INTEGER:
			if (isset($_POST[$var])) {
				$val = intval($_POST[$var]);
				if ($q_config[$var] == $val)
					return false;
				$q_config[$var] = $val;
				update_option('mqtranslate_'.$var, $q_config[$var]);
				return true;
			}
			break;
	}
	return false;
}

function qtrans_updateSettingIgnoreFileTypes() {
	global $q_config;
	if(!isset($_POST['submit'])) return false;
	$var='ignore_file_types';
	if(!isset($_POST[$var])) return false;
	$posted=preg_split('/[\s,]+/',strtolower($_POST[$var]));
	$val=explode(',',QT_IGNORE_FILE_TYPES);
	foreach($posted as $v){
		if(empty($v)) continue;
		if(in_array($v,$val)) continue;
		$val[]=$v;
	}
	if( qtrans_array_compare($q_config[$var],$val) ) return false;
	$q_config[$var] = $val;
	update_option('mqtranslate_'.$var, implode(',',$val));
	return true;
}

function qtrans_array_compare($a,$b) {
	if(count($a)!=count($b)) return false;
	//can be optimized
	$diff_a = array_diff($a,$b);
	$diff_b = array_diff($b,$a);
	return empty($diff_a) && empty($diff_b);
}

function qtrans_language_columns($columns) {
	return array(
				'flag' => 'Flag',
				'name' => __('Name', 'mqtranslate'),
				'status' => __('Action', 'mqtranslate'),
				'status2' => '',
				'status3' => ''
				);
}

function qtrans_useAdminTermLib($obj) {
	if ($_SERVER["SCRIPT_NAME"]==="/wp-admin/edit-tags.php" && strstr($_SERVER["QUERY_STRING"], "action=edit" ))
		return $obj;
	else
		return qtrans_useTermLib($obj);
}

function qtrans_admin_section_start($section, $nm) {
	echo '<h3>'.$section.'<span id="qtrans-show-'.$nm.'"> ( <a name="qtranslate_'.$nm.'_settings" href="#" onclick="return toggleShowHide(\'qtranslate-admin-'.$nm.'\');">'.__('Show', 'qtranslate').' / '.__('Hide', 'qtranslate').'</a> )</span>';
}

function qtrans_admin_section_end($nm) {
?>
<script type="text/javascript">
/* <![CDATA[ */
	qtrans_readShowHideCookie('qtranslate-admin-<?php echo $nm; ?>');
/* ]]> */
</script>
<?php
}

function qtrans_conf() {
	global $q_config, $wpdb;
	
	// do redirection for dashboard
	if(isset($_GET['godashboard'])) {
		echo '<h2>'.__('Switching Language', 'mqtranslate').'</h2>'.sprintf(__('Switching language to %1$s... If the Dashboard isn\'t loading, use this <a href="%2$s" title="Dashboard">link</a>.','mqtranslate'),$q_config['language_name'][qtrans_getLanguage()],admin_url()).'<script type="text/javascript">document.location="'.admin_url().'";</script>';
		exit();
	}
	
	// init some needed variables
	$error = '';
	$original_lang = '';
	$language_code = '';
	$language_name = '';
	$language_locale = '';
	$language_date_format = '';
	$language_time_format = '';
	$language_na_message = '';
	$language_flag = '';
	$language_default = '';
	$altered_table = false;
	
	$message = apply_filters('mqtranslate_configuration_pre','');
	
	// check for action
	if(isset($_POST['mqtranslate_reset']) && isset($_POST['mqtranslate_reset2'])) {
		$message = __('mqTranslate has been reset.', 'mqtranslate');
	} elseif(isset($_POST['default_language'])) {
		// save settings
		qtrans_updateSetting('default_language',		QT_LANGUAGE);
		qtrans_updateSetting('flag_location',			QT_URL);
		qtrans_updateSettingIgnoreFileTypes();
		qtrans_updateSetting('detect_browser_language',	QT_BOOLEAN);
		qtrans_updateSetting('hide_untranslated',		QT_BOOLEAN);
		qtrans_updateSetting('show_displayed_language_prefix', QT_BOOLEAN);
		qtrans_updateSetting('use_strftime',			QT_INTEGER);
		qtrans_updateSetting('url_mode',				QT_INTEGER);
		qtrans_updateSetting('auto_update_mo',			QT_BOOLEAN);
		qtrans_updateSetting('hide_default_language',	QT_BOOLEAN);
		qtrans_updateSetting('custom_fields', 			QT_ARRAY);
		qtrans_updateSetting('custom_field_classes', 	QT_ARRAY);
		qtrans_updateSetting('text_field_filters', 		QT_ARRAY);
		qtrans_updateSetting('disable_header_css',		QT_BOOLEAN);
		qtrans_updateSetting('disable_client_cookies',	QT_BOOLEAN);
		qtrans_updateSetting('use_secure_cookie', 		QT_BOOLEAN);
		qtrans_updateSetting('filter_all_options', 		QT_BOOLEAN);
		
		if (isset($_POST['allowed_custom_post_types']))
		{
			$acpt = explode(',', trim(trim($_POST['allowed_custom_post_types']), ','));
			$acpt = array_map('trim', $acpt);
			$q_config['allowed_custom_post_types'] = $acpt;
			$acpt = implode(',', $acpt);
			update_option('mqtranslate_allowed_custom_post_types', $acpt);
		}
		
		if(isset($_POST['update_mo_now']) && $_POST['update_mo_now']=='1' && qtrans_updateGettextDatabases(true))
			$message = __('Gettext databases updated.', 'mqtranslate');
	}
	
	if(isset($_POST['original_lang'])) {
		// validate form input
		if($_POST['language_na_message']=='')		$error = __('The Language must have a Not-Available Message!', 'mqtranslate');
		if(strlen($_POST['language_locale'])<2)		$error = __('The Language must have a Locale!', 'mqtranslate');
		if($_POST['language_name']=='')				$error = __('The Language must have a name!', 'mqtranslate');
		if(strlen($_POST['language_code'])!=2)		$error = __('Language Code has to be 2 characters long!', 'mqtranslate');
		if($_POST['original_lang']==''&&$error=='') {
			// new language
			if(isset($q_config['language_name'][$_POST['language_code']])) {
				$error = __('There is already a language with the same Language Code!', 'mqtranslate');
			} 
		} 
		if($_POST['original_lang']!=''&&$error=='') {
			// language update
			if($_POST['language_code']!=$_POST['original_lang']&&isset($q_config['language_name'][$_POST['language_code']])) {
				$error = __('There is already a language with the same Language Code!', 'mqtranslate');
			} else {
				// remove old language
				unset($q_config['language_name'][$_POST['original_lang']]);
				unset($q_config['flag'][$_POST['original_lang']]);
				unset($q_config['locale'][$_POST['original_lang']]);
				unset($q_config['date_format'][$_POST['original_lang']]);
				unset($q_config['time_format'][$_POST['original_lang']]);
				unset($q_config['not_available'][$_POST['original_lang']]);
				if(in_array($_POST['original_lang'],$q_config['enabled_languages'])) {
					// was enabled, so set modified one to enabled too
					for($i = 0; $i < sizeof($q_config['enabled_languages']); $i++) {
						if($q_config['enabled_languages'][$i] == $_POST['original_lang']) {
							$q_config['enabled_languages'][$i] = $_POST['language_code'];
						}
					}
				}
				if($_POST['original_lang']==$q_config['default_language'])
					// was default, so set modified the default
					$q_config['default_language'] = $_POST['language_code'];
			}
		}
		if(get_magic_quotes_gpc()) {
				if(isset($_POST['language_date_format'])) $_POST['language_date_format'] = stripslashes($_POST['language_date_format']);
				if(isset($_POST['language_time_format'])) $_POST['language_time_format'] = stripslashes($_POST['language_time_format']);
		}
		if($error=='') {
			// everything is fine, insert language
			$q_config['language_name'][$_POST['language_code']] = $_POST['language_name'];
			$q_config['flag'][$_POST['language_code']] = $_POST['language_flag'];
			$q_config['locale'][$_POST['language_code']] = $_POST['language_locale'];
			$q_config['date_format'][$_POST['language_code']] = $_POST['language_date_format'];
			$q_config['time_format'][$_POST['language_code']] = $_POST['language_time_format'];
			$q_config['not_available'][$_POST['language_code']] = $_POST['language_na_message'];
		}
		if($error!=''||isset($_GET['edit'])) {
			// get old values in the form
			$original_lang = $_POST['original_lang'];
			$language_code = $_POST['language_code'];
			$language_name = $_POST['language_name'];
			$language_locale = $_POST['language_locale'];
			$language_date_format = $_POST['language_date_format'];
			$language_time_format = $_POST['language_time_format'];
			$language_na_message = $_POST['language_na_message'];
			$language_flag = $_POST['language_flag'];
			$language_default = $_POST['language_default'];
		}
	} elseif(isset($_GET['convert'])){
		// update language tags
		global $wpdb;
		$wpdb->show_errors();
		foreach($q_config['enabled_languages'] as $lang) {
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_title = REPLACE(post_title, "[lang_'.$lang.']","<!--:'.$lang.'-->")');
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_title = REPLACE(post_title, "[/lang_'.$lang.']","<!--:-->")');
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_content = REPLACE(post_content, "[lang_'.$lang.']","<!--:'.$lang.'-->")');
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_content = REPLACE(post_content, "[/lang_'.$lang.']","<!--:-->")');
		}
		$message = "Database Update successful!";
	} elseif(isset($_GET['markdefault'])){
		// update language tags
		global $wpdb;
		$wpdb->show_errors();
		$result = $wpdb->get_results('SELECT ID, post_title, post_content FROM '.$wpdb->posts.' WHERE NOT (post_content LIKE "%<!--:-->%" OR post_title LIKE "%<!--:-->%")');
		foreach($result as $post) {
			$content = qtrans_split($post->post_content);
			$title = qtrans_split($post->post_title);
			foreach($q_config['enabled_languages'] as $language) {
				if($language != $q_config['default_language']) {
					$content[$language] = "";
					$title[$language] = "";
				}
			}
			$content = qtrans_join($content);
			$title = qtrans_join($title);
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_content = "'.mysql_escape_string($content).'", post_title = "'.mysql_escape_string($title).'" WHERE ID='.$post->ID);
		}
		$message = "All Posts marked as default language!";
	} elseif(isset($_GET['edit'])){
		$original_lang = $_GET['edit'];
		$language_code = $_GET['edit'];
		$language_name = $q_config['language_name'][$_GET['edit']];
		$language_locale = $q_config['locale'][$_GET['edit']];
		$language_date_format = $q_config['date_format'][$_GET['edit']];
		$language_time_format = $q_config['time_format'][$_GET['edit']];
		$language_na_message = $q_config['not_available'][$_GET['edit']];
		$language_flag = $q_config['flag'][$_GET['edit']];
	} elseif(isset($_GET['delete'])) {
		// validate delete (protect code)
		if($q_config['default_language']==$_GET['delete'])
			$error = 'Cannot delete Default Language!';
		if(!isset($q_config['language_name'][$_GET['delete']])||strtolower($_GET['delete'])=='code')
			$error = 'No such language!';
		if($error=='') {
			// everything seems fine, delete language
			qtrans_disableLanguage($_GET['delete']);
			unset($q_config['language_name'][$_GET['delete']]);
			unset($q_config['flag'][$_GET['delete']]);
			unset($q_config['locale'][$_GET['delete']]);
			unset($q_config['date_format'][$_GET['delete']]);
			unset($q_config['time_format'][$_GET['delete']]);
			unset($q_config['not_available'][$_GET['delete']]);
		}
	} elseif(isset($_GET['enable'])) {
		// enable validate
		if(!qtrans_enableLanguage($_GET['enable'])) {
			$error = __('Language is already enabled or invalid!', 'mqtranslate');
		}
	} elseif(isset($_GET['disable'])) {
		// enable validate
		if($_GET['disable']==$q_config['default_language'])
			$error = __('Cannot disable Default Language!', 'mqtranslate');
		if(!qtrans_isEnabled($_GET['disable']))
		if(!isset($q_config['language_name'][$_GET['disable']]))
			$error = __('No such language!', 'mqtranslate');
		// everything seems fine, disable language
		if($error=='' && !qtrans_disableLanguage($_GET['disable'])) {
			$error = __('Language is already disabled!', 'mqtranslate');
		}
	} elseif(isset($_GET['moveup'])) {
		$languages = qtrans_getSortedLanguages();
		$message = __('No such language!', 'mqtranslate');
		foreach($languages as $key => $language) {
			if($language==$_GET['moveup']) {
				if($key==0) {
					$message = __('Language is already first!', 'mqtranslate');
					break;
				}
				$languages[$key] = $languages[$key-1];
				$languages[$key-1] = $language;
				$q_config['enabled_languages'] = $languages;
				$message = __('New order saved.', 'mqtranslate');
				break;
			}
		}
	} elseif(isset($_GET['movedown'])) {
		$languages = qtrans_getSortedLanguages();
		$message = __('No such language!', 'mqtranslate');
		foreach($languages as $key => $language) {
			if($language==$_GET['movedown']) {
				if($key==sizeof($languages)-1) {
					$message = __('Language is already last!', 'mqtranslate');
					break;
				}
				$languages[$key] = $languages[$key+1];
				$languages[$key+1] = $language;
				$q_config['enabled_languages'] = $languages;
				$message = __('New order saved.', 'mqtranslate');
				break;
			}
		}
	}
	
	$everything_fine = ((isset($_POST['submit'])||isset($_GET['delete'])||isset($_GET['enable'])||isset($_GET['disable'])||isset($_GET['moveup'])||isset($_GET['movedown']))&&$error=='');
	if($everything_fine) {
		// settings might have changed, so save
		qtrans_saveConfig();
		if(empty($message)) {
			$message = __('Options saved.', 'mqtranslate');
		}
	}
	if($q_config['auto_update_mo']) {
		if(!is_dir(WP_LANG_DIR) || !$ll = @fopen(trailingslashit(WP_LANG_DIR).'mqtranslate.test','a')) {
			$error = sprintf(__('Could not write to "%s", Gettext Databases could not be downloaded!', 'mqtranslate'), WP_LANG_DIR);
		} else {
			@fclose($ll);
			@unlink(trailingslashit(WP_LANG_DIR).'mqtranslate.test');
		}
	}
	// don't accidentally delete/enable/disable twice
	$clean_uri = preg_replace("/&(delete|enable|disable|convert|markdefault|moveup|movedown)=[^&#]*/i","",$_SERVER['REQUEST_URI']);
	$clean_uri = apply_filters('mqtranslate_clean_uri', $clean_uri);

// Generate XHTML

	?>
<?php if ($message) : ?>
<div id="message" class="updated fade"><p><strong><?php echo $message; ?></strong></p></div>
<?php endif; ?>
<?php if ($error!='') : ?>
<div id="message" class="error fade"><p><strong><?php echo $error; ?></strong></p></div>
<?php endif; ?>

<?php if(isset($_GET['edit'])) { ?>
<div class="wrap">
<h2><?php _e('Edit Language', 'mqtranslate'); ?></h2>
<form action="" method="post" id="mqtranslate-edit-language">
<?php qtrans_language_form($language_code, $language_code, $language_name, $language_locale, $language_date_format, $language_time_format, $language_flag, $language_na_message, $language_default, $original_lang); ?>
<p class="submit"><input type="submit" name="submit" value="<?php _e('Save Changes &raquo;', 'mqtranslate'); ?>" /></p>
</form>
</div>
<?php } else { ?>
<div class="wrap">
<h2><?php _e('Language Management (mqTranslate Configuration)', 'mqtranslate'); ?></h2> 
<div class="tablenav"><?php printf(__('For help on how to configure mqTranslate correctly, take a look at the <a href="%1$s">qTranslate FAQ</a> and the <a href="%2$s">Support Forum</a>.', 'mqtranslate'), 'http://www.qianqin.de/qtranslate/faq/', 'http://www.qianqin.de/qtranslate/forum/viewforum.php?f=3'); ?></div>
	<form action="<?php echo $clean_uri;?>" method="post">
		<?php qtrans_admin_section_start(__('General Settings', 'mqtranslate'), 'general'); ?>
		<table class="form-table" id="qtranslate-admin-general">
			<tr>
				<th scope="row"><?php _e('Default Language / Order', 'mqtranslate') ?></th>
				<td>
					<fieldset><legend class="hidden"><?php _e('Default Language', 'mqtranslate') ?></legend>
				<?php
					foreach ( qtrans_getSortedLanguages() as $key => $language ) {
						echo "\t<label title='" . $q_config['language_name'][$language] . "'><input type='radio' name='default_language' value='" . $language . "'";
						if ( $language == $q_config['default_language'] ) {
							echo " checked='checked'";
						}
						echo ' />';
						echo ' <a href="'.add_query_arg('moveup', $language, $clean_uri).'"><img src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/arrowup.png" alt="up" /></a>';
						echo ' <a href="'.add_query_arg('movedown', $language, $clean_uri).'"><img src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/arrowdown.png" alt="down" /></a>';
						echo ' <img src="' . trailingslashit(WP_CONTENT_URL) .$q_config['flag_location'].$q_config['flag'][$language] . '" alt="' . $q_config['language_name'][$language] . '" /> ';
						echo ' '.$q_config['language_name'][$language] . "</label><br />\n";
					}
				?>
					</br>
					<small><?php printf(__('Choose the default language of your blog. This is the language which will be shown on %s. You can also change the order the languages by clicking on the arrows above.', 'mqtranslate'), get_bloginfo('url')); ?></small>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Hide Untranslated Content', 'mqtranslate');?></th>
				<td>
					<label for="hide_untranslated"><input type="checkbox" name="hide_untranslated" id="hide_untranslated" value="1"<?php echo ($q_config['hide_untranslated'])?' checked="checked"':''; ?>/> <?php _e('Hide Content which is not available for the selected language.', 'mqtranslate'); ?></label>
					<br/>
					<small>
					<?php _e('When checked, posts will be hidden if the content is not available for the selected language. If unchecked, a message will appear showing all the languages the content is available in.', 'mqtranslate'); ?>
					<?php _e('This function will not work correctly if you installed mqTranslate on a blog with existing entries. In this case you will need to take a look at "Convert Database" under "Advanced Settings".', 'mqtranslate'); ?>
					
					<br /><br />
					<label for="show_displayed_language_prefix"><input type="checkbox" name="show_displayed_language_prefix" id="show_displayed_language_prefix" value="1"<?php echo $q_config['show_displayed_language_prefix'] ? ' checked="checked"' : ''; ?>/> <?php _e('Show displayed language prefix when Content is not available for the selected language.', 'mqtranslate'); ?></label>
					</small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Detect Browser Language', 'mqtranslate');?></th>
				<td>
					<label for="detect_browser_language"><input type="checkbox" name="detect_browser_language" id="detect_browser_language" value="1"<?php echo ($q_config['detect_browser_language'])?' checked="checked"':''; ?>/> <?php _e('Detect the language of the browser and redirect accordingly.', 'mqtranslate'); ?></label>
					<br/>
					<small>
					<?php _e('When the frontpage is visited via bookmark/external link/type-in, the visitor will be forwarded to the correct URL for the language specified by his browser.', 'mqtranslate'); ?>
					</small>
				</td>
			</tr>
		</table>
		<?php
			qtrans_admin_section_end('general');
			qtrans_admin_section_start(__('Advanced Settings', 'mqtranslate'), 'advanced');
		?>
		<table class="form-table" id="qtranslate-admin-advanced" style="display: none">
			<tr>
				<th scope="row"><?php _e('URL Modification Mode', 'mqtranslate') ?></th>
				<td>
					<fieldset><legend class="hidden"><?php _e('URL Modification Mode', 'mqtranslate') ?></legend>
						<label title="Pre-Path Mode"><input type="radio" name="url_mode" value="<?php echo QT_URL_PATH; ?>" <?php echo ($q_config['url_mode']==QT_URL_PATH)?"checked=\"checked\"":""; ?> /> <?php _e('Use Pre-Path Mode (Default, puts /en/ in front of URL) - SEO friendly', 'mqtranslate'); ?></label><br />
						<label title="Pre-Domain Mode"><input type="radio" name="url_mode" value="<?php echo QT_URL_DOMAIN; ?>" <?php echo ($q_config['url_mode']==QT_URL_DOMAIN)?"checked=\"checked\"":""; ?> /> <?php _e('Use Pre-Domain Mode (uses http://en.yoursite.com) - You will need to configure sub-domains on your site.', 'mqtranslate'); ?></label><br />
						<label title="Query Mode"><input type="radio" name="url_mode" value="<?php echo QT_URL_QUERY; ?>" <?php echo ($q_config['url_mode']==QT_URL_QUERY)?"checked=\"checked\"":""; ?> /> <?php _e('Use Query Mode (?lang=en) - Least SEO friendly, not recommended', 'mqtranslate'); ?></label><br />
					</fieldset>
					<small>
					<?php _e('Pre-Path and Pre-Domain mode will only work with mod_rewrite/pretty permalinks. Additional Configuration is needed for Pre-Domain mode!', 'mqtranslate'); ?>
					</small>
					<br/><br/>
					<label for="hide_default_language"><input type="checkbox" name="hide_default_language" id="hide_default_language" value="1"<?php echo ($q_config['hide_default_language'])?' checked="checked"':''; ?>/> <?php _e('Hide URL language information for default language.', 'mqtranslate'); ?></label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Flag Image Path', 'mqtranslate');?></th>
				<td>
					<?php echo trailingslashit(WP_CONTENT_URL); ?><input type="text" name="flag_location" id="flag_location" value="<?php echo $q_config['flag_location']; ?>" style="width:50%"/>
					<br/>
					<small><?php _e('Path to the flag images under wp-content, with trailing slash. (Default: plugins/mqtranslate/flags/)', 'mqtranslate'); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Ignore Links', 'mqtranslate');?></th>
				<td>
					<input type="text" name="ignore_file_types" id="ignore_file_types" value="<?php echo $q_config['ignore_file_types']; ?>" style="width:100%"/>
					<br/>
					<small><?php _e('Don\'t convert Links to files of the given file types. (Default: gif,jpg,jpeg,png,pdf,swf,tif,rar,zip,7z,mpg,divx,mpeg,avi,css,js)', 'mqtranslate'); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Remove plugin CSS from head', 'mqtranslate'); ?></th>
				<td>
					<label for="disable_header_css"><input type="checkbox" name="disable_header_css" id="disable_header_css" value="1"<?php echo empty($q_config['disable_header_css']) ? '' : ' checked="checked"' ?> /> <?php _e('Remove inline CSS code added by plugin from the head', 'mqtranslate'); ?></label>
					<br />
					<small><?php _e('This will remove default styles applyied to mqTranslate Language Chooser', 'mqtranslate') ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Cookie Settings', 'mqtranslate'); ?></th>
				<td>
					<label for="disable_client_cookies"><input type="checkbox" name="disable_client_cookies" id="disable_client_cookies" value="1"<?php echo empty($q_config['disable_client_cookies']) ? '' : ' checked="checked"' ?> /> <?php _e('Disable all client cookies', 'mqtranslate'); ?> </label>
					<!-- 
					<br />
					<small><?php _e("If checked, language will not be saved for visitors between sessions.", 'mqtranslate') ?></small>
					-->
					<br /><br />
					
					<label for="use_secure_cookie"><input type="checkbox" name="use_secure_cookie" id="use_secure_cookie" value="1"<?php echo empty($q_config['use_secure_cookie']) ? '' : ' checked="checked"' ?> /> <?php _e('Make mqTranslate cookie available only through HTTPS connections', 'mqtranslate'); ?> </label>
					<br />
					<small><?php _e("Don't check this if you don't know what you're doing!", 'mqtranslate') ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Allowed Custom Post Types', 'mqtranslate'); ?></th>
				<td>
					<input type="text" name="allowed_custom_post_types" id="allowed_custom_post_types" value="<?php echo implode(', ', $q_config['allowed_custom_post_types']); ?>" style="width: 100%" />
					<br />
					<small><?php _e('Comma-separated list of the custom post types for which you want mqTranslate to keep multi-language values.', 'mqtranslate')?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Update Gettext Databases', 'mqtranslate');?></th>
				<td>
					<label for="auto_update_mo"><input type="checkbox" name="auto_update_mo" id="auto_update_mo" value="1"<?php echo ($q_config['auto_update_mo'])?' checked="checked"':''; ?>/> <?php _e('Automatically check for .mo-Database Updates of installed languages.', 'mqtranslate'); ?></label>
					<br/>
					<label for="update_mo_now"><input type="checkbox" name="update_mo_now" id="update_mo_now" value="1" /> <?php _e('Update Gettext databases now.', 'mqtranslate'); ?></label>
					<br/>
					<small><?php _e('mqTranslate will query the Wordpress Localisation Repository every week and download the latest Gettext Databases (.mo Files).', 'mqtranslate'); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Date / Time Conversion', 'mqtranslate');?></th>
				<td>
					<label><input type="radio" name="use_strftime" value="<?php echo QT_DATE; ?>" <?php echo ($q_config['use_strftime']==QT_DATE)?' checked="checked"':''; ?>/> <?php _e('Use emulated date function.', 'mqtranslate'); ?></label><br />
					<label><input type="radio" name="use_strftime" value="<?php echo QT_DATE_OVERRIDE; ?>" <?php echo ($q_config['use_strftime']==QT_DATE_OVERRIDE)?' checked="checked"':''; ?>/> <?php _e('Use emulated date function and replace formats with the predefined formats for each language.', 'mqtranslate'); ?></label><br />
					<label><input type="radio" name="use_strftime" value="<?php echo QT_STRFTIME; ?>" <?php echo ($q_config['use_strftime']==QT_STRFTIME)?' checked="checked"':''; ?>/> <?php _e('Use strftime instead of date.', 'mqtranslate'); ?></label><br />
					<label><input type="radio" name="use_strftime" value="<?php echo QT_STRFTIME_OVERRIDE; ?>" <?php echo ($q_config['use_strftime']==QT_STRFTIME_OVERRIDE)?' checked="checked"':''; ?>/> <?php _e('Use strftime instead of date and replace formats with the predefined formats for each language.', 'mqtranslate'); ?></label><br />
					<small><?php _e('Depending on the mode selected, additional customizations of the theme may be needed.', 'mqtranslate'); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Optimization Settings', 'mqtranslate'); ?></th>
				<td>
					<label for="filter_all_options"><input type="checkbox" name="filter_all_options" id="filter_all_options" value="1"<?php echo empty($q_config['filter_all_options']) ? '' : ' checked="checked"' ?> /> <?php _e('Filter all WordPress options', 'mqtranslate'); ?> </label>
					<br />
					<small><?php _e("If unchecked, some texts may not be translated anymore. However, disabling this feature may greatly improve loading times.", 'mqtranslate') ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Custom Fields', 'qtranslate');?></th>
				<td>
					<?php printf(__('Enter "id" or "class" attribute of text fields from your theme, which you wish to translate. This applies to post, page and media editors (/wp-admin/post*). To lookup "id" or "class", right-click on the field in the post or the page editor and choose "Inspect Element". Look for an attribute of the field named "id" or "class". Enter it below, as many as you need, space- or comma-separated. After saving configuration, these fields will start responding to the language switching buttons, and you can enter different text for each language. The input fields of type %s will be parsed using %s syntax, while single line text fields will use %s syntax. If you need to override this behaviour, prepend prefix %s or %s to the name of the field to specify which syntax to use. For more information, read %sFAQ%s.', 'qtranslate'),'\'textarea\'',esc_html('<!--:-->'),'[:]','\'<\'','\'[\'','<a href="https://wordpress.org/plugins/qtranslate-x/faq/">','</a>'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="text-align: right">id:</th>
				<td>
					<input type="text" name="custom_fields" id="qtrans_custom_fields" value="<?php echo implode(' ',$q_config['custom_fields']); ?>" style="width:100%"><br />
					<small><?php _e('The value of "id" attribute is normally unique within one page, otherwise the first field found, having an id specified, is picked up.', 'qtranslate'); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="text-align: right">class:</th>
				<td>
					<input type="text" name="custom_field_classes" id="qtrans_custom_field_classes" value="<?php echo implode(' ',$q_config['custom_field_classes']); ?>" style="width:100%"><br>
					<small><?php printf(__('All the fields of specified classes will respond to Language Switching Buttons. Be careful not to include a class, which would affect language-neutral fields. If you cannot uniquely identify a field needed neither by %s, nor by %s attribute, report the issue on %sSupport Forum%s', 'qtranslate'),'"id"', '"class"', '<a href="https://wordpress.org/support/plugin/qtranslate-x">','</a>'); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php echo __('Custom Filters', 'qtranslate');?></th>
				<td>
					<input type="text" name="text_field_filters" id="qtrans_text_field_filters" value="<?php echo implode(' ',$q_config['text_field_filters']); ?>" style="width:100%"><br>
					<small><?php printf(__('Names of filters (which are enabled on theme or other plugins via %s function) to add translation to. For more information, read %sFAQ%s.', 'qtranslate'),'apply_filters()','<a href="https://wordpress.org/plugins/qtranslate-x/faq/">','</a>'); ?></small>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Debugging Information', 'mqtranslate');?></th>
				<td>
					<p><?php printf(__('If you encounter any problems and you are unable to solve them yourself, you can visit the <a href="%s">Support Forum</a>. Posting the following Content will help other detect any misconfigurations.', 'mqtranslate'), 'http://www.qianqin.de/mqtranslate/forum/'); ?></p>
					<textarea readonly="readonly" id="mqtranslate_debug"><?php
						$q_config_copy = $q_config;
						// remove information to keep data anonymous and other not needed things
						unset($q_config_copy['url_info']);
						unset($q_config_copy['js']);
						unset($q_config_copy['windows_locale']);
						unset($q_config_copy['pre_domain']);
						unset($q_config_copy['term_name']);
						echo htmlspecialchars(print_r($q_config_copy, true));
					?></textarea>
				</td>
			</tr>
		</table>
		<?php qtrans_admin_section_end('advanced'); ?>
<?php do_action('mqtranslate_configuration', $clean_uri); ?>
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes', 'mqtranslate') ?>" />
		</p>
	</form>

</div>
<div class="wrap">

<h2><?php _e('Languages', 'mqtranslate') ?></h2>
<div id="col-container">

<div id="col-right">
<div class="col-wrap">

<table class="widefat">
	<thead>
	<tr>
<?php print_column_headers('language'); ?>
	</tr>
	</thead>

	<tfoot>
	<tr>
<?php print_column_headers('language', false); ?>
	</tr>
	</tfoot>

	<tbody id="the-list" class="qtranslate-language-list" class="list:cat">
<?php foreach($q_config['language_name'] as $lang => $language){ if($lang!='code') { ?>
	<tr>
		<td><img src="<?php echo trailingslashit(WP_CONTENT_URL).$q_config['flag_location'].$q_config['flag'][$lang]; ?>" alt="<?php echo $language; ?> Flag"></td>
		<td><?php echo $language; ?></td>
		<td><?php if(in_array($lang,$q_config['enabled_languages'])) { ?><a class="edit" href="<?php echo $clean_uri; ?>&disable=<?php echo $lang; ?>"><?php _e('Disable', 'mqtranslate'); ?></a><?php  } else { ?><a class="edit" href="<?php echo $clean_uri; ?>&enable=<?php echo $lang; ?>"><?php _e('Enable', 'mqtranslate'); ?></a><?php } ?></td>
		<td><a class="edit" href="<?php echo $clean_uri; ?>&edit=<?php echo $lang; ?>"><?php _e('Edit', 'mqtranslate'); ?></a></td>
		<td><?php if($q_config['default_language']==$lang) { ?><?php _e('Default', 'mqtranslate'); ?><?php  } else { ?><a class="delete" href="<?php echo $clean_uri; ?>&delete=<?php echo $lang; ?>"><?php _e('Delete', 'mqtranslate'); ?></a><?php } ?></td>
	</tr>
<?php }} ?>
	</tbody>
</table>
<p><?php _e('Enabling a language will cause mqTranslate to update the Gettext-Database for the language, which can take a while depending on your server\'s connection speed.','mqtranslate');?></p>
</div>
</div><!-- /col-right -->

<div id="col-left">
<div class="col-wrap">
<div class="form-wrap">
<h3><?php _e('Add Language', 'mqtranslate'); ?></h3>
<form name="addcat" id="addcat" method="post" class="add:the-list: validate">
<?php qtrans_language_form($language_code, $language_code, $language_name, $language_locale, $language_date_format, $language_time_format, $language_flag, $language_default, $language_na_message); ?>
<p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e('Add Language &raquo;', 'mqtranslate'); ?>" /></p>
</form></div>
</div>
</div><!-- /col-left -->

</div><!-- /col-container -->
<?php
}
}

/* Add a metabox in admin menu page */
function qtrans_nav_menu_metabox( $object )
{
	global $nav_menu_selected_id;

	$elems = array( '#qtransLangSwLM#' => __('Language Menu') );

	class qtransLangSwItems {
		public $db_id = 0;
		public $object = 'qtranslangsw';
		public $object_id;
		public $menu_item_parent = 0;
		public $type = 'custom';
		public $title;
		public $url;
		public $target = '';
		public $attr_title = '';
		public $classes = array();
		public $xfn = '';
	}

	$elems_obj = array();
	foreach ( $elems as $value => $title ) {
		$elems_obj[$title] = new qtransLangSwItems();
		$elems_obj[$title]->object_id	= esc_attr( $value );
		$elems_obj[$title]->title		= esc_attr( $title );
		$elems_obj[$title]->url			= esc_attr( $value );
	}

	$walker = new Walker_Nav_Menu_Checklist( array() );
?>
<div id="qtrans-langsw" class="qtranslangswdiv">
	<div id="tabs-panel-qtrans-langsw-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
		<ul id="qtrans-langswchecklist" class="list:qtrans-langsw categorychecklist form-no-clear">
			<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $elems_obj ), 0, (object)array( 'walker' => $walker ) ); ?>
		</ul>
	</div>
	<span class="list-controls hide-if-no-js">
		<a href="javascript:void(0);" class="help" onclick="jQuery( '#help-login-links' ).toggle();"><?php _e( 'Help' ); ?></a>
		<span class="hide-if-js" id="help-login-links"><br /><a name="help-login-links"></a>
		Menu item added is replaced with a sub-menu of available languages when menu is rendered. Depending on how your theme renders menu you may need to override and customize css entries .qtrans-lang-menu and .qtrans-lang-menu-item, originally defined in qtranslate.css.
		</span>
	</span>
	<p class="button-controls">
		<span class="add-to-menu">
			<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-qtrans-langsw-menu-item" id="submit-qtrans-langsw" />
			<span class="spinner"></span>
		</span>
	</p>
</div>
<?php
}

function qtrans_add_nav_menu_metabox()
{
	add_meta_box( 'add-qtrans-language-switcher', __( 'Language Switcher' ), 'qtrans_nav_menu_metabox', 'nav-menus', 'side', 'default' );
}
function qtrans_add_language_menu( $wp_admin_bar ) 
{
	global $q_config;
	if ( !is_admin() || !is_admin_bar_showing() )
		return;

	$wp_admin_bar->add_menu( array(
			'id'   => 'language',
			'parent' => 'top-secondary',
			//'meta' => array('class'),
			'title' => $q_config['language_name'][$q_config['language']]
		)
	);

	foreach($q_config['enabled_languages'] as $language)
	{
		$wp_admin_bar->add_menu( 
			array
			(
				'id'	 => $language,
				'parent' => 'language',
				'title'  => $q_config['language_name'][$language],
				'href'   => add_query_arg('lang', $language)
			)
		);
	}
}

add_filter('get_term', 'qtrans_useAdminTermLib',0);
add_filter('get_terms', 'qtrans_useAdminTermLib',0);

add_action('admin_head', 'qtrans_add_css');
add_action('admin_menu', 'qtrans_admin_head');
add_action('admin_head-nav-menus.php', 'qtrans_add_nav_menu_metabox');
add_action('admin_menu', 'qtrans_adminMenu');
add_action('admin_bar_menu', 'qtrans_add_language_menu', 999);
add_action('wp_before_admin_bar_render', 'qtrans_fixAdminBar');