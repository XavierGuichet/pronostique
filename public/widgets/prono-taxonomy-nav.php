<?php

class PronoTaxonomyNav_Widget extends WP_Widget {
    private $templater;

    /**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'prono-taxonomy-nav', // Base ID
			esc_html__( 'Navigation Liste Pronos', 'pronostiques' ), // Name
			array( 'description' => esc_html__( 'Crée un menu listant les taxonomies propres aux pronos', '' ), ) // Args
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
        $taxonomies_by_sport = PronoLib::getInstance()->getTaxonomiesFilterData();

        $tpl_params = array(
                            'before_widget' => $args['before_widget'],
                            'widget_title' => '',
                            'taxonomies_by_sport' => $taxonomies_by_sport,
                            'after_widget' => $args['after_widget'] );

        echo $this->templater->display('prono-taxonomy-nav', $tpl_params);
    }

    /**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

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
    }

    private function get_Taxonomies() {
        $competition_pod = pods( 'competition' )->find();


    }
}
