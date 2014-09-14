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

class mqTranslateWidget extends QtranslateSlugWidget {
	function mqTranslateWidget() {
		$widget_ops = array('classname' => 'widget_mqtranslate', 'description' => __('Allows your visitors to choose a Language.','mqtranslate') );
		$this->WP_Widget('mqtranslate', __('mqTranslate Language Chooser','mqtranslate'), $widget_ops);
	}
	
	function widget($args, $instance) {
		$instance['short_text'] = false;
		parent::widget($args, $instance);
	}
	
	function update($new_instance, $old_instance) {
		$old_instance['short_text'] = false;
		return parent::update($new_instance, $old_instance);
	}
	
	function form($instance) {
		$instance = (array) $instance;
		$instance['short_text'] = false;
		parent::form($instance);
	}
}

