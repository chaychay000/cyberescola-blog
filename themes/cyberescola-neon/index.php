<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
ce_header();
if ( is_404() ) {
?>
<?php
?>
<div class="wrap notfound">
	<div class="big grad-text">404</div>
	<h1>Página não encontrada</h1>
	<p style="color:var(--muted)">O endereço pode ter mudado. Que tal voltar para os artigos?</p>
	<p><a class="btn-grad big" href="<?php echo esc_url( home_url( '/' ) ); ?>">Ver os artigos</a></p>
</div>
<?php

} elseif ( is_page() ) {
?>
<?php
while ( have_posts() ) :
	the_post();
	?>
	<div class="wrap page-wrap">
		<article <?php post_class( 'glass entry' ); ?>>
			<h1><?php the_title(); ?></h1>
			<div class="entry-content"><?php the_content(); ?></div>
			<?php if ( is_page( 'sobre' ) ) { ce_author_card( 'author-fim' ); } ?>
		</article>
		<?php
		if ( comments_open() || get_comments_number() ) {
			ce_comments();
		}
		?>
	</div>
	<?php
endwhile;

} elseif ( is_singular() ) {
?>
<?php
while ( have_posts() ) :
	the_post();
	?>
	<div class="wrap">
		<header class="single-head">
			<div><?php echo ce_first_category_tag(); // phpcs:ignore ?></div>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			<div class="meta"><?php echo ce_author_avatar( get_the_author_meta( 'ID' ) ); // phpcs:ignore ?><div style="text-align:left"><b><?php the_author(); ?></b><br><?php echo esc_html( get_the_date( 'j \d\e F \d\e Y' ) ); ?> · <?php echo (int) ce_reading_time(); ?> min de leitura</div></div>
		</header>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="single-cover"><?php the_post_thumbnail( 'ce-wide', array( 'style' => 'width:100%;height:100%;object-fit:cover' ) ); ?></div>
		<?php endif; ?>
		<div class="article-layout">
			<div>
				<article <?php post_class( 'glass entry' ); ?>>
					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages(); ?>
					</div>
					<?php
					$tags = get_the_tags();
					if ( $tags ) :
						?>
						<div class="post-tags"><?php foreach ( $tags as $t ) : ?><a href="<?php echo esc_url( get_tag_link( $t ) ); ?>">#<?php echo esc_html( $t->name ); ?></a><?php endforeach; ?></div>
					<?php endif; ?>
					<?php ce_share(); ?>
				</article>
				<?php
				$prev = get_previous_post();
				$next = get_next_post();
				if ( $prev || $next ) :
					?>
					<nav class="post-nav" aria-label="Outros artigos">
						<div><?php if ( $prev ) : ?><a class="glass" href="<?php echo esc_url( get_permalink( $prev ) ); ?>"><small>← Anterior</small><?php echo esc_html( get_the_title( $prev ) ); ?></a><?php endif; ?></div>
						<div class="next"><?php if ( $next ) : ?><a class="glass" href="<?php echo esc_url( get_permalink( $next ) ); ?>"><small>Próximo →</small><?php echo esc_html( get_the_title( $next ) ); ?></a><?php endif; ?></div>
					</nav>
				<?php endif; ?>
				<?php
				if ( comments_open() || get_comments_number() ) {
					ce_comments();
				}
				?>
			</div>
			<?php ce_sidebar(); ?>
		</div>
	</div>
	<?php
endwhile;

} else {
?>
<?php
$is_front_list = ( is_home() && ! is_paged() );
?>
<div class="wrap">
	<header class="blog-head">
		<div>
			<?php if ( is_search() ) : ?>
				<div class="kicker">Busca</div>
				<h1>Resultados para “<span class="grad-text"><?php echo esc_html( get_search_query() ); ?></span>”</h1>
			<?php elseif ( is_archive() ) : ?>
				<div class="kicker"><?php echo is_category() ? 'Categoria' : 'Arquivo'; ?></div>
				<h1><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
				<?php if ( get_the_archive_description() ) : ?><p><?php echo esc_html( wp_strip_all_tags( get_the_archive_description() ) ); ?></p><?php endif; ?>
			<?php else : ?>
				<div class="kicker"><?php bloginfo( 'name' ); ?></div>
				<?php
				$title = ce_opt( 'ce_hero_title', 'Teatro na escola, sem complicação' );
				$parts = explode( ',', $title, 2 );
				?>
				<h1><?php echo esc_html( $parts[0] ); ?><?php if ( isset( $parts[1] ) ) : ?>, <span class="grad-text"><?php echo esc_html( trim( $parts[1] ) ); ?></span><?php endif; ?></h1>
				<p><?php echo esc_html( ce_opt( 'ce_hero_text', 'Ideias, jogos teatrais e dicas de planejamento para professores de Arte e Teatro.' ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		$top_cats = get_categories( array( 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 4 ) );
		if ( $top_cats && ! is_search() ) :
			?>
			<nav class="chips" aria-label="Categorias">
				<a class="chip <?php echo is_home() ? 'on' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">Todos</a>
				<?php foreach ( $top_cats as $c ) : ?>
					<a class="chip <?php echo is_category( $c->term_id ) ? 'on' : ''; ?>" href="<?php echo esc_url( get_category_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<?php
		if ( $is_front_list ) :
			the_post();
			?>
			<article <?php post_class( has_post_thumbnail() ? 'glass featured' : 'glass featured sem-capa' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?><a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true" style="display:flex"><?php ce_cover( 'ce-wide' ); ?></a><?php endif; ?>
				<div class="featured-body">
					<span class="tag">Em destaque<?php $c = get_the_category(); echo $c ? ' · ' . esc_html( $c[0]->name ) : ''; ?></span>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					<div class="meta"><?php echo ce_author_avatar( get_the_author_meta( 'ID' ) ); // phpcs:ignore ?><div><b><?php the_author(); ?></b><br><?php echo esc_html( get_the_date( 'j M Y' ) ); ?> · <?php echo (int) ce_reading_time(); ?> min de leitura</div></div>
					<a class="btn-grad big" href="<?php the_permalink(); ?>">Ler artigo <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
				</div>
			</article>
		<?php endif; ?>

		<div class="layout">
			<div>
				<?php if ( have_posts() ) : ?>
					<div class="section-title"><h2><?php echo $is_front_list ? 'Artigos recentes' : 'Artigos'; ?></h2></div>
					<div class="grid">
						<?php
						while ( have_posts() ) :
							the_post();
							ce_card();
						endwhile;
						?>
					</div>
				<?php elseif ( $is_front_list ) : ?>
					<div class="glass empty">Novos artigos chegam em breve.</div>
				<?php endif; ?>
				<nav class="pagination" aria-label="Páginas">
					<?php echo paginate_links( array( 'prev_text' => '←', 'next_text' => '→', 'mid_size' => 1 ) ); // phpcs:ignore ?>
				</nav>
			</div>
			<?php ce_sidebar(); ?>
		</div>
	<?php else : ?>
		<div class="layout">
			<div class="glass empty">
				<h2>Nada encontrado</h2>
				<p>Tente buscar outra palavra.</p>
				<?php get_search_form(); ?>
			</div>
			<?php ce_sidebar(); ?>
		</div>
	<?php endif; ?>
</div>
<?php

}
ce_footer();
