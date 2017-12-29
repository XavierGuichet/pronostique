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
        $sports = pods( 'sport' )->find(array('where' => 'count != 0', 'orderby' => 'tt.count DESC, name ASC'));
        $taxonomies_by_sport = array();

        while($sports->fetch() ) {
            $sport = $sports->display('name');
            if(!isset($taxonomies_by_sport[$sport]) && !$sports->field('hide')) {
                $sport_term = get_term($sports->display('term_id'));
                $taxonomies_by_sport[$sport] = array(
                    'sport' => array(
                        'name' => $sport_term->name,
                        'permalink' => get_term_link((int) $sport_term->term_id)
                    ),
                    'competitions' => array()
                );
            }
        }

        $competitions = pods( 'competition' )->find(array('where' => '(count != 0 OR description != "")', 'orderby' => 'tt.count DESC, name ASC'));

        while($competitions->fetch() ) {
            $sport = $competitions->display('sport.name');
            if(!isset($taxonomies_by_sport[$sport])) {
                continue;
            }
            if(!$competitions->field('hide')) {
                $taxonomy = array(
                    'name' => $competitions->display('name'),
                    'permalink' => get_term_link((int) $competitions->display('id'))
                );
                $taxonomies_by_sport[$sport]['competitions'][] = $taxonomy;
            }
        }

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
