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



function themechild_featured_or_default() {
    global $post;
    
    // Récupérer l'ID du post actuel
    $post_id = $post->ID ?? get_the_ID();
    
    // Vérifier si l'article a une image mise en avant
    if (has_post_thumbnail($post_id)) {
        $image_url = get_the_post_thumbnail_url($post_id, 'large');
        return '<img src="' . esc_url($image_url) . '" 
                class="article-featured-image" 
                alt="' . esc_attr(get_the_title($post_id)) . '">';
    } else {
        // Image par défaut - utilisez le chemin ABSOLU
        $default_image_url = get_stylesheet_directory_uri() . '/assets/images/default-image.jpg';
        
        // Vérifier si le fichier existe
        $default_image_path = get_stylesheet_directory() . '/assets/images/default-image.jpg';
        
        if (!file_exists($default_image_path)) {
            // Créer le dossier si nécessaire
            wp_mkdir_p(get_stylesheet_directory() . '/assets/images/');
            
            // URL d'image par défaut de secours
            $default_image_url = 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
        }
        
        return '<img src="' . esc_url($default_image_url) . '" 
                class="article-featured-image default-image" 
                alt="Image par défaut">';
    }
}
add_shortcode('featured_or_default', 'themechild_featured_or_default');

// Fonction pour forcer l'article épinglé à être en premier
function themechild_sticky_post_first($posts, $query) {
    // Ne s'applique qu'à la page d'accueil et aux requêtes principales
    if (is_home() && $query->is_main_query()) {
        // Récupérer les articles épinglés
        $sticky_posts = get_option('sticky_posts');
        
        if (!empty($sticky_posts)) {
            // Prendre seulement le PREMIER article épinglé
            $first_sticky = array_slice($sticky_posts, 0, 1);
            
            // Séparer les articles épinglés et non épinglés
            $sticky = array();
            $non_sticky = array();
            
            foreach ($posts as $post) {
                if (in_array($post->ID, $first_sticky)) {
                    $sticky[] = $post;
                } else {
                    $non_sticky[] = $post;
                }
            }
            
            // Réorganiser : 1 épinglé + les autres
            return array_merge($sticky, $non_sticky);
        }
    }
    
    return $posts;
}
add_filter('the_posts', 'themechild_sticky_post_first', 10, 2);