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
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-117669371-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-117669371-1');
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
            <a href="<?php echo home_url(); ?>" id="main-header-logo"></a>
            <div id="main-header-navigation">
                <?php wp_nav_menu( array( 'theme_location' => 'main-menu' ) ); ?>
                <div class="login-out">
                    <a href="<?php echo home_url(); ?>/login/">login</a>
                </div>
            </div>
        </header>
        <div id="breadcrumb"><?php echo ZazitHistoriiFrontend::getBreadcrumb()?></div>
        <div id="content">
