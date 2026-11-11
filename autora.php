<?php
/**
 * Cyber Escola Painel — perfil "Autora do blog".
 * Quem tem esse perfil vê só a tela "Meus artigos" e o editor, sem os menus do WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CE_AUTORA_ROLE', 'ce_autora' );

/* ---------- Cria / atualiza o perfil ---------- */
add_action( 'init', function () {
	if ( get_option( 'ce_autora_role_v' ) === '1' && get_role( CE_AUTORA_ROLE ) ) {
		return;
	}
	remove_role( CE_AUTORA_ROLE );
	add_role( CE_AUTORA_ROLE, 'Autora do blog', array(
		'read'                   => true,
		'level_2'                => true,
		'level_1'                => true,
		'level_0'                => true,
		'edit_posts'             => true,
		'edit_published_posts'   => true,
		'publish_posts'          => true,
		'delete_posts'           => true,
		'delete_published_posts' => true,
		'upload_files'           => true,
		'manage_categories'      => true,
	) );
	update_option( 'ce_autora_role_v', '1' );
} );

/* ---------- Foto do perfil (sem Gravatar) ---------- */
add_action( 'init', function () {
	register_meta( 'user', 'ce_foto', array(
		'type'          => 'integer',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function ( $allowed, $key, $user_id ) {
			return current_user_can( 'edit_user', $user_id );
		},
	) );
} );

add_filter( 'pre_get_avatar_data', function ( $args, $id_or_email ) {
	$uid = 0;
	if ( is_numeric( $id_or_email ) ) {
		$uid = (int) $id_or_email;
	} elseif ( $id_or_email instanceof WP_User ) {
		$uid = $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$uid = (int) $id_or_email->post_author;
	} elseif ( $id_or_email instanceof WP_Comment ) {
		$uid = (int) $id_or_email->user_id;
	} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
		$u   = get_user_by( 'email', $id_or_email );
		$uid = $u ? $u->ID : 0;
	}
	if ( $uid ) {
		$foto = (int) get_user_meta( $uid, 'ce_foto', true );
		$src  = $foto ? wp_get_attachment_image_url( $foto, 'thumbnail' ) : '';
		if ( $src ) {
			$args['url']          = $src;
			$args['found_avatar'] = true;
		}
	}
	return $args;
}, 10, 2 );

/* ---------- Caixas extras do editor só para administradores ---------- */
add_action( 'add_meta_boxes', function () {
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}
	foreach ( array( 'litespeed_meta_boxes', 'external_featured_image' ) as $id ) {
		foreach ( array( 'normal', 'side', 'advanced' ) as $ctx ) {
			remove_meta_box( $id, null, $ctx );
			remove_meta_box( $id, 'post', $ctx );
			remove_meta_box( $id, 'page', $ctx );
		}
	}
}, PHP_INT_MAX );

function ce_e_autora( $user = null ) {
	$user = $user ? $user : wp_get_current_user();
	return $user && ! empty( $user->roles ) && in_array( CE_AUTORA_ROLE, (array) $user->roles, true );
}

function ce_autora_url() {
	return admin_url( 'admin.php?page=ce-artigos' );
}

/* ---------- Depois do login vai direto para "Meus artigos" ---------- */
add_filter( 'login_redirect', function ( $to, $requested, $user ) {
	if ( $user instanceof WP_User && ce_e_autora( $user ) ) {
		return ce_autora_url();
	}
	return $to;
}, 99, 3 );

/* ---------- Sem barra preta no site ---------- */
add_filter( 'show_admin_bar', function ( $show ) {
	return ce_e_autora() ? false : $show;
} );

/* ---------- Só deixa abrir as telas permitidas ---------- */
add_action( 'admin_init', function () {
	if ( ! ce_e_autora() || wp_doing_ajax() ) {
		return;
	}
	global $pagenow;
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	$type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post';
	$ok   = in_array( $pagenow, array( 'post.php', 'profile.php', 'async-upload.php', 'media-upload.php' ), true )
		|| ( 'post-new.php' === $pagenow && 'post' === $type )
		|| ( 'admin.php' === $pagenow && 'ce-artigos' === $page );
	if ( ! $ok ) {
		wp_safe_redirect( ce_autora_url() );
		exit;
	}
} );

/* ---------- Tela "Meus artigos" ---------- */
add_action( 'admin_menu', function () {
	add_menu_page( 'Meus artigos', 'Meus artigos', 'edit_posts', 'ce-artigos', 'ce_autora_tela', 'dashicons-edit', 2 );
	if ( ! ce_e_autora() ) {
		remove_menu_page( 'ce-artigos' );
	}
} );

add_filter( 'admin_body_class', function ( $classes ) {
	return ce_e_autora() ? $classes . ' ce-so-editor' : $classes;
} );

add_action( 'admin_head', function () {
	if ( ! ce_e_autora() ) {
		return;
	}
	remove_all_actions( 'admin_notices' );
	remove_all_actions( 'all_admin_notices' );
	echo '<style>' . ce_autora_css() . '</style>';
}, 1 );

/* O editor abre em tela cheia (sem menu lateral e sem barra de cima) */
add_action( 'enqueue_block_editor_assets', function () {
	if ( ce_e_autora() ) {
		wp_add_inline_script( 'wp-edit-post', 'window.ceSoEditor = true;', 'before' );
	}
}, 5 );

/* "Sair" volta para a tela de entrada */
add_filter( 'logout_redirect', function ( $to, $requested, $user ) {
	if ( $user instanceof WP_User && ce_e_autora( $user ) ) {
		return wp_login_url();
	}
	return $to;
}, 99, 3 );

function ce_autora_tela() {
	$user  = wp_get_current_user();
	$first = $user->first_name ? $user->first_name : $user->display_name;
	$posts = get_posts( array(
		'author'      => $user->ID,
		'post_type'   => 'post',
		'post_status' => array( 'publish', 'draft', 'pending', 'future', 'private' ),
		'numberposts' => 100,
		'orderby'     => 'modified',
		'order'       => 'DESC',
	) );
	$count = array( 'publish' => 0, 'draft' => 0, 'future' => 0 );
	foreach ( $posts as $p ) {
		if ( isset( $count[ $p->post_status ] ) ) {
			$count[ $p->post_status ]++;
		}
	}
	$labels = array(
		'publish' => array( 'Publicado', 'pub' ),
		'draft'   => array( 'Rascunho', 'ras' ),
		'pending' => array( 'Em revisão', 'ras' ),
		'future'  => array( 'Agendado', 'age' ),
		'private' => array( 'Privado', 'ras' ),
	);
	?>
	<div class="ce-a">
		<header class="ce-a-top">
			<a class="ce-a-logo" href="<?php echo esc_url( ce_autora_url() ); ?>"><img src="https://cyberescola.com/img/ce3-logo.png" alt="Cyber Escola"></a>
			<nav>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">Ver o blog ↗</a>
				<a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">Minha conta</a>
				<a href="<?php echo esc_url( wp_logout_url() ); ?>">Sair</a>
			</nav>
		</header>

		<section class="ce-a-hero">
			<div>
				<span class="ce-a-kicker">ÁREA DA AUTORA</span>
				<h1>Olá, <?php echo esc_html( $first ); ?>!</h1>
				<p>O que vamos escrever hoje?</p>
			</div>
			<a class="ce-a-novo" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">＋ Escrever novo artigo</a>
		</section>

		<div class="ce-a-stats">
			<div><b><?php echo (int) $count['publish']; ?></b><span>publicados</span></div>
			<div><b><?php echo (int) $count['draft']; ?></b><span>rascunhos</span></div>
			<div><b><?php echo (int) $count['future']; ?></b><span>agendados</span></div>
		</div>

		<h2 class="ce-a-h2">Meus artigos</h2>
		<?php if ( empty( $posts ) ) : ?>
			<div class="ce-a-vazio">
				<p>Você ainda não tem artigos.</p>
				<a class="ce-a-novo" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">Escrever o primeiro</a>
			</div>
		<?php else : ?>
			<ul class="ce-a-lista">
				<?php foreach ( $posts as $p ) :
					$st    = isset( $labels[ $p->post_status ] ) ? $labels[ $p->post_status ] : array( $p->post_status, 'ras' );
					$thumb = get_the_post_thumbnail_url( $p, 'thumbnail' );
					$title = get_the_title( $p );
					?>
					<li>
						<a class="ce-a-capa" href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>">
							<?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt=""><?php else : ?><span>✎</span><?php endif; ?>
						</a>
						<div class="ce-a-info">
							<a class="ce-a-titulo" href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( $title ? $title : '(sem título)' ); ?></a>
							<div class="ce-a-meta">
								<span class="ce-a-pill <?php echo esc_attr( $st[1] ); ?>"><?php echo esc_html( $st[0] ); ?></span>
								<span>
									<?php
									if ( 'future' === $p->post_status ) {
										echo esc_html( 'Sai em ' . get_the_date( 'd/m/Y \à\s H:i', $p ) );
									} else {
										echo esc_html( 'Editado em ' . get_the_modified_date( 'd/m/Y', $p ) );
									}
									?>
								</span>
							</div>
						</div>
						<div class="ce-a-acoes">
							<a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>">Editar</a>
							<?php if ( 'publish' === $p->post_status ) : ?>
								<a href="<?php echo esc_url( get_permalink( $p ) ); ?>" target="_blank" rel="noopener">Ver</a>
							<?php endif; ?>
							<a class="del" href="<?php echo esc_url( get_delete_post_link( $p->ID ) ); ?>" onclick="return confirm('Mandar este artigo para a lixeira?');">Excluir</a>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
}

function ce_autora_css() {
	return <<<'CSS'
html.wp-toolbar { padding-top: 0 !important; }
body.ce-so-editor #wpadminbar, body.ce-so-editor #adminmenumain, body.ce-so-editor #adminmenuback, body.ce-so-editor #adminmenuwrap, body.ce-so-editor #wpfooter, body.ce-so-editor #screen-meta-links, body.ce-so-editor .notice, body.ce-so-editor .update-nag, body.ce-so-editor #hostinger-ai-assistant-root, body.ce-so-editor [class*="hostinger-ai"], body.ce-so-editor .hts-onboarding-button { display: none !important; }
body.ce-so-editor #wpcontent, body.ce-so-editor #wpfooter { margin-left: 0 !important; padding-left: 0 !important; }
body.ce-so-editor .editor-header .components-button[aria-label*="Setup"], body.ce-so-editor .editor-header a[href*="hostinger"] { display: none !important; }
body.ce-so-editor { background: #0B0A1A; }
.ce-a { max-width: 980px; margin: 0 auto; padding: 22px 20px 60px; font-family: 'Inter', system-ui, sans-serif; color: #EDEBFF; }
.ce-a a { text-decoration: none; }
.ce-a-top { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 12px 18px; border: 1px solid rgba(255,255,255,.12); border-radius: 18px; background: rgba(255,255,255,.04); }
.ce-a-logo img { height: 40px; display: block; }
.ce-a-top nav { display: flex; gap: 6px; flex-wrap: wrap; }
.ce-a-top nav a { color: #CFCDEA; padding: 8px 14px; border-radius: 10px; font-weight: 600; font-size: 14px; }
.ce-a-top nav a:hover { color: #fff; background: rgba(255,255,255,.08); }
.ce-a-hero { margin: 26px 0 18px; padding: 34px 36px; border-radius: 26px; border: 1px solid rgba(255,255,255,.12); display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
	background: radial-gradient(500px 260px at 0% 0%, rgba(180,92,240,.35), transparent 70%), radial-gradient(400px 260px at 100% 100%, rgba(31,193,214,.18), transparent 70%), rgba(255,255,255,.03); }
.ce-a-kicker { font-size: 12px; letter-spacing: .18em; font-weight: 700; color: #F0218A; }
.ce-a-hero h1 { font-family: 'Poppins', sans-serif; font-size: 40px; line-height: 1.1; margin: 8px 0 6px; color: #fff; padding: 0; }
.ce-a-hero p { margin: 0; color: #CFCDEA; font-size: 16px; }
.ce-a-novo { display: inline-block; padding: 14px 24px; border-radius: 14px; color: #fff !important; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 16px;
	background: linear-gradient(90deg, #FF8A4C, #F0218A 55%, #B45CF0); box-shadow: 0 14px 30px -10px rgba(240,33,138,.7); }
.ce-a-novo:hover { filter: brightness(1.08); }
.ce-a-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.ce-a-stats div { padding: 16px 20px; border-radius: 18px; border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.04); }
.ce-a-stats b { display: block; font-family: 'Poppins', sans-serif; font-size: 28px; color: #fff; }
.ce-a-stats span { color: #A9A6C9; font-size: 13px; }
.ce-a-h2 { font-family: 'Poppins', sans-serif; color: #fff; font-size: 20px; margin: 30px 0 12px; }
.ce-a-lista { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
.ce-a-lista li { display: flex; align-items: center; gap: 16px; padding: 12px 16px 12px 12px; margin: 0; border-radius: 18px; border: 1px solid rgba(255,255,255,.1); background: linear-gradient(160deg, rgba(255,255,255,.06), rgba(255,255,255,.02)); }
.ce-a-lista li:hover { border-color: rgba(192,132,252,.45); }
.ce-a-capa { width: 72px; height: 56px; flex: none; border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: rgba(180,92,240,.18); color: #C084FC; font-size: 22px; }
.ce-a-capa img { width: 100%; height: 100%; object-fit: cover; }
.ce-a-info { flex: 1; min-width: 0; }
.ce-a-titulo { display: block; color: #fff !important; font-weight: 700; font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ce-a-meta { display: flex; gap: 10px; align-items: center; margin-top: 6px; color: #A9A6C9; font-size: 13px; }
.ce-a-pill { padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.ce-a-pill.pub { background: rgba(140,224,90,.15); color: #B9F08F; }
.ce-a-pill.ras { background: rgba(255,138,76,.15); color: #FFB991; }
.ce-a-pill.age { background: rgba(31,193,214,.15); color: #7FE3F0; }
.ce-a-acoes { display: flex; gap: 6px; flex: none; }
.ce-a-acoes a { padding: 8px 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,.14); color: #EDEBFF; font-size: 13px; font-weight: 600; }
.ce-a-acoes a:hover { background: rgba(255,255,255,.08); color: #fff; }
.ce-a-acoes a.del { color: #FF9AB8; border-color: rgba(240,33,138,.3); }
.ce-a-vazio { text-align: center; padding: 40px; border-radius: 22px; border: 1px dashed rgba(255,255,255,.18); color: #CFCDEA; }
@media (max-width: 640px) {
	.ce-a-hero { padding: 24px; } .ce-a-hero h1 { font-size: 30px; }
	.ce-a-lista li { flex-wrap: wrap; } .ce-a-acoes { width: 100%; }
	.ce-a-stats b { font-size: 22px; }
}
CSS;
}
