<?php

class TipsterStats_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'tipster_stats', // Base ID
			esc_html__( 'Tipster Stats', 'pronostiques' ), // Name
			array( 'description' => esc_html__( 'Display stats of a tipster choosen by post author', '' ), ) // Args
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

        $widget_title = '';

		if ( ! empty( $instance['title'] ) ) {
			$widget_title = $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

        // TODO: This is a duplicate similar to user perf used on user bilan
        $gain_sql = ' ROUND(SUM( IF(tips_result = 1, (cote-1)*mise, IF(tips_result = 2, - mise, IF(tips_result = 3, 0, 0))) ), 2) as gain';
        $mises_sql = ' ROUND(SUM( IF(tips_result IN (1,2,3), mise, 0) ), 2) as mises';
        $VPNA_sql = ' SUM(IF(tips_result = 1,1,0)) AS V, SUM(IF(tips_result = 3,1,0)) AS N, SUM(IF(tips_result = 2,1,0)) AS P, SUM(IF(tips_result = 0,1,0)) AS A';

        $where = '1';
        if ($user_id !== null and is_numeric($user_id)) {
            $where .= ' AND author.id = '.intval($user_id);
        }

        $stats = pods('pronostique')->find(array(
                            'select' => "count(*) as 'nb_total_tips',".$gain_sql.','.$mises_sql.','.$VPNA_sql,
                            'where' => $where,
        ));

        $user_month_profit = pods('pronostique')->find(array(
                            'select' => $gain_sql,
                            'where' => $where.' AND MONTH(date) like MONTH(NOW()) AND YEAR(date) like YEAR(NOW())',
        ));

        if ($user_month_profit->total() == 0 || $user_month_profit->field('gain') === null) {
            $month_profit = 0;
        } else {
            $month_profit = $user_month_profit->field('gain');
        }

        $yield = Formatter::prefixSign(Calculator::Yield($stats->field('mises'), $stats->field('gain')));

        $tpl_params = array(
            'before_widget' => $args['before_widget'],
            'widget_title' => $widget_title,
            'stats' => $stats,
                                'month_profit' => $month_profit,
                                'yield' => $yield,
        'after_widget' => $args['after_widget'] );

        echo $this->templater->display('tipster-stats', $tpl_params);
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
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
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

		return $instance;
	}

} // class Foo_Widget
