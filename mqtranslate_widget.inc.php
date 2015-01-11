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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

define('QT_WIDGET_CSS',
'.qtrans_widget ul { margin: 0; }
.qtrans_widget ul li
{
display: inline; /* horizontal list, use "list-item" or other appropriate value for vertical list */
list-style-type: none; /* use "initial" or other to enable bullets */
margin: 0 5px 0 0; /* adjust spacing between items */
opacity: 0.5;
-o-transition: 1s ease opacity;
-moz-transition: 1s ease opacity;
-webkit-transition: 1s ease opacity;
transition: 1s ease opacity;
}
//.qtrans_widget ul li span { margin: 0 5px 0 0; } /* other way to control spacing */
.qtrans_widget ul li.active { opacity: 0.8; }
.qtrans_widget ul li:hover { opacity: 1; }
.qtrans_widget img { box-shadow: none; vertical-align: middle; }
.qtrans_flag { height:12px; width:18px; display:block; }
.qtrans_flag_and_text { padding-left:20px; }
.qtrans_flag span { display:none; }
');


/* mqTranslate Widget */

class mqTranslateWidget extends WP_Widget {
	function mqTranslateWidget() {
		$widget_ops = array('classname' => 'widget_mqtranslate', 'description' => __('Allows your visitors to choose a Language.','mqtranslate') );
		$this->WP_Widget('mqtranslate', __('mqTranslate Language Chooser','mqtranslate'), $widget_ops);
	}
	
	function widget($args, $instance) {
		extract($args);
		
		echo '<style type="text/css">'.PHP_EOL;
		echo empty($instance['widget-css']) ? QT_WIDGET_CSS : $instance['widget-css'];
		echo '</style>'.PHP_EOL;
		
		echo $before_widget;
		if (empty($instance['hide-title'])) {
			$title = $instance['title'];
			if (empty($title))
				$title=__('Language', 'mqtranslate');
			$title = apply_filters('qtrans_widget_title',$title.':');
			echo $before_title . $title . $after_title;
		}
		
		$type = $instance['type'];
		if($type!='text'&&$type!='image'&&$type!='both'&&$type!='dropdown') $type='text';

		qtrans_generateLanguageSelectCode($type, $this->id);
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		if(isset($new_instance['hide-title'])) $instance['hide-title'] = $new_instance['hide-title'];
		$instance['type'] = $new_instance['type'];
		$instance['widget-css'] = $new_instance['widget-css'];
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'hide-title' => false, 'type' => 'text', 'widget-css' => QT_WIDGET_CSS ) );
		$title = $instance['title'];
		$hide_title = $instance['hide-title'];
		$type = $instance['type'];
		$widget_css = $instance['widget-css'];
		if (empty($widget_css))
			$widget_css = QT_WIDGET_CSS;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'mqtranslate'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('hide-title'); ?>"><?php _e('Hide Title:', 'mqtranslate'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('hide-title'); ?>" name="<?php echo $this->get_field_name('hide-title'); ?>" <?php echo ($hide_title=='on')?'checked="checked"':''; ?>/></label></p>
		<p><?php _e('Display:', 'mqtranslate'); ?></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>1"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>1" value="text"<?php echo ($type=='text')?' checked="checked"':'' ?>/> <?php _e('Text only', 'mqtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>2"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>2" value="image"<?php echo ($type=='image')?' checked="checked"':'' ?>/> <?php _e('Image only', 'mqtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>3"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>3" value="both"<?php echo ($type=='both')?' checked="checked"':'' ?>/> <?php _e('Text and Image', 'mqtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>4"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>4" value="dropdown"<?php echo ($type=='dropdown')?' checked="checked"':'' ?>/> <?php _e('Dropdown Box', 'mqtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('widget-css'); ?>"><?php echo __('Widget CSS:', 'mqtranslate'); ?></label><br><textarea class="widefat" rows="6" name="<?php echo $this->get_field_name('widget-css'); ?>" id="<?php echo $this->get_field_id('widget-css'); ?>" /><?php echo esc_attr($widget_css); ?></textarea><br><small><?php _e('To reset to default, clear the text.','mqtranslate'); ?></small></p>
<?php
	}
}
?>
