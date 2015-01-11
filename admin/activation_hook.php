<?php // encoding: utf-8
/*
	Copyright 2014  qTranslate Team  (email : qTranslateTeam@gmail.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

function qtrans_admin_notice_deactivate_plugin($nm,$plugin)
{
	deactivate_plugins($plugin, true);
	
	$d=dirname($plugin);
	$link='<a href="https://wordpress.org/plugins/'.$d.'/" target="_blank">'.$nm.'</a>';
	$qtnm='mqTranslate';
	$qtlink='<a href="https://wordpress.org/plugins/mqtranslate/" target="_blank">'.$qtnm.'</a>';
	
	$f='qtrans_migrate_import_'.str_replace('-','_',dirname($plugin));
	$imported=false;
	if(function_exists($f)){
		global $wpdb;
		$options = $wpdb->get_col("SELECT `option_name` FROM {$wpdb->options} WHERE `option_name` LIKE 'mqtranslate_%'");
		if(empty($options)){
			$f();
			$imported=true;
		}
	}
	
	$msg = array();
	$msg[] = sprintf(__('Activation of plugin %s deactivated plugin %s since they cannot run simultaneously.','mqtranslate'),$qtlink,$link).' ';
	if ($imported)
		$msg[] = __('The compatible settings have been imported. Further tuning, import, export and reset of options can be done at Settings &gt; Languages configuration page.','mqtranslate');
	else
		$msg[] = __('You may import/export compatible settings on Settings &gt; Languages configuration page','mqtranslate');
	$msg[] = sprintf('<a class="button" href="">%s</a>', __('Continue', 'mqtranslate'));
	
	wp_die('<p>'.implode('</p><p>', $msg).'</p>');
}

function qtrans_activation_hook()
{
	// Check if other qTranslate forks are activated.
	if ( is_plugin_active( 'qtranslate/qtranslate.php' ) )
		qtrans_admin_notice_deactivate_plugin('qTranslate','qtranslate/qtranslate.php');

	if ( is_plugin_active( 'qtranslate-x/qtranslate.php' ) )
		qtrans_admin_notice_deactivate_plugin('qTranslate X','qtranslate-x/qtranslate.php');

	if ( is_plugin_active( 'qtranslate-xp/ppqtranslate.php' ) )
		qtrans_admin_notice_deactivate_plugin('qTranslate Plus','qtranslate-xp/ppqtranslate.php');

	if ( is_plugin_active( 'ztranslate/ztranslate.php' ) )
		qtrans_admin_notice_deactivate_plugin('zTranslate','ztranslate/ztranslate.php');
}

function qtrans_admin_notice_plugin_conflict($title,$plugin)
{
	if(!is_plugin_active($plugin)) return;
	$me='<a href="https://wordpress.org/plugins/mqtranslate/" style="color:blue" target="_blank">mqTranslate</a>';
	$link='<a href="https://wordpress.org/plugins/'.dirname($plugin).'/" style="color:magenta" target="_blank">'.$title.'</a>';
	echo '<div class="error"><p style="font-size: larger">';
	echo '<span style="color:red"><strong>'.__('Error').':</strong></span> '.sprintf(__('plugin %s cannot run concurrently with plugin %s. You may import and export compatible settings between %s and %s on Settings/<a href="%s">Languages</a> configuration page. Then you have to deactivate one of the plugins to continue.','mqtranslate'),$me,$link,'mqTranslate',$title,admin_url('options-general.php?page=mqtranslate'));
	$nonce=wp_create_nonce('deactivate-plugin_'.$plugin);
	echo '</p><p> &nbsp; &nbsp; &nbsp; &nbsp;<a class="button" href="'.admin_url('plugins.php?action=deactivate&plugin='.urlencode($plugin).'&plugin_status=all&paged=1&s&_wpnonce='.$nonce).'"><strong>'.__('Deactivate ').'<span style="color:magenta">'.$title.'</span></strong></a>';
	$nonce=wp_create_nonce('deactivate-plugin_mqtranslate/mqtranslate.php');
	echo ' &nbsp; &nbsp; &nbsp; &nbsp;<a class="button" href="'.admin_url('plugins.php?action=deactivate&plugin='.urlencode('mqtranslate/mqtranslate.php').'&plugin_status=all&paged=1&s&_wpnonce='.$nonce).'"><strong>'.__('Deactivate ').'<span style="color:blue">mqTranslate</span></strong></a>';
	echo '</p></div>';
}

function qtrans_admin_notices_plugin_conflicts()
{
	qtrans_admin_notice_plugin_conflict('qTranslate','qtranslate/qtranslate.php');
	qtrans_admin_notice_plugin_conflict('qTranslate X','qtranslate-x/qtranslate.php');
	qtrans_admin_notice_plugin_conflict('qTranslate Plus','qtranslate-xp/ppqtranslate.php');
	qtrans_admin_notice_plugin_conflict('zTranslate','ztranslate/ztranslate.php');
}
add_action('admin_notices', 'qtrans_admin_notices_plugin_conflicts');
?>
