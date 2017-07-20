<?php

class SGI_DPW_Widget extends WP_Widget
{

	protected static $scripts_loaded = false;
	private $defaults;

	public function __construct()
	{
		$widget_opts = array('id_base' => 'sgi_dpw_widget', 'description' => __( 'Display your posts with this widget', 'sgidpw' ) );
		$this->defaults = array(
			'title' 	  => __( 'Daily Posts', 'sgidpw' ),
			'numposts' 	  => 5,
			'category'	  => array(),
			'tag'		  => array(),
			'title_limit' => 53,
			'tab_pos'	  => 'right',
			'day_limit'	  => 5,
		);

		parent::__construct('sgi_dpw_widget',__('Daily Posts','sgidpw'),$widget_opts);

		add_action('wp_enqueue_scripts',array(&$this, 'load_scripts'), 50);
	}

	public function form ($instance)
	{
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'sgidpw' ); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<h4>Post and tab options</h4>
		<p>
	   	 	<label for="<?php echo $this->get_field_id( 'numposts' ); ?>"><?php _e( 'Number of posts to show per day (tab)', 'sgidpw' ); ?>:</label>
		 	<input id="<?php echo $this->get_field_id( 'numposts' ); ?>" type="text" name="<?php echo $this->get_field_name( 'numposts' ); ?>" value="<?php echo absint( $instance['numposts'] ); ?>" class="small-text" />
	  	</p>
	  	<p>
	   	 	<label for="<?php echo $this->get_field_id( 'day_limit' ); ?>"><?php _e( 'Number of days (tabs) to show', 'sgidpw' ); ?>:</label>
		 	<input id="<?php echo $this->get_field_id( 'day_limit' ); ?>" type="text" name="<?php echo $this->get_field_name( 'day_limit' ); ?>" value="<?php echo absint( $instance['day_limit'] ); ?>" class="small-text" />
	  	</p>

	  	<h4>Taxonomy options</h4>
		<p>
			<?php $this->widget_tax( $this, 'category', $instance['category'] ); ?>
		</p>

		<?php $tags = ( is_array( $instance['tag'] ) ) ? implode( ",", $instance['tag'] ) : $instance['tag'];?>
		<p>
			<label for="<?php echo $this->get_field_id( 'tag' ); ?>"><?php _e( 'Select tags', 'sgidpw' ); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'tag' ); ?>" type="text" name="<?php echo $this->get_field_name( 'tag' ); ?>" value="<?php echo $tags ?>" class="widefat" />
			<small class="howto"><?php _e( 'Specify tag slugs separated by comma if you want to select only those tags. i.e. sugar, spice, everything-nice.', 'sgidpw' ); ?></small>
		</p>

		<h4>Display options</h4>
		<p>
			<label for="<?php echo $this->get_field_id( 'title_limit' ); ?>"><?php _e( 'Post titles characters limit', 'sgidpw' ); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title_limit' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title_limit' ); ?>" value="<?php echo absint( $instance['title_limit'] ); ?>" class="small-text" />
			<small class="howto"><?php _e( 'Note: Leave empty if you want to show entire post titles', 'sgidpw' ); ?></small>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'tab_pos' ); ?>"><?php _e( 'Tab Position', 'sgidpw' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'tab_pos' ); ?>" name="<?php echo $this->get_field_name( 'tab_pos' ); ?>">
				<option value="right" <?php selected($instance['tab_pos'],'right');?>>Right</option>
				<option value="left" <?php selected($instance['tab_pos'],'left');?>>Left</option>
			</select>
			<small class="howto"><?php _e( 'Note: Select position for the tabs', 'sgidpw' ); ?></small>
		</p>


		<?php
	}

	public function update ($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['numposts'] = absint( $new_instance['numposts'] );
		$instance['day_limit'] = $new_instance['day_limit'];
		$instance['category'] = $new_instance['category'];
		$instance['tag'] = $new_instance['tag'];
		$instance['title_limit'] = absint( $new_instance['title_limit'] );
		$instance['tab_pos'] = $new_instance['tab_pos'];

		return $instance;
	}

	public function widget ($args, $instance)
	{
		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $before_widget;

		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( !empty( $title ) ) :
			echo $before_title . $title . $after_title;
		endif;

		$date_limit = strtotime("-{$instance['day_limit']} days");

		$q_args = array(
			'post_type'		 => apply_filters('sgi_dpw_post_types','post'),
			'date_query'	 => array (
				array(
					'after' => array(
						'year'  => date('Y',$date_limit),
						'month' => date('m',$date_limit),
						'day'	=> date('d',$date_limit)
					)
				)
			)
		);

		if (!empty($instance['category'])) :
			$q_args['category__in'] = ( is_array( $instance['category'] ) ) ? $instance['category'] : array( $instance['category'] );
		endif;

		if ( !empty( $instance['tag'] ) ) :
			$q_args['tag_slug__in'] = explode( ',', $instance['tag'] );
		endif;

		$dpw_posts = new WP_Query($q_args);

		/**
		 * @since 1.0
		 * @param string - Name of the template file to require
		 */
		$dpw_template_file = apply_filters('sgi_dpw_template_file','loop-dpw.php');

		$suffix = uniqid();
		$position = $instance['tab_pos'];

		$html = '<div class="dpw-tabs dpw-'.$position.'"><ul class="">';
		$tab_content = ''; $counter = 0;
		$cur_date = $last_date = '';


		
		while ($dpw_posts->have_posts()) : $dpw_posts->the_post();

			$cur_date = $this->get_post_date(get_the_ID());
			$class = ($counter == 0) ? 'current' : '';

			if ($cur_date != $last_date) :
				$last_date = $cur_date;
				$html .= "<li class=\"dpw-tab-link ${class}\" data-tab=\"dpw-tab-${suffix}-${counter}\">${cur_date}</li>";
				if ($counter != 0) :
					$tab_content .= ob_get_clean();
					$tab_content .= '</div>';
				endif;
				$tab_content .= "<div id=\"dpw-tab-${suffix}-${counter}\" class=\"tab-content ${class}\">";
			else :
				$tab_content .= ob_get_clean();
			endif;

			ob_start();
			require $this->sgi_get_template($dpw_template_file);
			$counter ++;
		endwhile;

		$tab_content .= ob_get_clean();

		$html .= '</ul><div class="dpw-content-wrapper">'.$tab_content.'</div></div></div>';

		echo $html;

		echo $after_widget;
	}

	/**
	 * Function that hooks in script enqueue system.
	 * 
	 * We're first checking if we should load scripts in the first place (via $load_scripts filter). After that, we're checking for bootstrap compatibility.
	 * If we need to load scripts, we're loading them only once by using the $scripts_loaded static var.
	 * After script load we set the $scripts_loaded flag to true.
	 * 
	 * @return void
	 * @since 1.0
	 * @author Sibin Grasic
	 */
	public function load_scripts()
	{
		/**
		 * @since 1.0
		 * @param boolean - flag which indicates if we want to load plugin styles and scripts or not
		 */
		$load_scripts = apply_filters('sgi_dpw_load_scripts',true);

		//If the theme is loading bootstrap framework, we don't need the css and JS, it should be handled natively
		//if (current_theme_supports( 'bootstrap' ))
		//	return;

		wp_register_style( 'sgi-daily-posts', plugins_url('assets/css/dpw.css',SGI_DPW_BASENAME), null, SGI_DPW_VERSION );
		wp_register_script( 'sgi-daily-posts-js', plugins_url( "assets/js/dpw.js", SGI_DPW_BASENAME ), false, SGI_DPW_VERSION, true);


		if (!self::$scripts_loaded && $load_scripts && is_active_widget(false, false, $this->id_base, true )) : 
			
			wp_enqueue_style( 'sgi-daily-posts' );
			wp_enqueue_script('sgi-daily-posts-js');

			self::$scripts_loaded = true;
		endif;
	}

	/**
	 * Function which is being used to display the taxonomy selection checkboxes
	 * @param array $widget_instance - an array of widget options
	 * @param string $taxonomy - taxonomy for which to retrieve the terms
	 * @param array $selected_taxonomy - currently selected taxonomies
	 * @return void
	 * @since 1.0
	 * @author Sibin Grasic
	 * @todo Update the checkbox list to use chosen.js
	 */
	private function widget_tax( $widget_instance, $taxonomy, $selected_taxonomy = false )
	{
		if ( !empty( $widget_instance ) && !empty( $taxonomy ) ) :
			$categories = get_terms( $taxonomy, 'orderby=name&hide_empty=0' );
			?>
			<label for="<?php echo $widget_instance->get_field_id( 'category' ); ?>"><?php _e( 'Choose from:', 'sgidpw' ); ?></label><br/>
			<?php foreach ( $categories as $category ) : ?>
						<input type="checkbox" name="<?php echo $widget_instance->get_field_name( 'category' ); ?>[]" value="<?php echo $category->term_id; ?>" <?php echo in_array( $category->term_id, (array)$selected_taxonomy ) ? 'checked': ''?> /> <?php echo $category->name; ?><br/>
			<?php endforeach;?>
			<?php
		endif;
	}

	/**
	 * Function which is being used instead of get_template_part, so that theme authors can easily override the template file
	 * @param string $template - Name of the template file we're requiring
	 * @return string - Full path for the template file we're requiring
	 * @since 1.0
	 * @author Sibin Grasic
	 */
	private function sgi_get_template($template)
	{

		/**
		 * @since 1.0
		 * @param string - Path in which to search for the file
		 */
		$file_path = apply_filters( 'sgi_dpw_template_path',trailingslashit('templates') );

		$template_slug = rtrim($template, '.php');
		$template = $template_slug . '.php';

		if ($theme_file = locate_template('/'.$file_path.$template) ) :
			$file = $theme_file;
		else :
			$file = SGI_DPW_PATH.$file_path.$template;
		endif;

		return $file;
	}

	/**
	 * Function that retrieves post date
	 * Since plugins / themes can hook into get_the_time filter (e.g. Meks Time Ago plugin), we need to get the absolute time for the post, directly from post object
	 * @param int $post_id - ID of the post we're retrieving publish date for
	 * @return string - Formated string for the date.
	 */
	private function get_post_date($post_id)
	{
		$dpw_post = get_post($post_id);

		$date = $dpw_post->post_date;
		unset($post);

		return date('d. M',strtotime($date));
	}
}