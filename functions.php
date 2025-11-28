<?php
/**
 * Force le chargement du style.css du thème enfant.
 */
add_action( 'wp_enqueue_scripts', 'mon_theme_enfant_styles' );

function mon_theme_enfant_styles() {
    // Charge le style principal du thème enfant
    wp_enqueue_style( 
        'mon-theme-enfant-style', 
        get_stylesheet_uri(),
        array(), 
        '1.0.0'
    );
}