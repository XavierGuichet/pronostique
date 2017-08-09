<?php

class TopVip_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'top_vip', // Base ID
			esc_html__( 'Top VIP', 'text_domain' ), // Name
			array( 'description' => esc_html__( 'Display a ranking of best VIP by Yield', '' ), ) // Args
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
        $widget_title = '';
        $table_title = '';
        $of_the_month = true;
        $max = 10;

		if ( ! empty( $instance['title'] ) ) {
			$widget_title = $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		if ( ! empty( $instance['table_title'] ) ) {
			$table_title = $instance['table_title'];
		}

		if ( ! empty( $instance['limit_count'] ) ) {
			$max = intval($instance['limit_count']);
		}
        // TODO : change to MONTH(NOW) and YEAR(NOW)

        $user_results = pods('pronostique')->find(
            array(
                'select' => 'ROUND(SUM( IF(tips_result = 1, (cote-1)*mise, IF(tips_result = 2, - mise, IF(tips_result = 3, 0, 0))) ), 2) AS Gain, SUM(mise) as Mise_total, t.*',
                'where' => 'tips_result > 0 AND is_expert = 1',
                'orderby' => 'Gain Desc',
                'groupby' => 'author.id',
            )
        );

        $top_vips = array();

        while($user_results->fetch() ) {
            $top_vips[] = array(
                'user_id' => $user_results->display('author.ID'),
                'name' => $user_results->display('author.user_nicename'),
                'gain' => $user_results->display('Gain'),
                'yield' => Calculator::Yield($user_results->field('Mise_total'),$user_results->field('Gain'))
            );
        }

        uasort($top_vips, 'topvip_yieldsort');



        $entetes = '<tr><th>&nbsp;</th> <th>Pseudo</th> <th>Yield</th><th>Profit</th></tr>';


        $tpl_params = array(
                    'before_widget' => $args['before_widget'],
                    'widget_title' => $widget_title,
                    'titre' => $table_title,
                    'entetes' => $entetes,
                    'row' => $top_vips,
                    'after_widget' => $args['after_widget']
                    );

        echo $this->templater->display('classements-vip', $tpl_params);
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
		$table_title = ! empty( $instance['table_title'] ) ? $instance['table_title'] : esc_html__( 'New table title', 'text_domain' );
		$limit_count = ! empty( $instance['limit_count'] ) ? (int) $instance['limit_count'] : 25;
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'table_title' ) ); ?>"><?php esc_attr_e( 'Table Title:', 'text_domain' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'table_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'table_title' ) ); ?>" type="text" value="<?php echo esc_attr( $table_title ); ?>">
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
		$instance['table_title'] = ( ! empty( $new_instance['table_title'] ) ) ? strip_tags( $new_instance['table_title'] ) : '';
		$instance['limit_count'] = ( ! empty( $new_instance['limit_count'] ) ) ? strip_tags( $new_instance['limit_count'] ) : '';

		return $instance;
	}

} // class Foo_Widget
function topvip_yieldsort($a,$b) {
    return ($a['yield'] < $b['yield']);
}
