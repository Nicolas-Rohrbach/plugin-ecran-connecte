<?php

function information_render_callback() {
    $information = new Information();
    $view = new ViewInformation();
    if(is_page()){
        return $information->insertInformation();
    }
}

function block_information() {
    wp_register_script(
        'information-script',
        plugins_url( 'block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-data' )
    );

    register_block_type('tvconnecteeamu/add-information', array(
        'editor_script' => 'information-script',
        'render_callback' => 'information_render_callback'
    ));
}
add_action( 'init', 'block_information' );