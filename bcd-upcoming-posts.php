<?php
/*
    Plugin Name: BCD Upcoming Posts
    Plugin URI: http://www.duhjones.com/downloads/bcd-upcoming-posts/
    Description: Creates a widget that can be used to display upcoming posts.  It can be customized to display a certain number of posts and in random order.  Also, provides a shortcode to display the list.
    Author: Frank Jones
    Version: 1.4.1
    Author URI: http://www.duhjones.com/
*/

// ----------------------------------------------------------------------------------------------------
// Set the plugin's url to a variable

define ( 'BCDUP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );



// ----------------------------------------------------------------------------------------------------
// Add the widget for displaying upcoming posts

add_action( 'widgets_init', create_function('', 'return register_widget("bcd_upcoming_posts");') );
class BCD_Upcoming_Posts extends WP_Widget {
	// Initializes the widget frame
	function BCD_Upcoming_Posts() {
		$widget_ops = array(
			'classname' => 'BCD_Upcoming_Posts',
			'description' => 'Displays upcoming posts'
		);
		
		$this->WP_Widget( 'BCD_Upcoming_Posts', 'Upcoming Posts', $widget_ops );
		
		if ( is_active_widget(false, false, $this->id_base) ) {
			wp_enqueue_script( 'bcdup-script', BCDUP_PLUGIN_URL . 'scripts/bcdup-script.js', false );
			wp_enqueue_style( 'bcdup-css', BCDUP_PLUGIN_URL . 'css/bcdup-css.css', false );
		}
}
	
	// Load the configuration options for the widget
	function form ( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title' => '',
				'posts_per_page' => '',
				'display_as_link' => false,
				'include_drafts' => false,
				'include_only_drafts' => false,
				'sort_order' => 'ASC'
			)
		);
		
		//Display the fields on the widget
		// Title
		$title = $instance['title'];
		$title_field_id = $this->get_field_id('title');
		$title_field_name = $this->get_field_name('title');
		$title_value = attribute_escape($title);
		
		echo '<p>';
		echo $this->build_textbox( $title_field_id, 'Title', 'widefat', $title_field_name, $title_value );
		echo '</p>';
		
		
		// Posts per page
		$posts_per_page = $instance['posts_per_page'];
		$posts_per_page_field_id = $this->get_field_id('posts_per_page');
		$posts_per_page_field_name = $this->get_field_name('posts_per_page');
		$posts_per_page_value = attribute_escape($posts_per_page);
		
		echo '<p>';
		echo $this->build_textbox( $posts_per_page_field_id, 'Number of posts to show', 'widefat', $posts_per_page_field_name, $posts_per_page_value );
		echo '</p>';
		
		// Show in random order
		$sort_order = $instance['sort_order'];
		$sort_order_field_id = $this->get_field_id('sort_order');
		$sort_order_field_name = $this->get_field_name('sort_order');
		$sort_order_value = attribute_escape( $sort_order );
		
		?>
		<p>
			<label>Sort order:</label>
			<select name="<?php echo $sort_order_field_name; ?>" id="<?php echo $sort_order_field_id; ?>">
				<option value="ASC" <?php selected( $sort_order_value, 'ASC' ); ?>>Ascending</option>
				<option value="DESC" <?php selected( $sort_order_value, 'DESC' ); ?>>Descending</option>
				<option value="rand" <?php selected( $sort_order_value, 'rand' ); ?>>Random</option>
			</select>
		</p>
		<?php
		
		// Show as hyperlink
		$display_as_link = $instance['display_as_link'];
		$display_as_link_field_id = $this->get_field_id('display_as_link');
		$display_as_link_field_name = $this->get_field_name('display_as_link');

		echo '<p>';
		echo $this->build_checkbox( $display_as_link, $display_as_link_field_id, $display_as_link_field_name, 'Display as a link?', 'no' );
		echo '</p>';
		
		
		// Show as hyperlink
		$include_drafts = $instance['include_drafts'];
		$include_drafts_field_id = $this->get_field_id('include_drafts');
		$include_drafts_field_name = $this->get_field_name('include_drafts');
		
		echo '<p>';
		echo $this->build_checkbox( $include_drafts, $include_drafts_field_id, $include_drafts_field_name, 'Include drafts?', 'no' );
		echo '</p>';
		
		
		// Show as hyperlink
		$include_only_drafts = $instance['include_only_drafts'];
		$include_only_drafts_field_id = $this->get_field_id('include_only_drafts');
		$include_only_drafts_field_name = $this->get_field_name('include_only_drafts');
		
		if ( 'on' == $include_drafts ) {
			$include_only_drafts_disabled = 'no';
		} else {
			$include_only_drafts_disabled = 'yes';
		}
		
		echo '<p style="margin-left: 2em">';
		echo $this->build_checkbox( $include_only_drafts, $include_only_drafts_field_id, $include_only_drafts_field_name, 'Include only drafts?', $include_only_drafts_disabled );
		echo '</p>';
	}
	
	// Save the values on the widget
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['posts_per_page'] = $new_instance['posts_per_page'];
		$instance['display_as_link'] = $new_instance['display_as_link'];
		$instance['include_drafts'] = $new_instance['include_drafts'];
		$instance['include_only_drafts'] = $new_instance['include_only_drafts'];
		$instance['sort_order'] = $new_instance['sort_order'];

		return $instance;
	}
	
	// Display the widget
	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		
		if (!empty($title)) {
			echo $before_title . $title . $after_title;;
		}
		
		if ( !empty( $instance['include_drafts'] ) && 'on' == $instance['include_drafts'] ) {
			if ( !empty( $instance['include_only_drafts'] ) && 'on' == $instance['include_only_drafts'] ) {
				$qry_args = array(
					'post_status' => array ( 'draft' )
				);
			} else {
				$qry_args = array(
					'post_status' => array ( 'draft', 'future' )
				);
			}
		} else {
			$qry_args = array(
				'post_status' => array ( 'future' )
			);
		}
		
		if ( !empty( $posts_per_page )) {
			$qry_args['posts_per_page'] = $instance['posts_per_page'];
		}
		
		if ( !empty( $instance['sort_order'] ) ) {
			switch ( $instance['sort_order'] ) {
				case 'DESC':
					$qry_args['order'] = 'DESC';
					$qry_args['orderby'] = 'date';
					break;
				case 'rand':
					$qry_args['orderby'] = 'rand';
					break;
				default:
					$qry_args['order'] = 'ASC';
					$qry_args['orderby'] = 'date';
			}
		}
		
		$my_query = new WP_Query( $qry_args );
		while ( $my_query->have_posts() ) {
			$my_query->the_post();
			
			echo '<ul>';
			echo '<li>';
			if ( !empty( $instance['display_as_link'] ) && 'on' == $instance['display_as_link'] ) {
				echo '<a href="' . get_permalink() . '">' . get_the_title();
			}
			else
			{
				echo get_the_title();
			}
			
			echo the_post_thumbnail( array(220,200) );
			
			if ( !empty( $instance['display_as_link'] ) && 'on' == $instance['display_as_link'] ) {
				echo '</a>';
			}
			
			echo '</li>';
			echo '</ul>';
		}
		wp_reset_query();
		
		echo $after_widget;
	}
	
	
	// ----------------------------------------------------------------------------------------------------
	// Helpers to build controls for display in the widget configuration panel
	
	// Builds a checkbox for display
	function build_checkbox ( $field_instance, $field_id, $field_name, $label, $disabled ) {
		$ret_val = '<input class="checkbox" type="checkbox" %checked% id="%id%" name="%name%" %disabled% /> ';
		$ret_val .= '<label id="%label-id%" for="%id%" %disabled-style%>%label%</label>';
		$ret_val .= '';
		
		$ret_val = str_replace( '%checked%', checked( $field_instance, 'on', false ), $ret_val );
		$ret_val = str_replace( '%id%', $field_id, $ret_val );
		$ret_val = str_replace( '%name%', $field_name, $ret_val );
		$ret_val = str_replace( '%label%', $label, $ret_val );
		$ret_val = str_replace( '%label-id%', $field_id . '_label', $ret_val );
		
		if ( 'yes' == $disabled ) {
			$ret_val = str_replace( '%disabled%', 'disabled', $ret_val );
			$ret_val = str_replace( '%disabled-style%', 'class="bcdup-disabled"', $ret_val );
		} else {
			$ret_val = str_replace( '%disabled%', '', $ret_val );
			$ret_val = str_replace( '%disabled-style%', '', $ret_val );
		}
		
		return $ret_val;
	}
	
	// Builds a textbox for display
	function build_textbox( $field_id, $label, $class, $name, $value ) {
		$ret_val = '<label for="%id%">%label%: ';
		$ret_val .= '<input id="%id%" type="text" class="%class%" name="%name%" value="%value%" />';
		$ret_val .= '</label>';
		
		$ret_val = str_replace( '%id%', $field_id, $ret_val );
		$ret_val = str_replace( '%label%', $label, $ret_val );
		$ret_val = str_replace( '%class%', $class, $ret_val );
		$ret_val = str_replace( '%name%', $name, $ret_val );
		$ret_val = str_replace( '%value%', $value, $ret_val );
		
		return $ret_val;
	}
}



// ----------------------------------------------------------------------------------------------------
// Add the shortcode [bcdupcoming]

add_shortcode( 'bcdupcoming', 'bcdup_sc_upcoming_posts' );
function bcdup_sc_upcoming_posts( $atts ) {
	extract( shortcode_atts( array(
		'numposts' => '',
		'sortorder' => 'asc',
		'showmore' => 'yes',
		'showlink' => 'no',
		'includedrafts' => 'omitted',
		'draftsonly' => 'no'
	), $atts ) );
	
	$output = '';
	
	global $post;
	$tmp_post = $post;
	
	if ( 'yes' == $includedrafts ) {
		if ( 'yes' == $draftsonly ) {
			$qry_args = array(
				'post_status' => array ( 'draft' )
			);
		} else {
			$qry_args = array(
				'post_status' => array ( 'draft', 'future' )
			);
		}
	} else {
		if ( 'yes' == $draftsonly && 'omitted' == $includedrafts ) {
			$qry_args = array(
				'post_status' => array ( 'draft' )
			);
		} else {
			$qry_args = array(
				'post_status' => array ( 'future' )
			);
		}
	}
	
	if ( !empty( $numposts )) {
		$qry_args['posts_per_page'] = $numposts;
	}
	
	if ( !empty( $sortorder ) ) {
		switch ( strtolower( $sortorder ) ) {
			case 'desc':
				$qry_args['order'] = 'DESC';
				$qry_args['orderby'] = 'date';
				break;
			case 'rand':
				$qry_args['orderby'] = 'rand';
				break;
			default:
				$qry_args['order'] = 'ASC';
				$qry_args['orderby'] = 'date';
		}
	}
	
	$my_query = new WP_Query( $qry_args );
	while ( $my_query->have_posts() ) {
		$my_query->the_post();
		
		$output .= '<div>';
		$output .= '<strong>';
		
		if ( !empty( $showlink ) && 'yes' == $showlink ) {
			$output .= '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
		}
		else
		{
			$output .= get_the_title();
		}
		
		$output .= '</strong>';
		
		if ( !empty( $showmore ) && 'yes' == $showmore ) {
			$output .= '<p>' . get_the_excerpt(__('(more…)')) . '</p>';
		}
		$output .= '</div>';
	}
	wp_reset_query();
	
	$output .= '';
	
	return $output;
}

?>
