<?php
/**
 * Cyber Escola Neon — funções do tema.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CE_NEON_VERSION', '1.0.0' );

/* ---------- Configuração básica ---------- */
add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'cyberescola-neon' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus( array(
		'principal' => __( 'Menu principal', 'cyberescola-neon' ),
		'rodape'    => __( 'Menu do rodapé', 'cyberescola-neon' ),
	) );

	add_image_size( 'ce-card', 720, 400, true );
	add_image_size( 'ce-wide', 1600, 700, true );

	// Cores do editor iguais às da marca.
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => 'Laranja', 'slug' => 'laranja', 'color' => '#FF8A4C' ),
		array( 'name' => 'Rosa', 'slug' => 'rosa', 'color' => '#F0218A' ),
		array( 'name' => 'Roxo', 'slug' => 'roxo', 'color' => '#B45CF0' ),
		array( 'name' => 'Ciano', 'slug' => 'ciano', 'color' => '#1FC1D6' ),
		array( 'name' => 'Verde', 'slug' => 'verde', 'color' => '#8CE05A' ),
		array( 'name' => 'Branco', 'slug' => 'branco', 'color' => '#FFFFFF' ),
		array( 'name' => 'Lilás claro', 'slug' => 'lilas', 'color' => '#CFCDEA' ),
		array( 'name' => 'Fundo', 'slug' => 'fundo', 'color' => '#0B0A1A' ),
	) );
} );

/* ---------- CSS e JS ---------- */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'ce-neon-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'ce-neon', get_stylesheet_uri(), array( 'ce-neon-fonts' ), CE_NEON_VERSION );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
} );

add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

/* ---------- Estilos e modelos de bloco ---------- */
add_action( 'init', function () {
	register_block_style( 'core/group', array( 'name' => 'dica', 'label' => __( 'Caixa de dica', 'cyberescola-neon' ) ) );
	register_block_style( 'core/paragraph', array( 'name' => 'dica', 'label' => __( 'Caixa de dica', 'cyberescola-neon' ) ) );
	register_block_style( 'core/button', array( 'name' => 'neon', 'label' => __( 'Neon (degradê)', 'cyberescola-neon' ) ) );

	register_block_pattern_category( 'cyberescola', array( 'label' => 'Cyber Escola' ) );

	register_block_pattern( 'cyberescola/dica', array(
		'title'      => __( 'Caixa de dica', 'cyberescola-neon' ),
		'categories' => array( 'cyberescola' ),
		'content'    => '<!-- wp:paragraph {"className":"is-style-dica"} --><p class="is-style-dica"><strong>Dica da Ana:</strong> escreva aqui a sua dica.</p><!-- /wp:paragraph -->',
	) );

	$url = esc_url( ce_promo_url() );
	register_block_pattern( 'cyberescola/compra', array(
		'title'      => __( 'Botão de compra do e-book', 'cyberescola-neon' ),
		'categories' => array( 'cyberescola' ),
		'content'    => '<!-- wp:group {"className":"ce-compra","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} --><div class="wp-block-group ce-compra"><!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">50 Planos para uma Aula de Teatro</h3><!-- /wp:heading --><!-- wp:paragraph --><p>E-book + videoaulas de aplicação · 7 dias de garantia</p><!-- /wp:paragraph --></div><!-- /wp:group --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"className":"is-style-neon"} --><div class="wp-block-button is-style-neon"><a class="wp-block-button__link wp-element-button" href="' . $url . '">Quero meus 50 planos →</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group -->',
	) );
} );

/* ---------- Opções no Personalizar ---------- */
add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'ce_neon', array( 'title' => 'Cyber Escola Neon', 'priority' => 30 ) );

	$fields = array(
		'ce_promo_url'   => array( 'Link do botão "50 Planos" e do anúncio', 'https://cyberescola.com/', 'esc_url_raw', 'url' ),
		'ce_promo_price' => array( 'Preço no anúncio', 'R$ 47', 'sanitize_text_field', 'text' ),
		'ce_hero_title'  => array( 'Título do blog', 'Teatro na escola, sem complicação', 'sanitize_text_field', 'text' ),
		'ce_hero_text'   => array( 'Texto abaixo do título', 'Ideias, jogos teatrais e dicas de planejamento para professores de Arte e Teatro.', 'sanitize_text_field', 'textarea' ),
		'ce_author_name' => array( 'Nome da autora', 'Ana Reis', 'sanitize_text_field', 'text' ),
		'ce_author_bio'  => array( 'Texto da autora', 'Atriz, diretora e professora de Teatro. Escreve sobre Teatro e Educação.', 'sanitize_text_field', 'textarea' ),
		'ce_author_drt'  => array( 'Registro (DRT)', 'DRT 0011405/MG', 'sanitize_text_field', 'text' ),
	);
	foreach ( $fields as $id => $f ) {
		$wp_customize->add_setting( $id, array( 'default' => $f[1], 'sanitize_callback' => $f[2] ) );
		$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'ce_neon', 'type' => $f[3] ) );
	}
	$wp_customize->add_setting( 'ce_author_photo', array( 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'ce_author_photo', array(
		'label' => 'Foto da autora', 'section' => 'ce_neon', 'mime_type' => 'image',
	) ) );
} );

function ce_opt( $id, $default = '' ) {
	return get_theme_mod( $id, $default );
}
function ce_promo_url() {
	return ce_opt( 'ce_promo_url', 'https://cyberescola.com/' );
}
function ce_img( $file ) {
	$map = array(
		'cyber.png'    => 'https://cyberescola.com/img/ce3-logo.png',
		'escola.png'   => 'https://cyberescola.com/img/ce3-escola.png',
		'ana-reis.jpg' => 'https://cyberescola.com/img/ana-reis.jpg',
		'caderno.png'  => get_template_directory_uri() . '/caderno.svg',
	);
	return isset( $map[ $file ] ) ? $map[ $file ] : '';
}

/* ---------- Ajudantes ---------- */
function ce_reading_time( $post = null ) {
	$text  = wp_strip_all_tags( get_post_field( 'post_content', $post ) );
	$words = count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

function ce_cat_class( $term_id ) {
	return 'c' . ( (int) $term_id % 5 );
}

function ce_first_category_tag( $post = null ) {
	$cats = get_the_category( $post );
	if ( empty( $cats ) ) {
		return '';
	}
	$c = $cats[0];
	return sprintf( '<a class="tag %s" href="%s">%s</a>', esc_attr( ce_cat_class( $c->term_id ) ), esc_url( get_category_link( $c ) ), esc_html( $c->name ) );
}

function ce_cover( $size = 'ce-card', $post = null ) {
	if ( has_post_thumbnail( $post ) ) {
		echo '<div class="cover">' . get_the_post_thumbnail( $post, $size, array( 'loading' => 'lazy' ) ) . '</div>';
	}
}

function ce_author_avatar( $user_id ) {
	$foto = (int) get_user_meta( $user_id, 'ce_foto', true );
	$src  = $foto ? wp_get_attachment_image_url( $foto, 'thumbnail' ) : '';
	if ( $src ) {
		return '<span class="avatar-grad"><img src="' . esc_url( $src ) . '" alt="" loading="lazy"></span>';
	}
	$name     = get_the_author_meta( 'display_name', $user_id );
	$initials = '';
	foreach ( preg_split( '/\s+/u', trim( $name ) ) as $part ) {
		$initials .= mb_substr( $part, 0, 1 );
	}
	return '<span class="avatar-grad"><span>' . esc_html( mb_strtoupper( mb_substr( $initials, 0, 2 ) ) ) . '</span></span>';
}

/* Resumo mais curto */
add_filter( 'excerpt_length', function () { return 26; } );
add_filter( 'excerpt_more', function () { return '…'; } );

/* Menu de reserva quando nenhum menu foi criado */
function ce_fallback_menu() {
	echo '<ul>';
	echo '<li><a href="https://cyberescola.com/">Início</a></li>';
	echo '<li class="' . ( is_home() ? 'current-menu-item' : '' ) . '"><a href="' . esc_url( home_url( '/' ) ) . '">Blog</a></li>';
	foreach ( array( 'sobre' => 'Sobre', 'contato' => 'Contato' ) as $slug => $label ) {
		$p = get_page_by_path( $slug );
		if ( $p ) {
			echo '<li class="' . ( is_page( $p->ID ) ? 'current-menu-item' : '' ) . '"><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( $label ) . '</a></li>';
		}
	}
	echo '</ul>';
}

/* ---------- Partes da página ---------- */
function ce_header() { ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#conteudo">Pular para o conteúdo</a>
<header class="site-header">
	<div class="wrap">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo( 'name' ); ?> — início">
			<img src="<?php echo esc_url( ce_img( 'cyber.png' ) ); ?>" alt="Cyber" width="120" height="34">
			<img src="<?php echo esc_url( ce_img( 'escola.png' ) ); ?>" alt="Escola" width="120" height="28">
		</a>
		<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="menu-principal" aria-label="Abrir menu">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
		</button>
		<nav class="main-nav" id="menu-principal" aria-label="Menu principal">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'principal',
				'container'      => false,
				'depth'          => 1,
				'fallback_cb'    => 'ce_fallback_menu',
			) );
			?>
		</nav>
		<div class="header-tools">
			<?php get_search_form(); ?>
			<a class="btn-buy" href="<?php echo esc_url( ce_promo_url() ); ?>">50 Planos</a>
		</div>
	</div>
</header>
<main id="conteudo">
<?php }
function ce_footer() { ?>
</main>
<footer class="site-footer">
	<div class="wrap">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo( 'name' ); ?>">
			<img src="<?php echo esc_url( ce_img( 'cyber.png' ) ); ?>" alt="Cyber" width="120" height="34" loading="lazy">
			<img src="<?php echo esc_url( ce_img( 'escola.png' ) ); ?>" alt="Escola" width="120" height="28" loading="lazy">
		</a>
		<nav aria-label="Menu do rodapé">
			<?php
			wp_nav_menu( array(
				'theme_location' => has_nav_menu( 'rodape' ) ? 'rodape' : 'principal',
				'container'      => false,
				'depth'          => 1,
				'fallback_cb'    => 'ce_fallback_menu',
			) );
			?>
		</nav>
		<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( ce_opt( 'ce_author_name', 'Ana Reis' ) ); ?> · Todos os direitos reservados</span>
	</div>
</footer>
<script>(function () {
	var b = document.querySelector('.menu-toggle'), n = document.getElementById('menu-principal');
	if (!b || !n) return;
	b.addEventListener('click', function () {
		var open = n.classList.toggle('open');
		b.setAttribute('aria-expanded', open ? 'true' : 'false');
		b.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
	});
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
<?php }
function ce_sidebar() { ?><?php
$photo_id = (int) ce_opt( 'ce_author_photo', 0 );
if ( ! $photo_id ) {
	$autoras = get_users( array( 'role' => 'ce_autora', 'number' => 1, 'meta_key' => 'ce_foto', 'fields' => 'ID' ) );
	$photo_id = $autoras ? (int) get_user_meta( $autoras[0], 'ce_foto', true ) : 0;
}
$photo    = $photo_id ? wp_get_attachment_image_url( $photo_id, 'medium' ) : ce_img( 'ana-reis.jpg' );
?>
<aside class="sidebar" aria-label="Barra lateral">
	<section class="glass box author-box">
		<div class="author-photo"><img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( ce_opt( 'ce_author_name', 'Ana Reis' ) ); ?>" loading="lazy"></div>
		<h3><?php echo esc_html( ce_opt( 'ce_author_name', 'Ana Reis' ) ); ?></h3>
		<p><?php echo esc_html( ce_opt( 'ce_author_bio', 'Atriz, diretora e professora de Teatro. Escreve sobre Teatro e Educação.' ) ); ?></p>
		<?php if ( ce_opt( 'ce_author_drt', 'DRT 0011405/MG' ) ) : ?><span class="drt"><?php echo esc_html( ce_opt( 'ce_author_drt', 'DRT 0011405/MG' ) ); ?></span><?php endif; ?>
	</section>
	<section class="glass box promo">
		<h3>50 Planos para uma Aula de Teatro</h3>
		<p>E-book + videoaulas de aplicação</p>
		<div class="price"><?php echo esc_html( ce_opt( 'ce_promo_price', 'R$ 47' ) ); ?></div>
		<a class="btn-white" href="<?php echo esc_url( ce_promo_url() ); ?>">Quero meus planos →</a>
		<img src="<?php echo esc_url( ce_img( 'caderno.png' ) ); ?>" alt="" loading="lazy">
	</section>
	<?php
	$cats = get_categories( array( 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 8 ) );
	if ( $cats ) :
		?>
	<section class="glass box cats">
		<h3>Categorias</h3>
		<ul>
			<?php foreach ( $cats as $c ) : ?>
				<li><a href="<?php echo esc_url( get_category_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a><span><?php echo (int) $c->count; ?></span></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php endif; ?>
</aside>
<?php }
function ce_card() { ?>
<article <?php post_class( has_post_thumbnail() ? 'glass card' : 'glass card sem-capa' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?><a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php ce_cover( 'ce-card' ); ?></a><?php endif; ?>
	<div class="card-body">
		<div><?php echo ce_first_category_tag(); // phpcs:ignore ?></div>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( get_the_excerpt() ); ?></p>
		<div class="card-meta"><span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span><span><?php echo (int) ce_reading_time(); ?> min</span></div>
	</div>
</article>
<?php }
add_filter( 'get_search_form', function () {
	$sid = 'busca-' . wp_unique_id();
	ob_start(); ?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $sid ); ?>">Buscar</label>
	<button type="submit" aria-label="Buscar"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8F8BAE" stroke-width="2.4" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg></button>
	<input type="search" id="<?php echo esc_attr( $sid ); ?>" name="s" placeholder="Buscar artigos…" value="<?php echo esc_attr( get_search_query() ); ?>">
</form>
<?php
	return ob_get_clean();
} );

function ce_comments() {
	if ( post_password_required() ) { return; }
	$comments = get_comments( array( 'post_id' => get_the_ID(), 'status' => 'approve', 'order' => 'ASC' ) );
	echo '<section id="comments" class="glass comments">';
	if ( $comments ) {
		echo '<h2>' . count( $comments ) . ' comentário(s)</h2><ol class="comment-list">';
		wp_list_comments( array( 'style' => 'ol', 'avatar_size' => 40, 'short_ping' => true ), $comments );
		echo '</ol>';
	}
	comment_form( array( 'title_reply' => 'Deixe um comentário', 'label_submit' => 'Enviar comentário', 'class_submit' => 'submit btn-grad' ) );
	echo '</section>';
}
