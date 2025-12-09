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

/**
 * 🚀 CUSTOM POST TYPE : ESPACE KIDS
 * Crée un univers séparé pour les contenus enfants.
 */
function abd_register_kids_universe() {
    
    // 1. Le CPT (Contenu)
    $args = array(
        'labels' => array(
            'name'          => 'Espace Kids',
            'singular_name' => 'Article Kids',
            'add_new_item'  => 'Ajouter un article Kids',
            'edit_item'     => 'Éditer l\'article Kids',
        ),
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true, // Obligatoire pour Gutenberg
        'menu_icon'    => 'dashicons-smiley',
        'supports'     => array('title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions'),
        // C'est ici qu'on définit l'URL magique : [site.com/kids/mon-article](https://site.com/kids/mon-article)
        'rewrite'      => array('slug' => 'kids', 'with_front' => false), 
        'template'     => array(
            array('core/pattern', array('slug' => 'mon-theme-enfant/article-kids-layout'))
        )
    );
    register_post_type('kids', $args);

    // 2. La Taxonomie (Catégories propres aux enfants : Sciences, Nature, etc.)
    // On ne veut pas utiliser les catégories "Adultes" ici.
    register_taxonomy('sujet_kids', 'kids', array(
        'label'        => 'Sujets Kids',
        'rewrite'      => array('slug' => 'kids-sujet'),
        'hierarchical' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'abd_register_kids_universe');



/**
 * Génère la navigation des catégories pour le header.
 * À utiliser dans parts/header.php (ex-header.html).
 */
/**
 * Génère le menu navigation catégorie pour le header avec bouton KIDS, barre, "Tout voir", puis catégories dynamiques.
 */
function abd_header_categories_nav() {
    // 1. Bouton KIDS (toujours en premier)
    $kids_cat = get_category_by_slug('kids');
    if ($kids_cat) {
        $kids_url = get_category_link($kids_cat->term_id);
        echo '<li>
            <a href="' . esc_url($kids_url) . '" class="abd-nav-link kids-btn">
                <span>👶</span> KIDS
            </a>
        </li>';
    }

    // 2. Barre séparatrice
    echo '<li style="width:1px; height:24px; background:#e2e8f0; margin:0 4px;"></li>';

    // 3. Bouton "Tout voir"
    $is_home = is_front_page() || is_home();
    echo '<li>
        <a href="' . esc_url(home_url('/')) . '" class="abd-nav-link' . ($is_home ? ' active' : '') . '">
            Tout voir
        </a>
    </li>';

    // 4. Autres catégories dynamiques (hors "kids")
    $categories = [
        'economie' => 'Économie',
        'tech' => 'Tech',
        'sport' => 'Sport',
        'ecologie' => 'Écologie',
        'societe' => 'Société',
        'shorts' => 'Shorts',
    ];
    foreach ($categories as $slug => $label) {
        $cat = get_category_by_slug($slug);
        if ($cat) {
            $url = get_category_link($cat->term_id);
            $active = (is_category($slug)) ? ' active' : '';
            echo '<li><a href="' . esc_url($url) . '" class="abd-nav-link' . $active . '">' . esc_html($label) . '</a></li>';
        }
    }
}




// SHORTCODE CORRIGÉ pour fonctionner dans la VERSION 2
function themechild_featured_or_default($atts = array(), $content = null) {
    global $post;
    
    // DEBUG : Vérifier si on est dans la boucle
    if (!$post || !is_object($post)) {
        // Essayer de récupérer le post depuis la requête globale
        global $wp_query;
        if ($wp_query && $wp_query->in_the_loop && $wp_query->post) {
            $post = $wp_query->post;
        }
    }
    
    $post_id = 0;
    
    // 1. Essayer depuis les attributs
    if (isset($atts['id'])) {
        $post_id = intval($atts['id']);
    }
    // 2. Essayer depuis le post global
    elseif ($post && isset($post->ID)) {
        $post_id = $post->ID;
    }
    // 3. Dernier recours : le dernier post de la requête
    else {
        global $wp_query;
        if ($wp_query && $wp_query->posts) {
            $post_id = $wp_query->posts[0]->ID ?? 0;
        }
    }
    
    // URL de l'image par défaut
    $default_image = get_stylesheet_directory_uri() . '/assets/images/default-image.jpg';
    
    // Vérifier si le fichier existe
    if (!file_exists(get_stylesheet_directory() . '/assets/images/default-image.jpg')) {
        $default_image = 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
    }
    
    // Si on a un ID d'article
    if ($post_id > 0) {
        // Vérifier si l'article a une image
        if (has_post_thumbnail($post_id)) {
            $image_url = get_the_post_thumbnail_url($post_id, 'large');
            $title = get_the_title($post_id);
            
            return '<img src="' . esc_url($image_url) . '" 
                    class="fallback-image" 
                    style="border-radius:18px; width:100%; height:300px; object-fit:cover; display:block;" 
                    alt="' . esc_attr($title) . '">';
        } else {
            // Image par défaut
            $title = get_the_title($post_id);
            return '<img src="' . esc_url($default_image) . '" 
                    class="fallback-image" 
                    style="border-radius:18px; width:100%; height:300px; object-fit:cover; display:block;" 
                    alt="' . esc_attr($title) . '">';
        }
    } else {
        // Image par défaut sans ID
        return '<img src="' . esc_url($default_image) . '" 
                class="fallback-image" 
                style="border-radius:18px; width:100%; height:300px; object-fit:cover; display:block;" 
                alt="Image par défaut">';
    }
}
add_shortcode('featured_or_default', 'themechild_featured_or_default');

// FILTRE pour aider le shortcode à trouver le bon post
add_action('wp', function() {
    // S'assurer que le post global est défini dans les requêtes
    if (is_main_query() && in_the_loop()) {
        // Rien à faire, WordPress gère déjà
    }
});