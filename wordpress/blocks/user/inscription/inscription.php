<?php

use Controllers\StudentController;

/**
 * Function of the block
 *
 * @return string
 */
function inscr_student_render_callback()
{
    if(is_page()) {
	    $student = new StudentController();
        return $student->inscriptionStudent();
    }
}

/**
 * Build a block
 */
function block_inscr_student()
{
    wp_register_script(
        'inscr_student-script',
        plugins_url( 'block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-data' )
    );

    register_block_type('tvconnecteeamu/inscr-student', array(
        'editor_script' => 'inscr_student-script',
        'render_callback' => 'inscr_student_render_callback'
    ));
}
add_action( 'init', 'block_inscr_student' );