<?php
/**
 * Cyber Escola Painel — fontes e tamanhos de letra no editor.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Lista de fontes: slug => array( nome, pilha CSS, pesos no Google Fonts ('' = só o normal) ) */
function ce_fontes_lista() {
	return array(
		'inter'              => array( 'Inter', "'Inter', sans-serif", '400;700' ),
		'poppins'            => array( 'Poppins', "'Poppins', sans-serif", '400;700' ),
		'fredoka'            => array( 'Fredoka', "'Fredoka', sans-serif", '400;700' ),
		'montserrat'         => array( 'Montserrat', "'Montserrat', sans-serif", '400;700' ),
		'roboto'             => array( 'Roboto', "'Roboto', sans-serif", '400;700' ),
		'open-sans'          => array( 'Open Sans', "'Open Sans', sans-serif", '400;700' ),
		'lato'               => array( 'Lato', "'Lato', sans-serif", '400;700' ),
		'nunito'             => array( 'Nunito', "'Nunito', sans-serif", '400;700' ),
		'quicksand'          => array( 'Quicksand', "'Quicksand', sans-serif", '400;700' ),
		'raleway'            => array( 'Raleway', "'Raleway', sans-serif", '400;700' ),
		'baloo-2'            => array( 'Baloo 2', "'Baloo 2', sans-serif", '400;700' ),
		'comfortaa'          => array( 'Comfortaa', "'Comfortaa', sans-serif", '400;700' ),
		'oswald'             => array( 'Oswald', "'Oswald', sans-serif", '400;700' ),
		'bebas-neue'         => array( 'Bebas Neue', "'Bebas Neue', sans-serif", '' ),
		'righteous'          => array( 'Righteous', "'Righteous', sans-serif", '' ),
		'merriweather'       => array( 'Merriweather', "'Merriweather', serif", '400;700' ),
		'lora'               => array( 'Lora', "'Lora', serif", '400;700' ),
		'playfair-display'   => array( 'Playfair Display', "'Playfair Display', serif", '400;700' ),
		'roboto-slab'        => array( 'Roboto Slab', "'Roboto Slab', serif", '400;700' ),
		'source-serif-4'     => array( 'Source Serif 4', "'Source Serif 4', serif", '400;700' ),
		'caveat'             => array( 'Caveat', "'Caveat', cursive", '400;700' ),
		'dancing-script'     => array( 'Dancing Script', "'Dancing Script', cursive", '400;700' ),
		'pacifico'           => array( 'Pacifico', "'Pacifico', cursive", '' ),
		'lobster'            => array( 'Lobster', "'Lobster', cursive", '' ),
		'indie-flower'       => array( 'Indie Flower', "'Indie Flower', cursive", '' ),
		'patrick-hand'       => array( 'Patrick Hand', "'Patrick Hand', cursive", '' ),
		'shadows-into-light' => array( 'Shadows Into Light', "'Shadows Into Light', cursive", '' ),
		'amatic-sc'          => array( 'Amatic SC', "'Amatic SC', cursive", '400;700' ),
		'permanent-marker'   => array( 'Permanent Marker', "'Permanent Marker', cursive", '' ),
		'chewy'              => array( 'Chewy', "'Chewy', cursive", '' ),
		'bangers'            => array( 'Bangers', "'Bangers', cursive", '' ),
		'luckiest-guy'       => array( 'Luckiest Guy', "'Luckiest Guy', cursive", '' ),
		'space-mono'         => array( 'Space Mono', "'Space Mono', monospace", '400;700' ),
	);
}

function ce_fontes_url( $slugs = null ) {
	$lista = ce_fontes_lista();
	$parts = array();
	foreach ( $lista as $slug => $f ) {
		if ( is_array( $slugs ) && ! in_array( $slug, $slugs, true ) ) {
			continue;
		}
		$fam     = str_replace( ' ', '+', $f[0] );
		$parts[] = 'family=' . $fam . ( $f[2] ? ':wght@' . $f[2] : '' );
	}
	if ( empty( $parts ) ) {
		return '';
	}
	return 'https://fonts.googleapis.com/css2?' . implode( '&', $parts ) . '&display=swap';
}

/* Fontes e tamanhos dentro das opções do editor */
add_filter( 'wp_theme_json_data_theme', function ( $theme_json ) {
	$familias = array();
	foreach ( ce_fontes_lista() as $slug => $f ) {
		$familias[] = array( 'name' => $f[0], 'slug' => $slug, 'fontFamily' => $f[1] );
	}
	$tamanhos = array(
		array( 'name' => 'Mini', 'slug' => 'ce-mini', 'size' => '12px' ),
		array( 'name' => 'Pequeno', 'slug' => 'ce-pequeno', 'size' => '14px' ),
		array( 'name' => 'Normal', 'slug' => 'ce-normal', 'size' => '17px' ),
		array( 'name' => 'Médio', 'slug' => 'ce-medio', 'size' => '20px' ),
		array( 'name' => 'Grande', 'slug' => 'ce-grande', 'size' => '24px' ),
		array( 'name' => 'Extra grande', 'slug' => 'ce-extra', 'size' => '32px' ),
		array( 'name' => 'Enorme', 'slug' => 'ce-enorme', 'size' => '40px' ),
		array( 'name' => 'Gigante', 'slug' => 'ce-gigante', 'size' => '56px' ),
		array( 'name' => 'Destaque', 'slug' => 'ce-destaque', 'size' => '72px' ),
	);
	return $theme_json->update_with( array(
		'version'  => 2,
		'settings' => array(
			'typography' => array(
				'fontFamilies'   => $familias,
				'fontSizes'      => $tamanhos,
				'customFontSize' => true,
				'fluid'          => false,
			),
		),
	) );
} );

/* Carrega as fontes dentro do editor */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'editor-styles' );
	add_editor_style( ce_fontes_url() );
}, 20 );
add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_style( 'ce-fontes-editor', ce_fontes_url(), array(), null );
} );

/* Deixa "Fonte" visível direto no painel Tipografia */
add_action( 'enqueue_block_editor_assets', function () {
	wp_add_inline_script( 'wp-blocks', <<<'JS'
wp.hooks.addFilter( 'blocks.registerBlockType', 'cyberescola/fonte-visivel', function ( settings ) {
	var t = settings.supports && settings.supports.typography;
	if ( t && t.__experimentalFontFamily ) {
		t.__experimentalDefaultControls = Object.assign( {}, t.__experimentalDefaultControls || {}, { fontFamily: true, fontSize: true } );
	}
	return settings;
} );
JS
	, 'after' );
} );

/* No site, carrega só as fontes usadas no artigo */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_singular() ) {
		return;
	}
	$content = (string) get_post_field( 'post_content', get_queried_object_id() );
	if ( false === strpos( $content, 'font-family' ) && false === strpos( $content, 'fontFamily' ) ) {
		return;
	}
	$usadas = array();
	foreach ( array_keys( ce_fontes_lista() ) as $slug ) {
		if ( false !== strpos( $content, 'has-' . $slug . '-font-family' ) || false !== strpos( $content, '"fontFamily":"' . $slug . '"' ) ) {
			$usadas[] = $slug;
		}
	}
	$url = ce_fontes_url( $usadas );
	if ( $url ) {
		wp_enqueue_style( 'ce-fontes-artigo', $url, array(), null );
	}
} );
