<?php // encoding: utf-8

/*Copyright 2008Qian Qin(email : mail@qianqin.de)

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
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* mqTranslate Hooks */

function qtrans_localeForCurrentLanguage($locale) {
	if (!defined('QTRANS_INIT'))
		return $locale;
	
	global $q_config;
	// try to figure out the correct locale
	$lang = $q_config['language'];
	$locale_lang = $q_config['locale'][$lang];
	$locale = array();
	$locale[] = $locale_lang.".utf8";
	$locale[] = $locale_lang."@euro";
	$locale[] = $locale_lang;
	$locale[] = $q_config['windows_locale'][$lang];
	$locale[] = $lang;
	
	// return the correct locale and most importantly set it (wordpress doesn't, which is bad)
	// only set LC_TIME as everyhing else doesn't seem to work with windows
	setlocale(LC_TIME, $locale);
	
	return $$locale_lang;
}

function qtrans_useCurrentLanguageIfNotFoundShowAvailable($content) {
	if (!defined('QTRANS_INIT'))
		return $content;
	
	global $q_config;
	return qtrans_use($q_config['language'], $content, true);
}

function qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($content) {
	if (!defined('QTRANS_INIT'))
		return $content;
	
	global $q_config;
	return qtrans_use($q_config['language'], $content, false);
}

function qtrans_useDefaultLanguage($content) {
	global $q_config;
	return qtrans_use($q_config['default_language'], $content, false);
}

function qtrans_excludePages($pages) {
	global $wpdb, $q_config;
	static $exclude = 0;
	if(!$q_config['hide_untranslated']) return $pages;
	if(is_array($exclude)) return array_merge($exclude, $pages);
	$query = "SELECT id FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' AND NOT ($wpdb->posts.post_title LIKE '%<!--:".qtrans_getLanguage()."-->%')" ;
	$hide_pages = $wpdb->get_results($query);
	$exclude = array();
	foreach($hide_pages as $page) {
		$exclude[] = $page->id;
	}
	return array_merge($exclude, $pages);
}

function qtrans_links($links, $file){ // copied from Sociable Plugin
	//Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(dirname(__FILE__).'/mqtranslate.php');
	
	if ($file == $this_plugin){
		$settings_link = '<a href="options-general.php?page=mqtranslate">' . __('Settings', 'mqtranslate') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}

function qtrans_languageColumnHeader($columns){
	$new_columns = array();
	if(isset($columns['cb']))			$new_columns['cb'] = '';
	if(isset($columns['title']))		$new_columns['title'] = '';
	if(isset($columns['author']))		$new_columns['author'] = '';
	if(isset($columns['categories']))	$new_columns['categories'] = '';
	if(isset($columns['tags']))			$new_columns['tags'] = '';
	$new_columns['language'] = __('Languages', 'mqtranslate');
	return array_merge($new_columns, $columns);;
}

function qtrans_languageColumn($column) {
	global $q_config, $post;
	if ($column == 'language') {
		$available_languages = qtrans_getAvailableLanguages($post->post_content);
		$missing_languages = array_diff($q_config['enabled_languages'], $available_languages);
		$available_languages_name = array();
		$missing_languages_name = array();
		foreach($available_languages as $language) {
			$available_languages_name[] = $q_config['language_name'][$language];
		}
		$available_languages_names = join(", ", $available_languages_name);
		
		echo apply_filters('qtranslate_available_languages_names',$available_languages_names);
		do_action('qtranslate_languageColumn', $available_languages, $missing_languages);
	}
	return $column;
}

function qtrans_versionLocale() {
	return 'en_US';
}

function qtrans_useRawTitle($title, $raw_title = '', $context = 'save') {
	if($raw_title=='') $raw_title = $title;
	if('save'==$context) {
		$raw_title = qtrans_useDefaultLanguage($raw_title);
		$title = remove_accents($raw_title);
	}
	return $title;
}

function qtrans_checkCanonical($redirect_url, $requested_url) {
	// fix canonical conflicts with language urls
	if(qtrans_convertURL($redirect_url)==qtrans_convertURL($requested_url)) 
		return false;
	return $redirect_url;
}

function qtrans_fixAdminBar($wp_admin_bar) {
	global $wp_admin_bar;
	$nodes = $wp_admin_bar->get_nodes();
	if (is_array($nodes)) {
		foreach($wp_admin_bar->get_nodes() as $node)
			$wp_admin_bar->add_node(qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($node));
	}
}

// Hooks for Plugin compatibility

function qtrans_supercache_dir($uri) {
	global $q_config;
	if(isset($q_config['url_info']['original_url'])) {
		$uri = $q_config['url_info']['original_url'];
	} else {
		$uri = $_SERVER['REQUEST_URI'];
	}
	$uri = preg_replace('/[ <>\'\"\r\n\t\(\)]/', '', str_replace( '/index.php', '/', str_replace( '..', '', preg_replace("/(\?.*)?$/", '', $uri ) ) ) );
	$uri = str_replace( '\\', '', $uri );
	$uri = strtolower(preg_replace('/:.*$/', '',  $_SERVER["HTTP_HOST"])) . $uri; // To avoid XSS attacs
	return $uri;
}
add_filter('supercache_dir',					'qtrans_supercache_dir',0);

function qtrans_gettext($translated_text) {
	global $q_config;
	if (!isset($q_config['language']))
		return $translated_text;
	return qtrans_use($q_config['language'], $translated_text, false);
}

function qtrans_gettext_with_context($translated_text) {
	global $q_config;
	if(!isset($q_config['language']))
		return $translated_text;
	return qtrans_use($q_config['language'], $translated_text, false);
}

// Hooks (Actions)
add_action('widgets_init',					'qtrans_widget_init'); 
add_action('plugins_loaded',				'qtrans_init', 2);

// Hooks (execution time critical filters) 
add_filter('gettext',						'qtrans_gettext',0);
add_filter('gettext_with_context',			'qtrans_gettext_with_context',0);
add_filter('the_content',					'qtrans_useCurrentLanguageIfNotFoundShowAvailable', 0);
add_filter('the_excerpt',					'qtrans_useCurrentLanguageIfNotFoundShowAvailable', 0);
add_filter('the_excerpt_rss',				'qtrans_useCurrentLanguageIfNotFoundShowAvailable', 0);
add_filter('sanitize_title',				'qtrans_useRawTitle',0, 3);
add_filter('comment_moderation_subject',	'qtrans_useDefaultLanguage',0);
add_filter('comment_moderation_text',		'qtrans_useDefaultLanguage',0);
add_filter('get_comment_date',				'qtrans_dateFromCommentForCurrentLanguage',0,2);
add_filter('get_comment_time',				'qtrans_timeFromCommentForCurrentLanguage',0,4);
add_filter('get_post_modified_time',		'qtrans_timeModifiedFromPostForCurrentLanguage',0,3);
add_filter('get_the_time',					'qtrans_timeFromPostForCurrentLanguage',0,3);
add_filter('get_the_date',					'qtrans_dateFromPostForCurrentLanguage',0,2);
add_filter('get_the_modified_date',			'qtrans_dateModifiedFromPostForCurrentLanguage',0,2);
add_filter('locale',						'qtrans_localeForCurrentLanguage',99);
add_filter('the_title',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage', 0);
add_filter('post_title',					'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage', 0);
add_filter('term_name',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('tag_rows',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('list_cats',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_list_categories',			'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_dropdown_cats',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_title',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('single_post_title',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('bloginfo',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_others_drafts',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_bloginfo_rss',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_wp_title_rss',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_title_rss',					'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_title_rss',					'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_content_rss',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('gettext',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('get_pages',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('category_description',			'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('bloginfo_rss',					'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_category_rss',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('wp_generate_tag_cloud',			'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('term_links-post_tag',			'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('link_name',						'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('link_description',				'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('pre_option_rss_language',		'qtrans_getLanguage',0);
add_filter('the_author',					'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter( "_wp_post_revision_field_post_title", 'qtrans_showAllSeperated', 0);
add_filter( "_wp_post_revision_field_post_content", 'qtrans_showAllSeperated', 0);
add_filter( "_wp_post_revision_field_post_excerpt", 'qtrans_showAllSeperated', 0);
add_filter('get_the_author_description', 	'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');

add_filter('_wp_post_revision_field_post_title', 'qtrans_showAllSeperated', 0);
add_filter('_wp_post_revision_field_post_content', 'qtrans_showAllSeperated', 0);
add_filter('_wp_post_revision_field_post_excerpt', 'qtrans_showAllSeperated', 0);

// Hooks (execution time non-critical filters) 
add_filter('author_feed_link',				'qtrans_convertURL');
add_filter('author_link',					'qtrans_convertURL');
add_filter('author_feed_link',				'qtrans_convertURL');
add_filter('day_link',						'qtrans_convertURL');
add_filter('get_comment_author_url_link',	'qtrans_convertURL');
add_filter('month_link',					'qtrans_convertURL');
add_filter('page_link',						'qtrans_convertURL');
add_filter('post_link',						'qtrans_convertURL');
add_filter('year_link',						'qtrans_convertURL');
add_filter('category_feed_link',			'qtrans_convertURL');
add_filter('category_link',					'qtrans_convertURL');
add_filter('tag_link',						'qtrans_convertURL');
add_filter('term_link',						'qtrans_convertURL');
add_filter('the_permalink',					'qtrans_convertURL');
add_filter('feed_link',						'qtrans_convertURL');
add_filter('post_comments_feed_link',		'qtrans_convertURL');
add_filter('tag_feed_link',					'qtrans_convertURL');
add_filter('get_pagenum_link',				'qtrans_convertURL');
add_filter('manage_posts_columns',			'qtrans_languageColumnHeader');
add_filter('manage_posts_custom_column',	'qtrans_languageColumn');
add_filter('manage_pages_columns',			'qtrans_languageColumnHeader');
add_filter('manage_pages_custom_column',	'qtrans_languageColumn');
add_filter('wp_list_pages_excludes',	    'qtrans_excludePages');
add_filter('comment_notification_text', 	'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');
add_filter('comment_notification_headers',	'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');
add_filter('comment_notification_subject',	'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');

add_filter('bloginfo_url',					'qtrans_convertBlogInfoURL',10,2);
add_filter('plugin_action_links', 			'qtrans_links', 10, 2);
add_filter('manage_language_columns',		'qtrans_language_columns');
add_filter('core_version_check_locale',		'qtrans_versionLocale');
add_filter('redirect_canonical',			'qtrans_checkCanonical', 10, 2);
