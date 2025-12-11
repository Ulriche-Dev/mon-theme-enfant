<?php
/**
 * Charge le style du thème enfant
 * toto
 */
add_action( 'wp_enqueue_scripts', 'mon_theme_enfant_styles' );
function mon_theme_enfant_styles() {
    wp_enqueue_style(
        'mon-theme-enfant-style',
        get_stylesheet_uri(),
        array(),
        '1.0.0'
    );
}

/**
 * Injecte les éléments <head> spécifiques pour FSE
 */
add_action('wp_head', 'mon_theme_enfant_custom_head');
function mon_theme_enfant_custom_head() {

    // 🔹 Meta description (évite les doublons si plugin SEO installé)
    if (!defined('WPSEO_VERSION')) : ?>
        <meta name="description" content="<?php bloginfo('description'); ?>">
    <?php endif; ?>

    <!-- 🔹 Favicons -->
    <link rel="icon" href="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/favicon.ico'); ?>" sizes="any">
    <link rel="icon" type="image/svg+xml" href="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/favicon.svg'); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/apple-touch-icon.png'); ?>">

<?php
}

/**
 * 🚀 CUSTOM POST TYPE : ESPACE KIDS
 */
function abd_register_kids_universe() {

    $args = array(
        'labels' => array(
            'name'          => 'Espace Kids',
            'singular_name' => 'Article Kids',
            'add_new_item'  => 'Ajouter un article Kids',
            'edit_item'     => 'Éditer l\'article Kids',
        ),
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-smiley',
        'supports'     => array('title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions'),
        'rewrite'      => array('slug' => 'kids', 'with_front' => false),
        'template'     => array(
            array('core/pattern', array('slug' => 'mon-theme-enfant/article-kids-layout'))
        )
    );

    register_post_type('kids', $args);

    register_taxonomy('sujet_kids', 'kids', array(
        'label'        => 'Sujets Kids',
        'rewrite'      => array('slug' => 'kids-sujet'),
        'hierarchical' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'abd_register_kids_universe');

/**
 * Génère la navigation catégories du header
 */
function abd_header_categories_nav() {

    // 🔸 Bouton Kids
    $kids_cat = get_category_by_slug('kids');
    if ($kids_cat) {
        $kids_url = get_category_link($kids_cat->term_id);
        echo '<li><a href="' . esc_url($kids_url) . '" class="abd-nav-link kids-btn"><span>👶</span> KIDS</a></li>';
    }

    // 🔸 Barre séparatrice
    echo '<li style="width:1px; height:24px; background:#e2e8f0; margin:0 4px;"></li>';

    // 🔸 Bouton "Tout voir"
    $is_home = is_front_page() || is_home();
    echo '<li><a href="' . esc_url(home_url('/')) . '" class="abd-nav-link' . ($is_home && !is_category() ? ' active' : '') . '">Tout voir</a></li>';

    // 🔸 Autres catégories
    $categories = [
        'economie' => 'Économie',
        'tech'     => 'Tech',
        'sport'    => 'Sport',
        'ecologie' => 'Écologie',
        'societe'  => 'Société',
    ];

    foreach ($categories as $slug => $label) {
        $cat = get_category_by_slug($slug);
        if ($cat) {
            $url = get_category_link($cat->term_id);
            $active = is_category($slug) ? ' active' : '';
            echo '<li><a href="' . esc_url($url) . '" class="abd-nav-link' . $active . '">' . esc_html($label) . '</a></li>';
        }
    }
}

/**
 * Shortcode: Image mise en avant ou image par défaut
 */
function themechild_featured_or_default() {

    if (has_post_thumbnail()) {
        $image_url = get_the_post_thumbnail_url(null, 'large');
    } else {
        $default_path = get_stylesheet_directory() . '/assets/images/default-image.jpg';
        $image_url = file_exists($default_path)
            ? get_stylesheet_directory_uri() . '/assets/images/default-image.jpg'
            : 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80';
    }

    return '<img src="' . esc_url($image_url) . '" 
                class="fallback-image" 
                style="border-radius:18px; width:100%; height:300px; object-fit:cover; display:block;" 
                alt="' . esc_attr(get_the_title()) . '">';
}
add_shortcode('featured_or_default', 'themechild_featured_or_default');
