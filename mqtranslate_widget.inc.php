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

class mqTranslateWidget extends WP_Widget {
	function mqTranslateWidget() {
		$widget_ops = array('classname' => 'widget_mqtranslate', 'description' => __('Allows your visitors to choose a Language.','mqtranslate') );
		$this->WP_Widget('mqtranslate', __('mqTranslate Language Chooser','mqtranslate'), $widget_ops);
	}
	
	function widget($args, $instance) {
		extract($args);
		
		echo $before_widget;
		$title = empty($instance['title']) ? __('Language', 'mqtranslate') : apply_filters('widget_title', $instance['title']);
		$hide_title = empty($instance['hide-title']) ? false : 'on';
		$type = $instance['type'];
		if($type!='text'&&$type!='image'&&$type!='both'&&$type!='dropdown'&&$type!='lang-code') $type='text';

		if($hide_title!='on') { echo $before_title . $title . $after_title; };
		qtrans_generateLanguageSelectCode($type, $this->id);
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		if(isset($new_instance['hide-title'])) $instance['hide-title'] = $new_instance['hide-title'];
		$instance['type'] = $new_instance['type'];

		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'hide-title' => false, 'type' => 'text' ) );
		$title = $instance['title'];
		$hide_title = $instance['hide-title'];
		$type = $instance['type'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'mqtranslate'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('hide-title'); ?>"><?php _e('Hide Title:', 'mqtranslate'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('hide-title'); ?>" name="<?php echo $this->get_field_name('hide-title'); ?>" <?php echo ($hide_title=='on')?'checked="checked"':''; ?>/></label></p>
		<p><?php _e('Display:', 'mqtranslate'); ?></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>1"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>1" value="text"<?php echo ($type=='text')?' checked="checked"':'' ?>/> <?php _e('Text only', 'mqtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>2"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>2" value="image"<?php echo ($type=='image')?' checked="checked"':'' ?>/> <?php _e('Image only', 'mqtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>3"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>3" value="both"<?php echo ($type=='both')?' checked="checked"':'' ?>/> <?php _e('Text and Image', 'mqtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>4"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>4" value="dropdown"<?php echo ($type=='dropdown')?' checked="checked"':'' ?>/> <?php _e('Dropdown Box', 'mqtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>5"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>5" value="lang-code"<?php echo ($type=='lang-code')?' checked="checked"':'' ?>/> <?php _e('Language code', 'mqtranslate'); ?></label></p>
<?php
	}
}
?>
