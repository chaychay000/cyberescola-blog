<?php
/**
 * Cyber Escola Painel — reforços de segurança.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* 1. Não mostrar a lista de usuários (nomes de login) para visitantes */
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $rota ) {
		if ( 0 === strpos( $rota, '/wp/v2/users' ) ) {
			unset( $endpoints[ $rota ] );
		}
	}
	return $endpoints;
} );

/* 2. Bloquear ?author=1 e /author/nome/ (descobrir nomes de usuário) */
add_action( 'template_redirect', function () {
	if ( is_user_logged_in() ) {
		return;
	}
	if ( is_author() || isset( $_GET['author'] ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 );

/* 3. oEmbed sem nome de usuário */
add_filter( 'oembed_response_data', function ( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
} );

/* 4. Mensagem de erro igual para usuário ou senha errados */
add_filter( 'login_errors', function () {
	return '<strong>Erro:</strong> usuário ou senha incorretos.';
} );

/* 5. Esconder a versão do WordPress */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );
function ce_tira_versao( $src ) {
	if ( $src && false !== strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'ce_tira_versao', 9999 );
add_filter( 'script_loader_src', 'ce_tira_versao', 9999 );

/* 6. Desligar XML-RPC e senhas de aplicativo (entradas que pulam a tela de login) */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', function () {
	return array();
} );
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
add_filter( 'wp_is_application_passwords_available', '__return_false' );

/* 7. Cabeçalhos de segurança */
function ce_cabecalhos_seguranca() {
	if ( headers_sent() ) {
		return;
	}
	header_remove( 'X-Powered-By' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	if ( is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=31536000' );
	}
}
add_action( 'send_headers', 'ce_cabecalhos_seguranca' );
add_action( 'admin_init', 'ce_cabecalhos_seguranca' );
add_action( 'login_init', 'ce_cabecalhos_seguranca' );

/* Ícone da aba (favicon) igual ao da página de vendas */
function ce_favicon_tags() {
	$base = 'https://cyberescola.com/';
	echo '<link rel="icon" href="' . esc_url( $base . 'favicon.ico' ) . '" sizes="any">' . "\n";
	echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( $base . 'favicon-32.png' ) . '">' . "\n";
	echo '<link rel="icon" type="image/png" sizes="192x192" href="' . esc_url( $base . 'favicon-192.png' ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $base . 'apple-touch-icon.png' ) . '">' . "\n";
	echo '<meta name="theme-color" content="#120E28">' . "\n";
}
add_action( 'wp_head', 'ce_favicon_tags', 2 );
add_action( 'admin_head', 'ce_favicon_tags', 2 );
add_action( 'login_head', 'ce_favicon_tags', 2 );
add_action( 'do_faviconico', function () {
	wp_redirect( 'https://cyberescola.com/favicon.ico', 301 );
	exit;
} );

/* Imagem padrão quando o link do blog é compartilhado (WhatsApp, Facebook etc.) */
function ce_og_imagem() {
	return 'https://blog.cyberescola.com/wp-content/uploads/2026/11/cyberescola-compartilhar.jpg';
}
foreach ( array( 'facebook', 'twitter' ) as $ce_rede ) {
	add_filter( "rank_math/opengraph/{$ce_rede}/image", 'ce_og_imagem', 99 );
	add_filter( "rank_math/opengraph/{$ce_rede}/image_array", function ( $img ) {
		if ( is_array( $img ) && isset( $img['url'] ) && ce_og_imagem() === $img['url'] ) {
			$img['width']  = 400;
			$img['height'] = 400;
			$img['type']   = 'image/jpeg';
			$img['alt']    = 'Cyber Escola';
		}
		return $img;
	}, 99 );
}
add_filter( 'rank_math/opengraph/twitter/twitter_card', function () {
	return 'summary';
}, 99 );

/* Sem comentários no blog */
add_filter( 'comments_open', '__return_false', 99 );
add_filter( 'pings_open', '__return_false', 99 );
add_filter( 'comments_array', '__return_empty_array', 99 );
add_filter( 'get_comments_number', '__return_zero', 99 );
add_action( 'init', function () {
	foreach ( array( 'post', 'page' ) as $tipo ) {
		remove_post_type_support( $tipo, 'comments' );
		remove_post_type_support( $tipo, 'trackbacks' );
	}
}, 100 );
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
}, 99 );
add_action( 'admin_bar_menu', function ( $bar ) {
	$bar->remove_node( 'comments' );
}, 999 );
add_filter( 'feed_links_show_comments_feed', '__return_false' );

/* Feed de comentários desligado (o blog não tem comentários) */
add_action( 'template_redirect', function () {
	if ( is_comment_feed() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 );

/* Limpeza única: post padrão, descrição da categoria e frase do blog */
add_action( 'init', function () {
	if ( get_option( 'ce_limpeza_v1' ) ) {
		return;
	}
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello && 'publish' === $hello->post_status ) {
		wp_update_post( array( 'ID' => $hello->ID, 'post_status' => 'draft' ) );
	}
	$cat = get_term_by( 'slug', 'blog', 'category' );
	if ( $cat && false !== stripos( (string) $cat->description, 'Your blog category' ) ) {
		wp_update_term( $cat->term_id, 'category', array( 'description' => 'Artigos, reflexões e dicas sobre Teatro e Educação para professores de Arte.' ) );
	}
	if ( '' === trim( (string) get_option( 'blogdescription' ) ) ) {
		update_option( 'blogdescription', 'Teatro na escola, sem complicação' );
	}
	update_option( 'ce_limpeza_v1', 1, false );
}, 30 );

/* Descrição da página inicial para o Google */
add_filter( 'rank_math/frontend/description', function ( $d ) {
	if ( ( is_front_page() || is_home() ) && '' === trim( (string) $d ) ) {
		return 'Ideias, jogos teatrais e dicas de planejamento para professores de Arte e Teatro. Blog da Cyber Escola.';
	}
	return $d;
} );
