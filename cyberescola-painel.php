<?php
/**
 * Plugin Name: Cyber Escola Painel
 * Description: Visual neon da Cyber Escola na tela de login, no painel e no editor do WordPress.
 * Version: 1.0.1
 * Author: Cyber Escola
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CE_PAINEL_VERSION', '1.0.1' );
define( 'CE_PAINEL_URL', plugin_dir_url( __FILE__ ) );

/* O tema Cyber Escola Neon vem junto com este plugin */
register_theme_directory( __DIR__ . '/themes' );

function ce_painel_fonts() {
	return 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap';
}

/* ---------- Tela de login ---------- */
add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style( 'ce-painel-fonts', ce_painel_fonts(), array(), null );
	wp_add_inline_style( 'login', ce_painel_css( 'login' ) . '.login h1 a{background-image:url(https://cyberescola.com/img/ce3-logo.png),url(https://cyberescola.com/img/ce3-escola.png)!important}' );
} );
add_filter( 'login_headerurl', function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return get_bloginfo( 'name' ); } );
add_filter( 'login_message', function ( $message ) {
	if ( empty( $_GET['action'] ) && empty( $_GET['checkemail'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$message .= '<div class="ce-login-head"><span class="ce-kick">ÁREA DO BLOG</span><h2>Boas-vindas!</h2><p>Entre para escrever e publicar seus artigos.</p></div>';
	}
	return $message;
} );
add_action( 'login_footer', function () {
	echo '<div class="ce-login-sec"><span>Conexão segura</span><span>Protegido pela Cloudflare</span></div>';
} );

/* ---------- Esquema de cores do painel ---------- */
add_action( 'admin_init', function () {
	wp_admin_css_color(
		'cyberneon',
		'Cyber Neon',
		admin_url( 'css/colors/midnight/colors.min.css' ),
		array( '#0B0A1A', '#1A1733', '#F0218A', '#B45CF0' ),
		array( 'base' => '#A9A6C9', 'focus' => '#fff', 'current' => '#fff' )
	);
} );
add_filter( 'get_user_option_admin_color', function ( $color ) {
	return ( empty( $color ) || 'fresh' === $color ) ? 'cyberneon' : $color;
} );

/* ---------- Painel e editor ---------- */
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style( 'ce-painel-fonts', ce_painel_fonts(), array(), null );
	wp_register_style( 'ce-painel-admin', false, array(), CE_PAINEL_VERSION );
	wp_enqueue_style( 'ce-painel-admin' );
	$css = ce_painel_css( 'admin' );
	if ( 'cyberneon' === get_user_option( 'admin_color' ) ) {
		$css .= ce_painel_css( 'admin-colors' );
	}
	wp_add_inline_style( 'ce-painel-admin', $css );
} );
add_action( 'enqueue_block_editor_assets', function () {
	wp_register_style( 'ce-painel-editor', false, array(), CE_PAINEL_VERSION );
	wp_enqueue_style( 'ce-painel-editor' );
	wp_add_inline_style( 'ce-painel-editor', ce_painel_css( 'editor-ui' ) );
} );

/* Rodapé do painel */
add_filter( 'admin_footer_text', function () {
	return '<span class="ce-foot">Painel Cyber Escola · feito com WordPress</span>';
} );

/* Tira o logo do WordPress da barra do topo */
add_action( 'admin_bar_menu', function ( $bar ) {
	$bar->remove_node( 'wp-logo' );
}, 999 );

/* Barra do topo também neon quando o painel usa o esquema Cyber Neon */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin_bar_showing() && 'cyberneon' === get_user_option( 'admin_color' ) ) {
		wp_add_inline_style( 'admin-bar', '#wpadminbar{background:#0B0A1A;border-bottom:1px solid rgba(255,255,255,.08)}' );
	}
} );

/* Visual do conteúdo dentro do editor (igual ao artigo publicado) */
add_filter( 'block_editor_settings_all', function ( $settings ) {
	$settings['styles'][] = array( 'css' => '@import url(' . ce_painel_fonts() . ');' . ce_painel_css( 'editor-content' ) );
	return $settings;
} );

function ce_painel_css( $which ) {
	switch ( $which ) {
		case 'login':
			return <<<'CSS'
body.login {
	min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; color: #EDEBFF;
	background:
		radial-gradient(900px 700px at 0% 0%, rgba(180,92,240,.38), transparent 60%),
		radial-gradient(700px 600px at 100% 100%, rgba(31,193,214,.25), transparent 60%),
		radial-gradient(600px 500px at 70% 0%, rgba(240,33,138,.22), transparent 60%),
		#0B0A1A !important;
}
.login #login { width: 440px; max-width: calc(100% - 32px); margin: 0 auto; padding: 6vh 0 24px; }
.login h1 a {
	width: 250px !important; height: 136px !important; margin: 0 auto 22px !important;
	background-size: 250px auto, 210px auto !important; background-position: center top, center 78px !important; background-repeat: no-repeat !important;
}
.login form {
	position: relative; margin-top: 0; padding: 30px 34px 32px; border-radius: 30px; border: 1px solid rgba(255,255,255,.14);
	background: linear-gradient(160deg, rgba(255,255,255,.10), rgba(255,255,255,.03)) !important;
	box-shadow: 0 40px 90px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.14); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
}
.login form::before { content: ""; position: absolute; top: 0; left: 34px; right: 34px; height: 3px; border-radius: 0 0 3px 3px; background: linear-gradient(90deg,#FF8A4C,#F0218A,#B45CF0,#1FC1D6); }
.ce-login-head { margin: 0 0 18px; padding: 0 4px; }
.ce-kick { font-size: 12px; font-weight: 700; letter-spacing: .22em; background: linear-gradient(90deg,#FF8A4C,#F0218A,#B45CF0); -webkit-background-clip: text; background-clip: text; color: transparent; }
.ce-login-head h2 { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 28px; color: #fff; margin: 6px 0 4px; letter-spacing: -.5px; }
.ce-login-head p { color: #A9A6C9; font-size: 15px; margin: 0; }
.login label { color: #CFCDEA; font-size: 13px; font-weight: 600; }
.login input[type=text], .login input[type=password], .login input[type=email] {
	height: 52px; margin-top: 6px; padding: 0 16px; border-radius: 14px; font-size: 16px; color: #fff;
	background: rgba(10,9,24,.65) !important; border: 1px solid rgba(255,255,255,.14) !important; box-shadow: none !important;
}
.login input[type=text]:focus, .login input[type=password]:focus, .login input[type=email]:focus { border-color: #B45CF0 !important; box-shadow: 0 0 0 4px rgba(180,92,240,.22) !important; }
.login .button.wp-hide-pw { height: 52px; top: 6px; color: #A9A6C9; }
.login .button.wp-hide-pw .dashicons { color: #A9A6C9; }
.login .forgetmenot label { font-weight: 500; }
.login input[type=checkbox] { background: rgba(10,9,24,.65); border-color: rgba(255,255,255,.3); border-radius: 6px; }
.login input[type=checkbox]:checked::before { filter: hue-rotate(260deg) saturate(3); }
.login .cf-turnstile, .login .cf-turnstile-div { margin: 16px 0 6px; }
.login .submit { margin-top: 10px; clear: both; }
.wp-core-ui.login .button-primary, .login #wp-submit {
	float: none; width: 100%; height: 56px; margin-top: 14px; border: 0; border-radius: 16px; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 17px; color: #fff; text-shadow: none;
	background: linear-gradient(90deg,#FF8A4C,#F0218A 40%,#B45CF0 75%,#1FC1D6) !important;
	box-shadow: 0 20px 50px -6px rgba(240,33,138,.55), inset 0 1px 0 rgba(255,255,255,.45);
}
.login #wp-submit:hover { filter: brightness(1.08); }
.login #nav, .login #backtoblog { text-align: center; margin: 18px 0 0; padding: 0; }
.login #nav a, .login #backtoblog a { color: #CFCDEA !important; font-weight: 500; }
.login #nav a:hover, .login #backtoblog a:hover { color: #fff !important; }
.login .message, .login .notice, .login #login_error {
	border: 1px solid rgba(255,255,255,.14); border-left-width: 4px; border-radius: 14px; color: #EDEBFF;
	background: rgba(255,255,255,.06); box-shadow: none; margin-bottom: 16px;
}
.login .message, .login .notice-info { border-left-color: #1FC1D6; }
.login #login_error { border-left-color: #F0218A; }
.login #login_error a { color: #FF8FC6; }
.login .privacy-policy-page-link a { color: #A9A6C9; }
.language-switcher { display: none; }
.ce-login-sec { display: flex; justify-content: center; gap: 22px; margin-top: 18px; font-size: 12.5px; color: #8F8BAE; }
.ce-login-sec span::before { content: "✓ "; color: #8CE05A; }
CSS;
		case 'admin-colors':
			return <<<'CSS'
/* Esquema de cores "Cyber Neon" do painel */
#adminmenuback, #adminmenuwrap, #adminmenu { background: #0F0D22; }
#adminmenu a { color: #CFCDEA; }
#adminmenu div.wp-menu-image::before { color: #8F8BAE; }
#adminmenu a:hover, #adminmenu li.menu-top:hover, #adminmenu li.opensub > a.menu-top { color: #fff; background: rgba(180,92,240,.18); }
#adminmenu li.menu-top:hover div.wp-menu-image::before, #adminmenu li.opensub > a.menu-top div.wp-menu-image::before { color: #fff; }
#adminmenu .wp-submenu, #adminmenu .wp-has-current-submenu .wp-submenu, #adminmenu a.wp-has-current-submenu:focus + .wp-submenu { background: #16132E; }
#adminmenu .wp-submenu a { color: #A9A6C9; }
#adminmenu .wp-submenu a:hover, #adminmenu .wp-submenu li.current a { color: #fff; }
#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, #adminmenu li.current a.menu-top, #adminmenu .wp-menu-arrow, #adminmenu .wp-menu-arrow div {
	color: #fff; background: linear-gradient(90deg, #F0218A, #B45CF0);
}
#adminmenu li.wp-has-current-submenu div.wp-menu-image::before, #adminmenu li.current div.wp-menu-image::before { color: #fff; }
#adminmenu .awaiting-mod, #adminmenu .update-plugins { background: #F0218A; color: #fff; }
#collapse-button { color: #8F8BAE; }
#wpadminbar { background: #0B0A1A; border-bottom: 1px solid rgba(255,255,255,.08); }
#wpadminbar .ab-item, #wpadminbar a.ab-item, #wpadminbar > #wp-toolbar span.ab-label, #wpadminbar > #wp-toolbar span.noticon, #wpadminbar .ab-icon::before, #wpadminbar .ab-item::before { color: #CFCDEA; }
#wpadminbar .ab-top-menu > li:hover > .ab-item, #wpadminbar .ab-top-menu > li.hover > .ab-item { background: #1A1733; color: #fff; }
#wpadminbar .menupop .ab-sub-wrapper { background: #16132E; }
#wpadminbar .quicklinks .menupop ul li a { color: #CFCDEA; }
.wp-core-ui .button-primary { background: linear-gradient(90deg, #F0218A, #B45CF0); border-color: #B45CF0; color: #fff; text-shadow: none; box-shadow: none; }
.wp-core-ui .button-primary:hover, .wp-core-ui .button-primary:focus { background: linear-gradient(90deg, #FF2F98, #C06CFA); border-color: #C06CFA; color: #fff; }
a { color: #8B3FD9; }
a:hover, a:active { color: #F0218A; }
input[type=checkbox]:checked::before { filter: hue-rotate(250deg) saturate(2.5); }
input[type=radio]:checked::before { background: #B45CF0; }
.wp-core-ui input[type=reset]:hover, .wp-core-ui .button-link { color: #8B3FD9; }
CSS;
		case 'admin':
			return <<<'CSS'
/* Painel: fontes e detalhes neon (o resto segue o padrão do WordPress, para não quebrar plugins) */
#wpwrap, #wpcontent, #wpbody, body.wp-admin { font-family: 'Inter', system-ui, sans-serif; }
.wrap h1, .wrap h1.wp-heading-inline { font-family: 'Poppins', system-ui, sans-serif; font-weight: 700; letter-spacing: -.02em; }
#wpbody-content .wrap > h1::after { content: ""; display: block; width: 64px; height: 3px; border-radius: 3px; margin-top: 8px; background: linear-gradient(90deg,#FF8A4C,#F0218A,#B45CF0,#1FC1D6); }
.wrap .page-title-action { border-radius: 10px; border: 0; color: #fff; background: linear-gradient(90deg, #F0218A, #B45CF0); padding: 6px 14px; }
.wrap .page-title-action:hover { color: #fff; filter: brightness(1.1); }
.postbox, .card { border-radius: 14px; }
.ce-foot { color: #8F8BAE; }
CSS;
		case 'editor-ui':
			return <<<'CSS'
/* Editor de blocos com a moldura escura neon (barra do topo, lateral e inserção de blocos) */
.edit-post-layout, .editor-editor-interface, .interface-interface-skeleton__body { background: #0B0A1A; }
.editor-header, .edit-post-header, .interface-interface-skeleton__header .editor-header {
	background: rgba(11,10,26,.96) !important; border-bottom: 1px solid rgba(255,255,255,.08) !important; color: #EDEBFF;
}
.editor-header .components-button, .edit-post-header .components-button { color: #CFCDEA; }
.editor-header .components-button:hover:not(:disabled), .editor-header .components-button.is-pressed { color: #fff; }
.editor-header .components-button.is-pressed { background: rgba(180,92,240,.25); }
.editor-header .editor-post-publish-button, .editor-header .editor-post-publish-panel__toggle, .editor-post-publish-panel .editor-post-publish-button {
	background: linear-gradient(90deg,#FF8A4C,#F0218A 40%,#B45CF0 75%,#1FC1D6) !important; color: #fff !important; border-radius: 12px; border: 0; font-weight: 700;
	box-shadow: 0 10px 26px -8px rgba(240,33,138,.6);
}
.editor-header .editor-post-save-draft, .editor-header .editor-preview-dropdown__toggle { border-radius: 10px; }
.editor-document-bar { background: rgba(255,255,255,.06) !important; color: #EDEBFF; border-radius: 10px; }
.editor-document-bar .components-button, .editor-document-bar__title { color: #EDEBFF !important; }
.interface-interface-skeleton__sidebar, .interface-complementary-area, .editor-sidebar {
	background: #100E24 !important; color: #CFCDEA; border-left: 1px solid rgba(255,255,255,.08) !important;
}
.interface-complementary-area-header, .editor-sidebar__panel-tabs, .components-panel__header { background: #100E24 !important; border-color: rgba(255,255,255,.08) !important; }
.interface-complementary-area .components-panel, .interface-complementary-area .components-panel__body { background: transparent; border-color: rgba(255,255,255,.08); }
.interface-complementary-area .components-panel__body-toggle.components-button, .interface-complementary-area h2, .interface-complementary-area .components-base-control__label,
.interface-complementary-area label, .interface-complementary-area .components-button.is-tertiary, .interface-complementary-area .editor-post-panel__row-label { color: #EDEBFF !important; }
.interface-complementary-area .components-button.is-tertiary, .interface-complementary-area .components-button.is-link { color: #C084FC !important; }
.interface-complementary-area p, .interface-complementary-area .components-base-control__help, .interface-complementary-area .editor-post-panel__row-control { color: #A9A6C9; }
.interface-complementary-area .components-tab-panel__tabs-item, .editor-sidebar__panel-tabs [role=tab] { color: #A9A6C9; }
.interface-complementary-area .components-tab-panel__tabs-item.is-active, .editor-sidebar__panel-tabs [role=tab][aria-selected=true] { color: #fff; }
.interface-complementary-area input, .interface-complementary-area select, .interface-complementary-area textarea,
.interface-complementary-area .components-input-control__container, .interface-complementary-area .components-form-token-field__input-container {
	background: rgba(10,9,24,.65) !important; color: #fff !important; border-color: rgba(255,255,255,.14) !important;
}
.interface-complementary-area .components-input-control__backdrop { border-color: rgba(255,255,255,.14) !important; }
.block-editor-inserter__menu, .block-editor-inserter__panel-content { background: #100E24; color: #EDEBFF; }
.block-editor-block-types-list__item-title, .block-editor-inserter__panel-title { color: #CFCDEA !important; }
.block-editor-block-types-list__item:hover { background: rgba(180,92,240,.18) !important; }
.block-editor-block-types-list__item-icon { color: #C084FC !important; }
.block-editor-block-list__layout .is-selected > .block-editor-block-list__block-edit::before { box-shadow: 0 0 0 2px #B45CF0; }
.editor-styles-wrapper { background: #0B0A1A; }
CSS;
		case 'editor-content':
			return <<<'CSS'
/* Visual do editor de blocos igual ao artigo publicado */
body, .editor-styles-wrapper {
	background: #0B0A1A !important; color: #CFCDEA; font-family: 'Inter', system-ui, sans-serif; font-size: 18px; line-height: 1.8;
}
.editor-styles-wrapper h1, .editor-styles-wrapper h2, .editor-styles-wrapper h3, .editor-styles-wrapper h4,
.editor-styles-wrapper .editor-post-title__input, .editor-styles-wrapper .wp-block-post-title { font-family: 'Poppins', system-ui, sans-serif; color: #fff; letter-spacing: -.02em; }
.editor-styles-wrapper .wp-block-post-title, .editor-styles-wrapper .editor-post-title__input { font-size: 42px; font-weight: 800; }
.editor-styles-wrapper a { color: #FF8FC6; }
.editor-styles-wrapper strong { color: #fff; }
.editor-styles-wrapper mark { background: rgba(240,33,138,.25); color: #fff; }
.editor-styles-wrapper blockquote { padding: 20px 24px; border-radius: 18px; background: rgba(180,92,240,.12); border: 1px solid rgba(192,132,252,.3); color: #fff; }
.editor-styles-wrapper .is-style-dica { padding: 18px 22px; border-radius: 18px; background: linear-gradient(135deg, rgba(31,193,214,.14), rgba(180,92,240,.12)); border: 1px solid rgba(31,193,214,.35); color: #E3DFF7; }
.editor-styles-wrapper .is-style-dica strong:first-child { color: #7FE7F2; }
.editor-styles-wrapper .is-style-neon .wp-block-button__link { background: linear-gradient(90deg, #FF8A4C, #F0218A 40%, #B45CF0 75%, #1FC1D6) !important; color: #fff !important; border-radius: 16px; font-family: 'Poppins', sans-serif; font-weight: 700; }
.editor-styles-wrapper .ce-compra { padding: 28px; border-radius: 22px; background: linear-gradient(150deg, rgba(255,138,76,.22), rgba(240,33,138,.22) 45%, rgba(180,92,240,.24) 75%, rgba(31,193,214,.2)); border: 1px solid rgba(255,255,255,.18); }
.editor-styles-wrapper img { border-radius: 18px; }
CSS;
	}
	return '';
}
