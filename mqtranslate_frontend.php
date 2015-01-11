<?php
if ( !defined( 'ABSPATH' ) ) exit;

function qtrans_add_lang_icons_css ()
{
	global $q_config;
	echo '<style type="text/css">'.PHP_EOL;
	foreach($q_config['enabled_languages'] as $lang) 
		echo '.qtrans_flag_'.$lang.' {background-image: url('.trailingslashit(WP_CONTENT_URL).$q_config['flag_location'].$q_config['flag'][$lang].'); background-repeat: no-repeat;}'.PHP_EOL;
	do_action('qtrans_head_add_css');
	echo '</style>'.PHP_EOL;
}

function qtrans_head(){
	global $q_config;
	echo "\n<meta http-equiv=\"Content-Language\" content=\"".str_replace('_','-',$q_config['locale'][$q_config['language']])."\" />\n";
	qtrans_add_lang_icons_css();

	// set links to translations of current page
	foreach ($q_config['enabled_languages'] as $language) {
		if($language != qtrans_getLanguage())
			echo '<link hreflang="'.$language.'" href="'.qtrans_convertURL('',$language).'" rel="alternate" />'."\n";
	}
}
add_action('wp_head', 'qtrans_head');

function qtrans_get_nav_menu_items( $items, $menu, $args )
{
	global $q_config;
	$language=$q_config['language'];
	$flag_location=trailingslashit($q_config['WP_CONTENT_URL']).$q_config['flag_location'];
	$itemid=0;
	$menu_order=0;
  	$qtransmenu=null;
	foreach($items as $item)
	{
	  if($itemid<$item->ID) $itemid=$item->ID;
	  if($menu_order<$item->menu_order) $menu_order=$item->menu_order;
		if( !isset( $item->url ) || strstr( $item->url, '#qtransLangSw' ) === FALSE ) continue;
		$item->title=__('Language','qtranslate').':'.'&nbsp;<img src="'.$flag_location.$q_config['flag'][$language].'">';
		$item->url=null;
		$item->classes[] = 'qtrans-lang-menu';
		$qtransmenu = $item;
	}
	if(!$qtransmenu) return $items;
	$url = '';
	foreach($q_config['enabled_languages'] as $lang)
	{
		$item=new WP_Post((object)array('ID' => ++$itemid));
		//$item->db_id=$item->ID;
		$item->menu_item_parent=$qtransmenu->ID;
		$item->menu_order=++$menu_order;
		$item->post_type='nav_menu_item';
		$item->object='custom';
		//$item->object_id=0;
		$item->type='custom';
		$item->type_label='Custom';
		$item->title='<img src="'.$flag_location.$q_config['flag'][$lang].'">&nbsp;'.$q_config['language_name'][$lang];
		$item->post_title = $item->title;
		$item->post_name='language-menuitem-'.$lang;
		if($lang!=$language)
			$item->url=qtrans_convertURL($url, $lang, false, true);
		$item->classes=array();
		$item->classes[] = 'qtrans-lang-menu-item';
		$items[]=$item;
		++$menu->count;
	}
	return $items;
}
add_filter( 'wp_get_nav_menu_items',  'qtrans_get_nav_menu_items', 0, 3 );

function qtrans_add_lang_icons ()
{
	global $q_config;
	echo "<style>\n";
	foreach($q_config['enabled_languages'] as $lang) 
	{
		echo '.qtrans-flag-'.$lang.' {background-image: url('.trailingslashit(WP_CONTENT_URL).$q_config['flag_location'].$q_config['flag'][$lang]."); background-repeat: no-repeat;}\n";
	}
	echo "</style>\n";
}
add_filter('wp_head', 'qtrans_add_lang_icons');

function qtrans_get_attachment_image_attributes($attr, $attachment, $size)
{
	foreach( $attr as $name => $value ){
		if($name!=='alt') continue;
		$attr[$name]=qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($value);
	}
	return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'qtrans_get_attachment_image_attributes',0,3);

function qtrans_excludeUntranslatedPosts($where) {
	global $q_config, $wpdb;
	if($q_config['hide_untranslated'] && !is_singular()) {
		$where .= " AND $wpdb->posts.post_content LIKE '%<!--:".qtrans_getLanguage()."-->%'";
	}
	return $where;
}
// don't filter untranslated posts in admin
add_filter('posts_where_request', 'qtrans_excludeUntranslatedPosts');

function qtrans_home_url($url, $path, $orig_scheme, $blog_id)
{
	global $q_config;
	return qtrans_convertURL($url, '', false, !$q_config['hide_default_language']);
}
add_filter('home_url', 'qtrans_home_url', 0, 4);

function qtrans_esc_html($text) {
	return qtrans_useDefaultLanguage($text);
}
// filter options
add_filter('esc_html', 'qtrans_esc_html', 0);

// Compability with Default Widgets
qtrans_optionFilter();
add_filter('widget_title', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('widget_text', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);

add_filter('wp_head', 'qtrans_add_css');
add_filter('wp_setup_nav_menu_item', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');

add_filter('get_comment_author', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('the_author', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
add_filter('tml_title', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);

// translate terms
add_filter('cat_row', 'qtrans_useTermLib',0);
add_filter('cat_rows', 'qtrans_useTermLib',0);
add_filter('wp_get_object_terms', 'qtrans_useTermLib',0);
add_filter('single_tag_title', 'qtrans_useTermLib',0);
add_filter('single_cat_title', 'qtrans_useTermLib',0);
add_filter('the_category', 'qtrans_useTermLib',0);
add_filter('get_term', 'qtrans_useTermLib',0);
add_filter('get_terms', 'qtrans_useTermLib',0);
add_filter('get_category', 'qtrans_useTermLib',0);
