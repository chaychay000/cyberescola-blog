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
	wp_enqueue_style( 'ce-neon', get_stylesheet_uri(), array( 'ce-neon-fonts' ), CE_NEON_VERSION . '.' . (int) @filemtime( get_stylesheet_directory() . '/style.css' ) );
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
		'ce_author_bio'  => array( 'Texto da autora', "Artista, educadora e designer educacional\nEscreve sobre Arte, Educação e Teatro.", 'sanitize_textarea_field', 'textarea' ),
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
		<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> Cyber Escola · Todos os direitos reservados</span>
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
function ce_author_bio() {
	$novo = "Artista, educadora e designer educacional\nEscreve sobre Arte, Educação e Teatro.";
	$bio  = (string) ce_opt( 'ce_author_bio', $novo );
	if ( '' === trim( $bio ) || false !== strpos( $bio, 'Atriz, diretora e professora de Teatro' ) ) {
		$bio = $novo;
	}
	return $bio;
}
function ce_author_card( $class = '' ) {
	$photo_id = (int) ce_opt( 'ce_author_photo', 0 );
	if ( ! $photo_id ) {
		$autoras  = get_users( array( 'role' => 'ce_autora', 'number' => 1, 'meta_key' => 'ce_foto', 'fields' => 'ID' ) );
		$photo_id = $autoras ? (int) get_user_meta( $autoras[0], 'ce_foto', true ) : 0;
	}
	$photo = $photo_id ? wp_get_attachment_image_url( $photo_id, 'medium' ) : ce_img( 'ana-reis.jpg' );
	?>
	<section class="glass box author-box <?php echo esc_attr( $class ); ?>">
		<div class="author-photo"><img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( ce_opt( 'ce_author_name', 'Ana Reis' ) ); ?>" loading="lazy"></div>
		<h3><?php echo esc_html( ce_opt( 'ce_author_name', 'Ana Reis' ) ); ?></h3>
		<p><?php echo nl2br( esc_html( ce_author_bio() ) ); // phpcs:ignore ?></p>
		<?php if ( ce_opt( 'ce_author_drt', 'DRT 0011405/MG' ) ) : ?><span class="drt"><?php echo esc_html( ce_opt( 'ce_author_drt', 'DRT 0011405/MG' ) ); ?></span><?php endif; ?>
	</section>
	<?php
}
function ce_sidebar() { ?>
<aside class="sidebar" aria-label="Barra lateral">
	<?php ce_author_card(); ?>
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

/* Botões de compartilhar (só nos artigos do blog) */
function ce_share_icon( $rede ) {
	$i = array(
		'whatsapp'  => '<path fill="#fff" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.6.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3a.4.4 0 0 0 0-.4l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3Z"/>',
		'facebook'  => '<path fill="#fff" d="M14 8.5V6.8c0-.8.5-1 .9-1H17V2.1L14 2c-3.3 0-4 2.5-4 4.1v2.4H8V12h2v10h4V12h2.7l.4-3.5H14Z"/>',
		'telegram'  => '<path fill="#fff" d="M21.9 4.3 18.7 19.4c-.2 1-.9 1.3-1.8.8l-4.8-3.6-2.3 2.2c-.3.3-.5.5-1 .5l.3-4.9 9-8.1c.4-.3-.1-.5-.6-.2L6.4 13 1.6 11.5c-1-.3-1-1 .2-1.5L20.6 2.9c.9-.3 1.6.2 1.3 1.4Z"/>',
		'x'         => '<path fill="#fff" d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.2-8.3L1.8 3h6.4l4.4 5.8L17.8 3Zm-1.1 16.2h1.7L7.3 4.7H5.5l11.2 14.5Z"/>',
		'linkedin'  => '<path fill="#fff" d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3 9h4v12H3V9Zm7 0h3.8v1.7h.1c.5-1 1.8-2 3.8-2 4 0 4.8 2.6 4.8 6V21h-4v-5.6c0-1.3 0-3-1.9-3s-2.1 1.4-2.1 2.9V21h-4V9Z"/>',
		'pinterest' => '<path fill="#fff" d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.5 1.8-2.5.9 0 1.3.7 1.3 1.5 0 .9-.6 2.2-.9 3.4-.2 1 .5 1.9 1.6 1.9 1.9 0 3.3-2 3.3-4.9 0-2.5-1.8-4.3-4.4-4.3-3 0-4.8 2.3-4.8 4.6 0 .9.3 1.9.8 2.4.1.1.1.2.1.3l-.3 1.2c0 .2-.2.3-.4.2-1.4-.6-2.2-2.7-2.2-4.3 0-3.5 2.5-6.7 7.3-6.7 3.8 0 6.8 2.7 6.8 6.4 0 3.8-2.4 6.9-5.8 6.9-1.1 0-2.2-.6-2.6-1.3l-.7 2.7c-.3 1-1 2.2-1.4 2.9A10 10 0 1 0 12 2Z"/>',
		'email'     => '<g fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></g>',
		'link'      => '<g fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></g>',
		'share'     => '<g fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7"/><path d="M16 6l-4-4-4 4"/><path d="M12 2v13"/></g>',
	);
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . ( isset( $i[ $rede ] ) ? $i[ $rede ] : '' ) . '</svg>';
}
function ce_share() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$url   = get_permalink();
	$title = wp_strip_all_tags( get_the_title() );
	$u     = rawurlencode( $url );
	$t     = rawurlencode( $title );
	$img   = has_post_thumbnail() ? rawurlencode( (string) get_the_post_thumbnail_url( null, 'large' ) ) : '';
	$redes = array(
		array( 'whatsapp', 'WhatsApp', 'https://api.whatsapp.com/send?text=' . rawurlencode( $title . ' ' . $url ) ),
		array( 'facebook', 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . $u ),
		array( 'telegram', 'Telegram', 'https://t.me/share/url?url=' . $u . '&text=' . $t ),
		array( 'x', 'X', 'https://twitter.com/intent/tweet?url=' . $u . '&text=' . $t ),
		array( 'linkedin', 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . $u ),
		array( 'pinterest', 'Pinterest', 'https://pinterest.com/pin/create/button/?url=' . $u . '&description=' . $t . ( $img ? '&media=' . $img : '' ) ),
		array( 'email', 'E-mail', 'mailto:?subject=' . $t . '&body=' . rawurlencode( $title . "\n\n" . $url ) ),
	);
	?>
	<section class="ce-share" id="compartilhar" aria-label="Compartilhar este artigo">
		<h3>Gostou? <span class="grad-text">Compartilhe com outros professores</span></h3>
		<p>Ajude mais gente a levar o Teatro para a sala de aula.</p>
		<div class="ce-share-grid">
			<?php foreach ( $redes as $r ) : ?>
				<a class="ce-sh ce-sh-<?php echo esc_attr( $r[0] ); ?>" href="<?php echo esc_url( $r[2] ); ?>" <?php echo 'email' === $r[0] ? '' : 'target="_blank" rel="noopener nofollow"'; ?> aria-label="Compartilhar no <?php echo esc_attr( $r[1] ); ?>"><?php echo ce_share_icon( $r[0] ); // phpcs:ignore ?><span><?php echo esc_html( $r[1] ); ?></span></a>
			<?php endforeach; ?>
			<button type="button" class="ce-sh ce-sh-link" data-url="<?php echo esc_url( $url ); ?>"><?php echo ce_share_icon( 'link' ); // phpcs:ignore ?><span>Copiar link</span></button>
		</div>
	</section>
	<?php
}
add_action( 'wp_footer', function () {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	?>
	<div class="ce-share-barra" aria-hidden="true">
		<span>Gostou do artigo? <b>Compartilhe</b></span>
		<button type="button" tabindex="-1" data-title="<?php echo esc_attr( wp_strip_all_tags( get_the_title() ) ); ?>" data-url="<?php echo esc_url( get_permalink() ); ?>"><?php echo ce_share_icon( 'share' ); // phpcs:ignore ?>Compartilhar</button>
	</div>
	<script>
	( function () {
		function copiar( txt, ok ) {
			if ( navigator.clipboard && window.isSecureContext ) { navigator.clipboard.writeText( txt ).then( ok, function () {} ); return; }
			var a = document.createElement( 'textarea' ); a.value = txt; a.style.position = 'fixed'; a.style.opacity = '0'; document.body.appendChild( a ); a.select();
			try { document.execCommand( 'copy' ); ok(); } catch ( e ) {}
			a.remove();
		}
		document.querySelectorAll( '.ce-sh-link' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				var s = b.querySelector( 'span' ), antes = s.textContent;
				copiar( b.getAttribute( 'data-url' ), function () {
					b.classList.add( 'ok' ); s.textContent = 'Link copiado!';
					setTimeout( function () { b.classList.remove( 'ok' ); s.textContent = antes; }, 1800 );
				} );
			} );
		} );
		var barra = document.querySelector( '.ce-share-barra' ), bloco = document.getElementById( 'compartilhar' );
		if ( ! barra ) { return; }
		var btn = barra.querySelector( 'button' );
		btn.addEventListener( 'click', function () {
			if ( navigator.share ) {
				navigator.share( { title: btn.getAttribute( 'data-title' ), url: btn.getAttribute( 'data-url' ) } ).catch( function () {} );
			} else if ( bloco ) {
				bloco.scrollIntoView( { behavior: 'smooth', block: 'center' } );
			}
		} );
		var desceu = false, blocoVisivel = false;
		function atualiza() { barra.classList.toggle( 'on', desceu && ! blocoVisivel ); }
		window.addEventListener( 'scroll', function () { desceu = window.scrollY > 500; atualiza(); }, { passive: true } );
		if ( bloco && 'IntersectionObserver' in window ) {
			new IntersectionObserver( function ( e ) { blocoVisivel = e[0].isIntersecting; atualiza(); } ).observe( bloco );
		}
	} )();
	</script>
	<?php
} );
