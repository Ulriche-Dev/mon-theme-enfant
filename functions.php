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
