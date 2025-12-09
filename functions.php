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



// 1. Fonction pour gérer l'image par défaut via filtre
function themechild_default_featured_image($html, $post_id, $post_thumbnail_id, $size, $attr) {
    // Si pas d'image mise en avant
    if (empty($html)) {
        $default_image = get_stylesheet_directory_uri() . '/assets/images/default-image.jpg';
        
        // Vérifier si le fichier existe
        if (!file_exists(get_stylesheet_directory() . '/assets/images/default-image.jpg')) {
            // Créer le dossier si nécessaire
            wp_mkdir_p(get_stylesheet_directory() . '/assets/images/');
            
            // URL d'image par défaut de secours
            $default_image = 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
        }
        
        $html = sprintf(
            '<img src="%s" class="wp-post-image default-image" alt="%s" style="width:100%%; height:300px; object-fit:cover; border-radius:18px;">',
            esc_url($default_image),
            esc_attr(get_the_title($post_id))
        );
    }
    
    return $html;
}
add_filter('post_thumbnail_html', 'themechild_default_featured_image', 10, 5);

// 2. Forcer l'article épinglé à être en premier (UN SEUL)
function themechild_sticky_post_first($posts) {
    if (is_home() || is_front_page()) {
        // Récupérer l'ID de l'article épinglé
        $sticky_posts = get_option('sticky_posts');
        
        if (!empty($sticky_posts)) {
            // Prendre seulement le PREMIER article épinglé
            $sticky_id = $sticky_posts[0];
            
            // Chercher l'article épinglé dans les résultats
            $sticky_post = null;
            $other_posts = array();
            
            foreach ($posts as $post) {
                if ($post->ID == $sticky_id) {
                    $sticky_post = $post;
                } else {
                    $other_posts[] = $post;
                }
            }
            
            // Si on a trouvé l'article épinglé, le mettre en premier
            if ($sticky_post) {
                // Limiter à 9 articles maximum
                $other_posts = array_slice($other_posts, 0, 8);
                return array_merge(array($sticky_post), $other_posts);
            }
        }
    }
    
    return $posts;
}
add_filter('the_posts', 'themechild_sticky_post_first');

// 3. Ajouter une classe CSS pour l'article épinglé
function themechild_sticky_post_class($classes, $class, $post_id) {
    if (is_sticky($post_id)) {
        $classes[] = 'sticky-post';
    }
    return $classes;
}
add_filter('post_class', 'themechild_sticky_post_class', 10, 3);
