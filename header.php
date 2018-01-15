<?php
/**
 * The template for displaying the header
 *
 *
 * @package zazit_historii
 * @subpackage zazit_historii
 * @since Zažít historii 1.0
 */
global $frontend;

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: "ca-pub-7412562488918859",
            enable_page_level_ads: true
        });
    </script>
    <title><?php wp_title(); ?></title>
    <?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <?php endif; ?>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
    <div class="site-inner">
        <header id="main-header">
            <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="icon"></a><a class="site-title-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                <div class="main-menu">
                <div id="menu_toggle"><span></span><span></span><span></span></div>
                <div class="user_panel">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="small_icon"></a>
                    <?php
                    if (!is_user_logged_in()) {
                        ?>
                        <div class="current_user">
                            <a class="header_login" href="<?php echo get_home_url().'/login/' ?>" title="<?php echo __('Login / Registration', THEME); ?>"><?php echo __('Login / Registration', THEME); ?></a>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="current_user"><img src="<?php
                            $file = get_user_meta( get_current_user_id(), '_profil_image', true );
                            if ($file != 0) {
                                echo wp_get_attachment_image_url($file, 'thumbnail');
                            } else {
                                echo get_avatar_url(get_current_user_id());
                            }
                            ?>">
                            <span><?php echo $frontend->get_user_name( get_current_user_id() ); ?></span>
                        </div>
                        <div class="user_menu">
                            <a class="show_on_hover_profil" href="<?php echo get_home_url().'/profil/'; ?>" title="<?php echo __('Profil', THEME); ?>"><?php echo __('Profil', THEME); ?></a>
                            <a class="show_on_hover_profil" href="<?php echo get_home_url().'/new-event/'; ?>" title="<?php echo __('Add new event', THEME); ?>"><?php echo __('Add new event', THEME); ?></a>
                            <a class="show_on_hover_profil" href="<?php echo get_the_permalink( 17 ); ?>" title="<?php echo get_the_title( 17 ) ?>"><?php echo get_the_title( 17 ); ?></a>
                            <a class="show_on_hover_profil" href="<?php echo wp_logout_url( get_home_url() ); ?>" title="<?php echo __('Logout', THEME); ?>"><?php echo __('Logout', THEME); ?></a>
                        </div>
                        <?php
                    }
                    ?>
                    <form action="<?php echo get_post_type_archive_link( '_events' ) ?>" method="get">
                        <div class="search-form">
                            <input type="text" class="search-form-input" placeholder="<?php echo __( 'Search events...', THEME ) ?>" name="<?php echo __( 'search', THEME ) ?>">
                            <input type="submit" class="search-form-button" value="">
                        </div>
                    </form>
                    </div>
                    <?php if ( has_nav_menu( 'main-menu' ) ) : ?>
                    <?php
                    /*wp_nav_menu( array(
                        'theme_location' => 'main-menu',
                        'menu_class'     => 'main-menu-list',
                    ) );*/
                    ?>

                    <?php endif; ?>
                </div>
        </header>
        <div id="main-part">
            <div id="navigation-panel">
                <h4><?php echo __('Ages', THEME); ?></h4>
                <?php
                $ages = $frontend->get_all_ages_list();
                $current_age = '';
                if (isset($_GET[ __( 'ages', THEME ) ])) {
                    $current_age = $_GET[ __( 'ages', THEME ) ];
                }
                $navigation = $frontend->show_navigation_from_terms( $ages, $current_age );
                echo $navigation;
                ?>
            </div>
            <div id="content">
