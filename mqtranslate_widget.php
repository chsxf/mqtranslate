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

/* mqTranslate Widget */

// Language Select Code for non-Widget users
function qtrans_generateLanguageSelectCode($style='', $id='') {
	if (function_exists('is_plugin_active') && is_plugin_active('qtranslate-slug/qtranslate-slug.php'))
		qts_language_menu($style, array( 'id' => $id, 'short' => '' ) );
	else
	{
		global $q_config;
		if($style=='') $style='text';
		if(is_bool($style)&&$style) $style='image';
		if(is_404()) $url = get_option('home'); else $url = '';
		if($id=='') $id = 'mqtranslate';
		$id .= '-chooser';
		$flag_location=trailingslashit(WP_CONTENT_URL).$q_config['flag_location'];
		switch($style) {
			case 'image':
			case 'text':
			case 'dropdown':
				echo PHP_EOL.'<ul class="qtrans_language_chooser" id="'.$id.'">'.PHP_EOL;
				foreach (qtrans_getSortedLanguages() as $language) {
					$classes = array('lang-'.$language);
					if($language == $q_config['language'])
						$classes[] = 'active';
					echo '<li class="'. implode(' ', $classes) .'"><a href="'.qtrans_convertURL($url, $language).'"';
					// set hreflang
					echo ' hreflang="'.$language.'" title="'.$q_config['language_name'][$language].'"';
					if($style=='image')
						echo ' class="qtrans_flag qtrans_flag_'.$language.'"';
					echo '><span';
					if($style=='image')
						echo ' style="display:none"';
					echo '>'.$q_config['language_name'][$language].'</span></a></li>'.PHP_EOL;
				}
				echo "</ul><div class=\"qtrans_widget_end\"></div>";
				if($style=='dropdown') {
					echo "<script type=\"text/javascript\">\n// <![CDATA[\r\n";
					echo "var lc = document.getElementById('".$id."');\n";
					echo "var s = document.createElement('select');\n";
					echo "s.id = 'qtrans_select_".$id."';\n";
					echo "lc.parentNode.insertBefore(s,lc);\n";
					// create dropdown fields for each language
					foreach(qtrans_getSortedLanguages() as $language) {
						echo qtrans_insertDropDownElement($language, qtrans_convertURL($url, $language), $id);
					}
					// hide html language chooser text
					echo "s.onchange = function() { document.location.href = this.value;}\n";
					echo "lc.style.display='none';\n";
					echo "// ]]>\n</script>\n";
				}
				break;
			case 'both':
				echo '<ul class="qtrans_language_chooser" id="'.$id.'">';
				foreach(qtrans_getSortedLanguages() as $language) {
					echo '<li';
					if($language == $q_config['language'])
						echo ' class="active"';
					echo '><a href="'.qtrans_convertURL($url, $language).'"';
					echo ' class="qtrans_flag_'.$language.' qtrans_flag_and_text" title="'.$q_config['language_name'][$language].'"';
					echo '><span>'.$q_config['language_name'][$language].'</span></a></li>';
				}
				echo "</ul><div class=\"qtrans_widget_end\"></div>";
				break;
		}
	}
}

function qtrans_widget_init() {
	if (function_exists('is_plugin_active') && is_plugin_active('qtranslate-slug/qtranslate-slug.php'))
		require_once('mqtranslate_widget_qtranslate-slug.php');
	else
		require_once('mqtranslate_widget.inc.php');
	register_widget('mqTranslateWidget');
	do_action('qtrans_widget_init');
}
?>