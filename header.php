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
    <?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <?php endif; ?>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
    <div class="site-inner">
        <header id="main-header">
            <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
            <?php if ( has_nav_menu( 'main-menu' ) ) : ?>
                <div class="main-menu">
                    <div id="menu_toggle"><span></span><span></span><span></span></div>
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'main-menu',
                        'menu_class'     => 'main-menu-list',
                    ) );
                    ?>
                </div>
            <?php endif; ?>
            <form action="<?php echo get_post_type_archive_link( '_events' ) ?>" method="get">
                <div class="search-form">
                    <input type="text" class="search-form-input" placeholder="<?php echo __( 'Search events...', THEME ) ?>" name="<?php echo __( 'search', THEME ) ?>">
                    <input type="submit" class="search-form-button" value="">
                </div>
            </form>
            <div class="user_panel">
                <?php
                if (!is_user_logged_in()) {
                    ?>
                    <a href="<?php echo get_home_url().'/login/' ?>" title="<?php echo __('Login', THEME); ?>"><?php echo __('Login', THEME); ?></a>
                    <?php
                } else {
                    ?>
                    <div class="current_user"><img src="<?php echo get_avatar_url( array( 'id' => get_current_user_id() ) ) ?>"><?php echo $frontend->get_user_name( get_current_user_id() ); ?></div>
                    <a class="show_on_hover_profil" href="<?php echo get_home_url().'/'.__('new_event', THEME).'/'; ?>" title="<?php echo __('Add new event', THEME); ?>"><?php echo __('Add new event', THEME); ?></a>
                    <a class="show_on_hover_profil" href="<?php echo wp_logout_url( get_home_url() ); ?>" title="<?php echo __('Logout', THEME); ?>"><?php echo __('Logout', THEME); ?></a>
                    <?php
                }
                ?>
            </div>
        </header>
        <div id="main-part">
            <div id="navigation-panel">
                <?php
                $ages = $frontend->get_all_ages_list();
                $current_age = '';
                if (isset($_GET['ages'])) {
                    $current_age = $_GET['ages'];
                }
                $navigation = $frontend->show_navigation_from_terms( $ages, $current_age );
                echo $navigation;
                ?>
            </div>
            <div id="content">
