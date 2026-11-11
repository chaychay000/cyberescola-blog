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
	if ( is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=31536000' );
	}
}
add_action( 'send_headers', 'ce_cabecalhos_seguranca' );
add_action( 'admin_init', 'ce_cabecalhos_seguranca' );
add_action( 'login_init', 'ce_cabecalhos_seguranca' );
