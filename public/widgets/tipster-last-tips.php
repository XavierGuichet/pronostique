<?php

class TipsterLastTips_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'tipster_last_tips', // Base ID
			esc_html__( 'Dernier tips de', 'pronostiques' ), // Name
			array( 'description' => esc_html__( 'Display last tips of a user', '' ), ) // Args
		);

        $this->templater = new TemplateEngine(__DIR__);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
        global $post;
        $user_id = $post->post_author;
        if(!$user_id) {
            return '';
        }
        $user = get_userdata($user_id);
        $widget_title = '';
        $limit = 10;

		if ( ! empty( $instance['title'] ) ) {
			$widget_title = $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) ." ". $user->user_nicename . $args['after_title'];
		}
        if ( ! empty( $instance['limit_count'] ) ) {
			$limit = intval($instance['limit_count']);
		}

        // TODO: This is a duplicate similar to user perf used on user bilan
        $params = array(
            'orderby' => 'date DESC',
            'where' => '1 ',
        );

        if ($user_id === '0') {
            $params['where'] .= ' AND author.id = '.get_current_user_id();
        } elseif (is_numeric($user_id)) {
            $params['where'] .= ' AND author.id = '.$user_id;
        }

        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        $tips = pods('pronostique')->find($params);

        $isUserAdherent = UsersDAO::isUserInGroup(get_current_user_id(), UsersDAO::GROUP_ADHERENTS);

        $tpl_params = array(
                            'before_widget' => $args['before_widget'],
                            'widget_title' => $widget_title,
                            'tips' => $tips,
                            'isUserAdherent' => $isUserAdherent,
                            'after_widget' => $args['after_widget'] );

        echo $this->templater->display('tipster-last-tips', $tpl_params);
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'text_domain' );
        $limit_count = ! empty( $instance['limit_count'] ) ? (int) $instance['limit_count'] : 25;
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'limit_count' ) ); ?>"><?php esc_attr_e( 'Show X lines:', 'text_domain' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit_count' ) ); ?>" type="number" value="<?php echo esc_attr( $limit_count ); ?>">
        </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['limit_count'] = ( ! empty( $new_instance['limit_count'] ) ) ? strip_tags( $new_instance['limit_count'] ) : '';

		return $instance;
	}

} // class Foo_Widget
