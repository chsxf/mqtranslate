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

function qtrans_migrate_options_update($nm_to,$nm_from)
{
	global $wpdb;
	$option_names = $wpdb->get_col("SELECT `option_name` FROM {$wpdb->options} WHERE `option_name` LIKE '$nm_to\_%'");
	foreach ($option_names as $name)
	{
		if(strpos($name,'_flag_location')>0) continue;
		$nm = str_replace($nm_to,$nm_from,$name);
		$value=get_option($nm);
		if($value===FALSE) continue;
		update_option($name,$value);
	}
}

function qtrans_migrate_options_copy($nm_to,$nm_from)
{
	global $wpdb;
	$options = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE `option_name` LIKE '$nm_from\_%'");
	foreach ($options as $option)
	{
		$name=$option->option_name;
		$value=$option->option_value;
		if(strpos($name,'_flag_location')>0) continue;
		$nm = str_replace($nm_from,$nm_to,$name);
		update_option($nm,$value);
	}
}

function qtrans_migrate_import_qtranslate() { qtrans_migrate_options_update('mqtranslate','qtranslate'); }
function qtrans_migrate_export_qtranslate() { qtrans_migrate_options_copy('qtranslate','mqtranslate'); }

function qtrans_migrate_import_qtranslate_x() { qtrans_migrate_import_qtranslate(); }
function qtrans_migrate_export_qtranslate_x() { qtrans_migrate_export_qtranslate(); }

function qtrans_migrate_import_qtranslate_xp(){ qtrans_migrate_options_update('mqtranslate','ppqtranslate'); }
function qtrans_migrate_export_qtranslate_xp(){ qtrans_migrate_options_copy('ppqtranslate','mqtranslate'); }

function qtrans_migrate_import_ztranslate() { qtrans_migrate_import_qtranslate(); }
function qtrans_migrate_export_ztranslate() { qtrans_migrate_export_qtranslate(); }

function qtrans_migrate_plugin($plugin){
	$var=$plugin.'-migration';
	if (!isset($_POST[$var]) || !in_array($_POST[$var], array('import', 'export'))) return;
	qtrans_loadConfig();
	qtrans_saveConfig();
	$f='qtrans_migrate_'.$_POST[$var].'_'.str_replace('-','_',$plugin);
	$f();
}

function qtrans_migrate_plugins()
{
	qtrans_migrate_plugin('qtranslate');
	qtrans_migrate_plugin('qtranslate-x');
	qtrans_migrate_plugin('qtranslate-xp');
	qtrans_migrate_plugin('ztranslate');
}
add_action('qtrans_init_begin','qtrans_migrate_plugins',11);

function qtrans_add_row_migrate($nm,$plugin) {
	if(!file_exists(WP_CONTENT_DIR.'/plugins/'.$plugin)) return;
?>
<tr valign="top" id="qtranslate-<?php echo $plugin; ?>">
	<th scope="row"><?php _e('Plugin');?> <a href="https://wordpress.org/plugins/<?php echo $plugin; ?>/" target="_blank"><?php echo $nm; ?></a></th>
	<td>
<?php
	if($plugin=='qtranslate' || $plugin=='ztranslate'){
		_e('There is no need to migrate any setting, the database schema is compatible with this plugin.');
	}else{
?>
		<label for="qtranslate_no_migration"><input type="radio" name="<?php echo $plugin; ?>-migration" id="<?php echo $plugin; ?>_no_migration" value="none" checked /> <?php _e('Do not migrate any setting', 'mqtranslate'); ?></label>
		<br/>
		<label for="qtranslate_import_migration"><input type="radio" name="<?php echo $plugin; ?>-migration" id="qtranslate_import_migration" value="import" /> <?php echo __('Import settings from ', 'mqtranslate').$nm; ?></label>
		<br/>
		<label for="qtranslate_export_migration"><input type="radio" name="<?php echo $plugin; ?>-migration" id="qtranslate_export_migration" value="export" /> <?php echo __('Export settings to ', 'mqtranslate').$nm; ?></label>
<?php } ?>
	</td>
</tr>
<?php
}

function qtrans_admin_section_import_export($request_uri)
{
	qtrans_admin_section_start(__('Import').'/'.__('Export'),'import');
?>
	<table class="form-table" id="qtranslate-admin-import" style="display: none">
		<tr valign="top" id="qtranslate-convert-database">
			<th scope="row"><?php _e('Convert Database', 'mqtranslate');?></th>
			<td>
				<?php printf(__('If you are updating from qTranslate 1.x or Polyglot, <a href="%s">click here</a> to convert posts to the new language tag format.', 'mqtranslate'), $request_uri.'&convert=true'); ?>
				<?php printf(__('If you have installed qTranslate for the first time on a Wordpress with existing posts, you can either go through all your posts manually and save them in the correct language or <a href="%s">click here</a> to mark all existing posts as written in the default language.', 'mqtranslate'), $request_uri.'&markdefault=true'); ?>
				<?php _e('Both processes are <b>irreversible</b>! Be sure to make a full database backup before clicking one of the links.', 'mqtranslate'); ?>
			</td>
		</tr>
		<?php qtrans_add_row_migrate('qTranslate','qtranslate'); ?>
		<?php qtrans_add_row_migrate('qTranslate X','qtranslate-x'); ?>
		<?php qtrans_add_row_migrate('qTranslate Plus','qtranslate-xp'); ?>
		<?php qtrans_add_row_migrate('zTranslate','ztranslate'); ?>
		<tr valign="top">
			<th scope="row"><?php _e('Reset qTranslate', 'mqtranslate');?></th>
			<td>
				<label for="qtranslate_reset"><input type="checkbox" name="qtranslate_reset" id="qtranslate_reset" value="1"/> <?php _e('Check this box and click Save Changes to reset all mqTranslate settings.', 'mqtranslate'); ?></label>
				<br/>
				<label for="qtranslate_reset2"><input type="checkbox" name="qtranslate_reset2" id="qtranslate_reset2" value="1"/> <?php _e('Yes, I really want to reset mqTranslate.', 'mqtranslate'); ?></label>
				<br/>
				<label for="qtranslate_reset3"><input type="checkbox" name="qtranslate_reset3" id="qtranslate_reset3" value="1"/> <?php _e('Also delete Translations for Categories/Tags/Link Categories.', 'mqtranslate'); ?></label>
				<br/>
				<small><?php _e('If something isn\'t working correctly, you can always try to reset all mqTranslate settings. A Reset won\'t delete any posts but will remove all settings (including all languages added).', 'mqtranslate'); ?></small>
			</td>
		</tr>
	</table>
<?php
	qtrans_admin_section_end('import');
}
add_action('qtranslate_configuration', 'qtrans_admin_section_import_export', 9);
?>
