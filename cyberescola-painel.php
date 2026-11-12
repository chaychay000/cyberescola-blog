<?php
/**
 * Plugin Name: Cyber Escola Painel
 * Description: Visual neon da Cyber Escola na tela de login, no painel e no editor do WordPress.
 * Version: 2.0.7
 * Author: Cyber Escola
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CE_PAINEL_VERSION', '2.0.7' );
require_once __DIR__ . '/autora.php';
require_once __DIR__ . '/fontes.php';
require_once __DIR__ . '/seguranca.php';
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

/* ---------- Editor no estilo do modelo (v1.1) ---------- */
add_action( 'enqueue_block_editor_assets', function () {
	wp_add_inline_style( 'ce-painel-editor', ce_painel_extra( 'ui' ) );
	wp_add_inline_script( 'wp-edit-post', ce_painel_extra( 'js' ) );
}, 20 );
add_filter( 'block_editor_settings_all', function ( $settings ) {
	$settings['styles'][] = array( 'css' => ce_painel_extra( 'content' ) );
	return $settings;
}, 20 );
/* Menu lateral recolhido (só ícones) quando estiver escrevendo */
add_filter( 'admin_body_class', function ( $classes ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
		$classes .= ' folded ce-editor';
	}
	return $classes;
} );

function ce_painel_extra( $which ) {
	switch ( $which ) {
		case 'js':
			return <<<'JS'
( function ( wp ) {
	if ( ! wp || ! wp.plugins || ! wp.data || ! wp.element ) { return; }
	var el = wp.element.createElement;
	var Panel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	var ceTries = 0;
	var ceStop = wp.data.subscribe( function () {
		try {
			var prefs = wp.data.select( 'core/preferences' );
			var ceQuer = !! window.ceSoEditor;
			if ( prefs && !! prefs.get( 'core/edit-post', 'fullscreenMode' ) !== ceQuer ) {
				wp.data.dispatch( 'core/preferences' ).set( 'core/edit-post', 'fullscreenMode', ceQuer );
				ceStop();
			} else if ( ++ceTries > 400 ) { ceStop(); }
		} catch ( e ) {}
	} );
	/* Botão "Imagem" na barra de cima */
	function ceImagens() {
		if ( ! window.wp.media ) { return; }
		var frame = wp.media( { title: 'Inserir imagens', button: { text: 'Inserir no texto' }, library: { type: 'image' }, multiple: true } );
		frame.on( 'select', function () {
			var blocks = frame.state().get( 'selection' ).toJSON().map( function ( a ) {
				var big = a.sizes && a.sizes.large;
				return wp.blocks.createBlock( 'core/image', { id: a.id, url: big ? big.url : a.url, alt: a.alt || '', sizeSlug: big ? 'large' : 'full', linkDestination: 'none' } );
			} );
			if ( ! blocks.length ) { return; }
			var be = wp.data.select( 'core/block-editor' );
			var bd = wp.data.dispatch( 'core/block-editor' );
			var sel = be.getSelectedBlock();
			if ( sel && wp.blocks.isUnmodifiedDefaultBlock && wp.blocks.isUnmodifiedDefaultBlock( sel ) ) {
				bd.replaceBlocks( sel.clientId, blocks );
			} else if ( sel ) {
				bd.insertBlocks( blocks, be.getBlockIndex( sel.clientId ) + 1, be.getBlockRootClientId( sel.clientId ) || undefined );
			} else {
				bd.insertBlocks( blocks );
			}
		} );
		frame.open();
	}
	function ceBotaoImagem() {
		if ( document.querySelector( '.ce-btn-imagem' ) ) { return; }
		var alvo = document.querySelector( '.editor-document-tools__left' ) || document.querySelector( '.edit-post-header-toolbar__left' );
		if ( ! alvo ) { return; }
		var b = document.createElement( 'button' );
		b.type = 'button';
		b.className = 'components-button ce-btn-imagem';
		b.title = 'Inserir imagem (envie do computador ou escolha da biblioteca)';
		b.innerHTML = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="3" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.8" fill="currentColor"/><path d="M4.5 18l5-5 3.5 3.5 2.5-2.5 4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg><span>Imagem</span>';
		b.addEventListener( 'click', ceImagens );
		alvo.insertBefore( b, alvo.children[ 1 ] || null );
	}
	setInterval( ceBotaoImagem, 1000 );
	/* Autora: esconde do "+" os blocos que ela não usa */
	if ( window.ceSoEditor ) {
		wp.domReady( function () {
			var esconder = [ 'core/freeform', 'core/code', 'core/math', 'core/preformatted', 'core/verse', 'core/html', 'core/shortcode', 'core/archives', 'core/calendar', 'core/categories', 'core/latest-comments', 'core/latest-posts', 'core/page-list', 'core/rss', 'core/search', 'core/tag-cloud', 'core/more', 'core/nextpage', 'core/playlist', 'core/icon', 'core/navigation', 'core/site-logo', 'core/site-title', 'core/site-tagline', 'core/query', 'core/avatar', 'core/post-title', 'core/post-excerpt', 'core/post-featured-image', 'core/post-author-name', 'core/post-comments-count', 'core/post-comments-link', 'core/post-date', 'core/post-terms', 'core/post-navigation-link', 'core/post-time-to-read', 'core/read-more', 'core/comments', 'core/post-comments-form', 'core/loginout', 'core/term-count', 'core/term-description', 'core/term-name', 'core/terms-query', 'core/query-title', 'core/post-author-biography', 'core/breadcrumbs', 'rank-math/command', 'rank-math/rich-snippet', 'rank-math/howto-block', 'hostinger-ai-plugin/block', 'hostinger-reach/subscription' ];
			setTimeout( function () {
				try {
					var ed = wp.data.dispatch( 'core/editor' );
					if ( ed && ed.hideBlockTypes ) {
						ed.hideBlockTypes( esconder );
					} else if ( wp.data.dispatch( 'core/edit-post' ).hideBlockTypes ) {
						wp.data.dispatch( 'core/edit-post' ).hideBlockTypes( esconder );
					}
				} catch ( e ) {}
			}, 800 );
		} );
	}
	/* Mostra cada fonte com o próprio desenho na lista */
	if ( window.MutationObserver ) {
		var ceFontes = [ 'Inter', 'Poppins', 'Fredoka', 'Montserrat', 'Roboto', 'Open Sans', 'Lato', 'Nunito', 'Quicksand', 'Raleway', 'Baloo 2', 'Comfortaa', 'Oswald', 'Bebas Neue', 'Righteous', 'Merriweather', 'Lora', 'Playfair Display', 'Roboto Slab', 'Source Serif 4', 'Caveat', 'Dancing Script', 'Pacifico', 'Lobster', 'Indie Flower', 'Patrick Hand', 'Shadows Into Light', 'Amatic SC', 'Permanent Marker', 'Chewy', 'Bangers', 'Luckiest Guy', 'Space Mono' ];
		new MutationObserver( function () {
			var itens = document.querySelectorAll( '.components-custom-select-control__item:not([data-ce-fonte])' );
			for ( var i = 0; i < itens.length; i++ ) {
				var nome = ( itens[ i ].textContent || '' ).trim();
				itens[ i ].setAttribute( 'data-ce-fonte', '1' );
				if ( ceFontes.indexOf( nome ) > -1 ) {
					itens[ i ].style.fontFamily = "'" + nome + "'";
				}
			}
		} ).observe( document.body, { childList: true, subtree: true } );
	}
	/* Barra dos blocos só aparece quando o texto está marcado (ou em blocos sem texto) */
	( function () {
		var ultimo = null;
		wp.data.subscribe( function () {
			var be = wp.data.select( 'core/block-editor' );
			if ( ! be || ! be.getSelectionStart ) { return; }
			var s = be.getSelectionStart(), e = be.getSelectionEnd();
			var esconder = !! ( s && s.attributeKey && e && s.clientId === e.clientId && s.offset === e.offset );
			/* No celular: em blocos de texto, a barra só aparece com texto marcado (dois toques ou arrastar) */
			if ( ! esconder && window.innerWidth < 782 ) {
				var id = be.getSelectedBlockClientId();
				var faixa = !! ( s && e && s.attributeKey && ( s.clientId !== e.clientId || s.offset !== e.offset ) );
				var TXT = [ 'core/paragraph', 'core/heading', 'core/list', 'core/list-item', 'core/quote', 'core/pullquote', 'core/preformatted', 'core/verse', 'core/details' ];
				if ( id && ! faixa && TXT.indexOf( be.getBlockName( id ) ) > -1 ) { esconder = true; }
			}
			if ( esconder !== ultimo ) {
				ultimo = esconder;
				document.body.classList.toggle( 'ce-sem-selecao', esconder );
			}
		} );
	} )();
	/* Formatação do texto na aba Bloco, junto da Tipografia */
	( function () {
		if ( ! wp.hooks || ! wp.compose || ! wp.blockEditor || ! wp.richText || ! wp.components ) { return; }
		var el = wp.element.createElement;
		var BLOCOS = [ 'core/paragraph', 'core/heading', 'core/list-item', 'core/pullquote', 'core/button', 'core/details', 'core/quote' ];
		function htmlDe( v ) {
			if ( typeof v === 'string' ) { return v; }
			if ( v && v.toHTMLString ) { return v.toHTMLString(); }
			return v ? String( v ) : '';
		}
		function alvo( clientId ) {
			var be = wp.data.select( 'core/block-editor' );
			var s = be.getSelectionStart(), e = be.getSelectionEnd();
			var bloco = be.getBlock( clientId );
			if ( ! bloco ) { return null; }
			var chave = ( s && s.clientId === clientId && s.attributeKey ) ? s.attributeKey : ( bloco.attributes.content !== undefined ? 'content' : ( bloco.attributes.text !== undefined ? 'text' : ( bloco.attributes.summary !== undefined ? 'summary' : null ) ) );
			if ( ! chave ) { return null; }
			var valor = wp.richText.create( { html: htmlDe( bloco.attributes[ chave ] ) } );
			var ini = 0, fim = valor.text.length;
			if ( s && e && s.clientId === clientId && e.clientId === clientId && s.attributeKey === chave && s.offset !== e.offset ) {
				ini = Math.min( s.offset, e.offset );
				fim = Math.max( s.offset, e.offset );
			}
			valor.start = ini;
			valor.end = fim;
			return { chave: chave, valor: valor, ini: ini, fim: fim };
		}
		function aplicar( clientId, tipo, atributos ) {
			var a = alvo( clientId );
			if ( ! a || a.ini === a.fim ) { return; }
			var novo;
			if ( tipo === 'core/link' ) {
				var ativo = wp.richText.getActiveFormat( a.valor, 'core/link' );
				if ( ativo ) {
					novo = wp.richText.removeFormat( a.valor, 'core/link', a.ini, a.fim );
				} else {
					var url = window.prompt( 'Cole o endereço do link:', 'https://' );
					if ( ! url || url === 'https://' ) { return; }
					novo = wp.richText.applyFormat( a.valor, { type: 'core/link', attributes: { url: url } }, a.ini, a.fim );
				}
			} else {
				novo = wp.richText.toggleFormat( a.valor, { type: tipo, attributes: atributos } );
			}
			var upd = {};
			upd[ a.chave ] = wp.richText.toHTMLString( { value: novo } );
			wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes( clientId, upd );
		}
		function alinhar( props, lado ) {
			var tipo = wp.blocks.getBlockType( props.name );
			var at = tipo && tipo.attributes ? tipo.attributes : {};
			if ( at.textAlign ) {
				props.setAttributes( { textAlign: props.attributes.textAlign === lado ? undefined : lado } );
			} else if ( at.align ) {
				props.setAttributes( { align: props.attributes.align === lado ? undefined : lado } );
			} else {
				var st = Object.assign( {}, props.attributes.style || {} );
				st.typography = Object.assign( {}, st.typography || {}, { textAlign: lado } );
				props.setAttributes( { style: st } );
			}
		}
		function Botao( rotulo, titulo, estilo, onClick ) {
			return el( wp.components.Button, { className: 'ce-fmt-btn', label: titulo, showTooltip: true, onMouseDown: function ( ev ) { ev.preventDefault(); }, onClick: onClick }, el( 'span', { style: estilo }, rotulo ) );
		}
		function Painel( props ) {
			var id = props.clientId;
			return el( 'div', { className: 'ce-fmt' },
				el( 'div', { className: 'ce-fmt-tit' }, 'Formatação ', el( 'small', null, '(marque o texto antes)' ) ),
				el( 'div', { className: 'ce-fmt-linha' },
					Botao( 'B', 'Negrito', { fontWeight: 800 }, function () { aplicar( id, 'core/bold' ); } ),
					Botao( 'I', 'Itálico', { fontStyle: 'italic', fontFamily: 'Georgia, serif' }, function () { aplicar( id, 'core/italic' ); } ),
					Botao( 'S', 'Sublinhado', { textDecoration: 'underline' }, function () { aplicar( id, 'core/underline' ); } ),
					Botao( 'S', 'Riscado', { textDecoration: 'line-through' }, function () { aplicar( id, 'core/strikethrough' ); } ),
					Botao( '🔗', 'Link', {}, function () { aplicar( id, 'core/link' ); } ),
					Botao( 'x²', 'Sobrescrito', {}, function () { aplicar( id, 'core/superscript' ); } ),
					Botao( '✎', 'Marca-texto', { background: '#FFE45C', color: '#1A1433', padding: '0 4px', borderRadius: '4px' }, function () { aplicar( id, 'core/text-color', { 'class': 'has-inline-color', style: 'background-color:#FFE45C' } ); } )
				),
				el( 'div', { className: 'ce-fmt-linha' },
					Botao( '⯇', 'Alinhar à esquerda', {}, function () { alinhar( props, 'left' ); } ),
					Botao( '≡', 'Centralizar', {}, function () { alinhar( props, 'center' ); } ),
					Botao( '⯈', 'Alinhar à direita', {}, function () { alinhar( props, 'right' ); } ),
					Botao( '☰', 'Justificar', {}, function () { if ( window.ceToggleJust ) { window.ceToggleJust( props ); } } )
				)
			);
		}
		var comPainel = wp.compose.createHigherOrderComponent( function ( BlockEdit ) {
			return function ( props ) {
				if ( ! props.isSelected || BLOCOS.indexOf( props.name ) < 0 ) {
					return el( BlockEdit, props );
				}
				var Item = wp.components.__experimentalToolsPanelItem;
				var conteudo = el( Painel, props );
				var inspetor = Item
					? el( wp.blockEditor.InspectorControls, { group: 'typography' }, el( Item, { hasValue: function () { return false; }, label: 'Formatação', onDeselect: function () {}, isShownByDefault: true, panelId: props.clientId, className: 'ce-fmt-item' }, conteudo ) )
					: el( wp.blockEditor.InspectorControls, null, el( wp.components.PanelBody, { title: 'Formatação' }, conteudo ) );
				return el( wp.element.Fragment, null, el( BlockEdit, props ), inspetor );
			};
		}, 'ceFormatacao' );
		wp.hooks.addFilter( 'editor.BlockEdit', 'cyberescola/formatacao', comPainel );
	} )();
	/* Justificar texto (botão na barra do bloco) */
	( function () {
		if ( ! wp.blockEditor || ! wp.blockEditor.BlockControls || ! wp.components.ToolbarButton || ! wp.hooks ) { return; }
		var el = wp.element.createElement;
		var TIPOS = [ 'core/paragraph', 'core/heading', 'core/list', 'core/list-item', 'core/quote', 'core/pullquote', 'core/verse', 'core/details' ];
		function temJust( cn ) { return ( ' ' + ( cn || '' ) + ' ' ).indexOf( ' ce-justificado ' ) > -1; }
		function semJust( cn ) { var r = ( cn || '' ).split( /\s+/ ).filter( function ( c ) { return c && c !== 'ce-justificado'; } ).join( ' ' ); return r || undefined; }
		function alinhado( a ) {
			return a.textAlign || ( a.style && a.style.typography && a.style.typography.textAlign ) || ( [ 'left', 'center', 'right' ].indexOf( a.align ) > -1 ? a.align : null );
		}
		window.ceToggleJust = function ( props ) {
			var a = props.attributes, upd = {};
			if ( temJust( a.className ) ) {
				upd.className = semJust( a.className );
			} else {
				upd.className = ( ( a.className || '' ) + ' ce-justificado' ).trim();
				if ( [ 'left', 'center', 'right' ].indexOf( a.align ) > -1 ) { upd.align = undefined; }
				if ( a.textAlign ) { upd.textAlign = undefined; }
				if ( a.style && a.style.typography && a.style.typography.textAlign ) {
					var st = JSON.parse( JSON.stringify( a.style ) );
					delete st.typography.textAlign;
					upd.style = st;
				}
			}
			props.setAttributes( upd );
		};
		var icone = el( 'svg', { width: 24, height: 24, viewBox: '0 0 24 24', 'aria-hidden': true }, el( 'path', { d: 'M4 5.5h16V7H4zm0 4h16V11H4zm0 4h16V15H4zm0 4h16V19H4z', fill: 'currentColor' } ) );
		wp.hooks.addFilter( 'editor.BlockEdit', 'cyberescola/justificar', wp.compose.createHigherOrderComponent( function ( BlockEdit ) {
			return function ( props ) {
				var just = temJust( props.attributes.className );
				var al = alinhado( props.attributes );
				wp.element.useEffect( function () {
					if ( just && al ) { props.setAttributes( { className: semJust( props.attributes.className ) } ); }
				}, [ al ] );
				if ( ! props.isSelected || TIPOS.indexOf( props.name ) < 0 ) { return el( BlockEdit, props ); }
				return el( wp.element.Fragment, null, el( BlockEdit, props ),
					el( wp.blockEditor.BlockControls, { group: 'block' },
						el( wp.components.ToolbarButton, { icon: icone, label: 'Justificar', isPressed: just, onClick: function () { window.ceToggleJust( props ); } } )
					)
				);
			};
		}, 'ceJustificar' ) );
	} )();
	/* Imagem de capa em cima do título */
	( function () {
		var frame;
		function canvas() {
			var fr = document.querySelector( 'iframe[name="editor-canvas"]' );
			return ( fr && fr.contentDocument ) ? fr.contentDocument : document;
		}
		function abrir() {
			if ( ! wp.media ) { return; }
			if ( ! frame ) {
				frame = wp.media( { title: 'Imagem de capa do artigo', library: { type: 'image' }, button: { text: 'Usar como capa' }, multiple: false } );
				frame.on( 'select', function () {
					var a = frame.state().get( 'selection' ).first();
					if ( a ) { wp.data.dispatch( 'core/editor' ).editPost( { featured_media: a.id } ); }
				} );
			}
			frame.open();
		}
		function botao( d, txt, acao, cls ) {
			var b = d.createElement( 'button' );
			b.type = 'button'; b.className = cls || ''; b.setAttribute( 'data-ce', acao ); b.textContent = txt;
			return b;
		}
		function desenhar() {
			var ed = wp.data.select( 'core/editor' );
			if ( ! ed || ed.getCurrentPostType() !== 'post' ) { return; }
			var d = canvas();
			var w = d.querySelector( '.editor-visual-editor__post-title-wrapper, .edit-post-visual-editor__post-title-wrapper' );
			if ( ! w ) { return; }
			var box = w.querySelector( '.ce-capa' );
			if ( ! box ) {
				box = d.createElement( 'div' );
				box.className = 'ce-capa';
				box.setAttribute( 'contenteditable', 'false' );
				w.insertBefore( box, w.firstChild );
				box.addEventListener( 'click', function ( e ) {
					var b = e.target.closest( '[data-ce]' );
					if ( ! b ) { return; }
					e.preventDefault(); e.stopPropagation();
					if ( b.getAttribute( 'data-ce' ) === 'rem' ) { wp.data.dispatch( 'core/editor' ).editPost( { featured_media: 0 } ); } else { abrir(); }
				} );
			}
			var id = ed.getEditedPostAttribute( 'featured_media' ) || 0, url = '';
			if ( id ) {
				var m = wp.data.select( 'core' ).getMedia( id );
				if ( m ) {
					var s = m.media_details && m.media_details.sizes;
					url = ( s && ( ( s.large && s.large.source_url ) || ( s.full && s.full.source_url ) ) ) || m.source_url || '';
				}
			}
			var k = id + '|' + url;
			if ( box.getAttribute( 'data-k' ) === k ) { return; }
			box.setAttribute( 'data-k', k );
			box.textContent = '';
			if ( ! id ) {
				box.appendChild( botao( d, '＋  Adicionar imagem de capa', 'add', 'ce-capa-add' ) );
			} else if ( ! url ) {
				var l = d.createElement( 'div' ); l.className = 'ce-capa-load'; l.textContent = 'Carregando capa…'; box.appendChild( l );
			} else {
				var im = d.createElement( 'div' ); im.className = 'ce-capa-img';
				im.style.backgroundImage = 'url("' + encodeURI( url ) + '")';
				var ac = d.createElement( 'div' ); ac.className = 'ce-capa-acoes';
				ac.appendChild( botao( d, 'Trocar capa', 'add' ) );
				ac.appendChild( botao( d, 'Remover', 'rem' ) );
				im.appendChild( ac ); box.appendChild( im );
			}
		}
		wp.data.subscribe( desenhar );
		setInterval( desenhar, 800 );
	} )();
	/* Celular: abre a aba Configurações como no computador */
	( function () {
		if ( window.innerWidth >= 782 ) { return; }
		var voltas = 0, mexeu = false;
		document.addEventListener( 'click', function ( ev ) {
			if ( ev.target.closest && ev.target.closest( '.interface-complementary-area-header, .interface-pinned-items, .editor-header__settings' ) ) { mexeu = true; }
		}, true );
		var iv = setInterval( function () {
			var i = wp.data.select( 'core/interface' ), ed = wp.data.select( 'core/editor' );
			if ( ! i || ! ed || ! ed.getCurrentPostId() ) { return; }
			if ( mexeu || ++voltas > 40 ) { clearInterval( iv ); return; }
			if ( ! i.getActiveComplementaryArea( 'core' ) ) {
				wp.data.dispatch( 'core/interface' ).enableComplementaryArea( 'core', 'edit-post/document' );
			}
		}, 300 );
	} )();
	if ( ! Panel ) { return; }
	/* Uma aba só: as opções do bloco aparecem dentro da aba do artigo */
	( function () {
		var BlockInspector = wp.blockEditor && wp.blockEditor.BlockInspector;
		if ( ! BlockInspector ) { return; }
		function abaAtiva() {
			var i = wp.data.select( 'core/interface' );
			if ( ! i ) { return null; }
			return i.getActiveComplementaryArea( 'core' ) || i.getActiveComplementaryArea( 'core/edit-post' );
		}
		wp.data.subscribe( function () {
			if ( abaAtiva() === 'edit-post/block' ) {
				wp.data.dispatch( 'core/edit-post' ).openGeneralSidebar( 'edit-post/document' );
			}
		} );
		function BlocoAqui() {
			var qtd = wp.data.useSelect( function ( select ) { return select( 'core/block-editor' ).getSelectedBlockCount(); }, [] );
			wp.element.useEffect( function () {
				if ( qtd && ! wp.data.select( 'core/editor' ).isEditorPanelOpened( 'ce-bloco/ce-bloco' ) ) {
					wp.data.dispatch( 'core/editor' ).toggleEditorPanelOpened( 'ce-bloco/ce-bloco' );
				}
			}, [ qtd > 0 ] );
			if ( ! qtd ) { return null; }
			return el( Panel, { name: 'ce-bloco', title: 'Bloco selecionado', className: 'ce-bloco-panel', initialOpen: true }, el( BlockInspector ) );
		}
		wp.plugins.registerPlugin( 'ce-bloco', { render: BlocoAqui } );
	} )();
	function Pronto() {
		var d = wp.data.useSelect( function ( select ) {
			var ed = select( 'core/editor' );
			return {
				title: ed.getEditedPostAttribute( 'title' ) || '',
				excerpt: ed.getEditedPostAttribute( 'excerpt' ) || '',
				media: ed.getEditedPostAttribute( 'featured_media' ),
				cats: ed.getEditedPostAttribute( 'categories' ) || [],
				content: ed.getEditedPostContent() || ''
			};
		}, [] );
		var text = d.content.replace( /<!--[\s\S]*?-->/g, ' ' ).replace( /<[^>]+>/g, ' ' );
		var words = ( text.match( /\S+/g ) || [] ).length;
		var mins = Math.max( 1, Math.ceil( words / 200 ) );
		var imgs = ( d.content.match( /<img\s/g ) || [] ).length;
		var noAlt = ( d.content.match( /<img(?![^>]*alt="[^"]+")[^>]*>/g ) || [] ).length;
		var heads = ( d.content.match( /<h[2-4][\s>]/g ) || [] ).length;
		var items = [
			[ 'Título preenchido', d.title.trim().length > 0 ],
			[ 'Resumo preenchido', d.excerpt.trim().length > 0 ],
			[ 'Imagem de destaque', !! d.media ],
			[ 'Categoria escolhida', d.cats.filter( function ( c ) { return c !== 1; } ).length > 0 ],
			[ 'Texto alternativo nas imagens', imgs === 0 || noAlt === 0 ],
			[ 'Link para o e-book', /cyberescola\.com\/?["#?]/.test( d.content ) ]
		];
		var done = items.filter( function ( i ) { return i[ 1 ]; } ).length;
		return el( Panel, { name: 'ce-pronto', title: 'Pronto para publicar? ' + done + ' de ' + items.length, className: 'ce-pronto' },
			el( 'div', { className: 'ce-stats' },
				el( 'span', null, el( 'b', null, words ), ' palavras' ),
				el( 'span', null, el( 'b', null, mins + ' min' ), ' de leitura' ),
				el( 'span', null, el( 'b', null, heads ), ' títulos' ),
				el( 'span', null, el( 'b', null, imgs ), ' imagens' )
			),
			el( 'ul', { className: 'ce-check' }, items.map( function ( i, k ) {
				return el( 'li', { key: k, className: i[ 1 ] ? 'ok' : 'todo' }, i[ 0 ] );
			} ) )
		);
	}
	wp.plugins.registerPlugin( 'ce-pronto', { render: Pronto } );
} )( window.wp );
JS;
		case 'ui':
			return <<<'CSS'
body.ce-editor, body.ce-editor #wpwrap, body.ce-editor #wpcontent, body.ce-editor #wpbody { background: #0B0A1A; }
body.ce-editor #adminmenu .wp-menu-image::before { font-size: 20px; }
body.ce-editor #adminmenu li.menu-top > a { border-radius: 14px; margin: 6px 8px; }
.editor-header, .edit-post-header { min-height: 72px; padding-right: 12px; }
.editor-header .components-button.has-icon:not(.editor-post-publish-button):not(.editor-post-publish-panel__toggle):not(.is-primary) {
	border: 1px solid rgba(255,255,255,.12); border-radius: 12px; background: rgba(255,255,255,.04); margin: 0 3px; min-width: 40px; height: 40px;
}
.editor-header .editor-post-preview, .editor-header .editor-preview-dropdown__toggle, .editor-header .editor-post-save-draft, .editor-header .editor-post-saved-state {
	border: 1px solid rgba(255,255,255,.14) !important; border-radius: 12px !important; color: #EDEBFF !important; height: 40px; padding: 0 14px !important; box-shadow: none !important;
}
.editor-header .editor-post-saved-state { color: #B9F08F !important; border-color: rgba(140,224,90,.3) !important; background: rgba(140,224,90,.1); }
.editor-header .editor-post-publish-button, .editor-header .editor-post-publish-panel__toggle {
	height: 44px !important; padding: 0 24px !important; border-radius: 13px !important; font-family: 'Poppins', sans-serif !important; font-size: 15px !important;
}
.editor-document-bar { border-radius: 12px !important; border: 1px solid rgba(255,255,255,.12) !important; min-height: 40px; }
.editor-document-bar__title, .editor-document-bar .components-button { font-family: 'Inter', sans-serif; }
.editor-document-bar__post-title, .editor-document-bar .components-text { color: #EDEBFF !important; }
.editor-document-bar__post-type-label { color: #A9A6C9 !important; }
.block-editor-inserter__search .components-input-control__container, .components-search-control .components-input-control__container { background: rgba(255,255,255,.06) !important; border-radius: 10px; }
.block-editor-inserter__search input[type=search], .components-search-control input[type=search] { background: transparent !important; color: #fff !important; }
.ce-pronto .components-panel__body-toggle.components-button { font-size: 13px !important; }
.editor-header .components-button.ce-btn-imagem {
	height: 40px; padding: 0 14px 0 11px; margin: 0 4px; gap: 7px; border-radius: 12px; font-weight: 600; font-size: 13.5px; color: #fff;
	background: linear-gradient(135deg, rgba(31,193,214,.22), rgba(180,92,240,.28)); border: 1px solid rgba(31,193,214,.45); box-shadow: 0 8px 20px -10px rgba(31,193,214,.7);
}
.editor-header .components-button.ce-btn-imagem:hover { color: #fff; border-color: #1FC1D6; background: linear-gradient(135deg, rgba(31,193,214,.35), rgba(180,92,240,.4)); }
.media-modal { border-radius: 18px; overflow: hidden; }
/* Lateral em cartões */
.interface-complementary-area .components-panel { padding: 6px 0 20px; }
.interface-complementary-area .components-panel__body {
	margin: 12px 14px !important; border: 1px solid rgba(255,255,255,.12) !important; border-radius: 18px;
	background: linear-gradient(160deg, rgba(255,255,255,.08), rgba(255,255,255,.025)) !important; box-shadow: 0 16px 34px -18px rgba(0,0,0,.8);
}
.interface-complementary-area .components-panel__body-toggle.components-button { font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 14px; }
.interface-complementary-area .components-panel__body.is-opened > .components-panel__body-title { border-bottom: 0; }
.editor-sidebar__panel-tabs, .interface-complementary-area .components-tab-panel__tabs { margin: 12px 14px 0; padding: 4px; border-radius: 12px; background: rgba(255,255,255,.05) !important; border: 0 !important; }
.editor-sidebar__panel-tabs [role=tab], .interface-complementary-area .components-tab-panel__tabs-item { border-radius: 9px; height: 36px; font-weight: 600; }
.editor-sidebar__panel-tabs [role=tab][aria-selected=true], .interface-complementary-area .components-tab-panel__tabs-item.is-active { background: rgba(255,255,255,.12); box-shadow: none !important; }
.editor-sidebar__panel-tabs [role=tab]::after, .interface-complementary-area .components-tab-panel__tabs-item::after { display: none; }
.interface-complementary-area .editor-post-card-panel { margin: 12px 14px; padding: 16px; border-radius: 18px; border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.04); }
.interface-complementary-area .editor-post-card-panel__title, .interface-complementary-area .editor-post-card-panel__title-name { color: #fff !important; font-family: 'Poppins', sans-serif; }
.interface-complementary-area .editor-post-featured-image__toggle, .interface-complementary-area .editor-post-featured-image__preview { border-radius: 14px; background: rgba(255,255,255,.05); color: #CFCDEA; }
.interface-complementary-area .components-form-token-field__token { border-radius: 999px; overflow: hidden; }
.interface-complementary-area .components-form-token-field__token-text, .interface-complementary-area .components-form-token-field__remove-token { background: rgba(240,33,138,.25) !important; color: #fff !important; }
.interface-complementary-area .components-checkbox-control__input[type=checkbox] { background: rgba(10,9,24,.65); border-color: rgba(255,255,255,.3); }
.interface-complementary-area .components-checkbox-control__input[type=checkbox]:checked { background: #B45CF0; border-color: #B45CF0; }
/* Aba Bloco: letras e controles claros */
.interface-complementary-area { color: #EDEBFF; }
.interface-complementary-area .components-truncate, .interface-complementary-area .components-text, .interface-complementary-area .components-heading, .interface-complementary-area [class*="__text"], .interface-complementary-area [class*="__heading"], .interface-complementary-area .block-editor-panel-color-gradient-settings__color-name, .interface-complementary-area .block-editor-block-card__title, .interface-complementary-area .components-base-control__label, .interface-complementary-area label, .interface-complementary-area legend, .interface-complementary-area .components-tools-panel-header h2 { color: #EDEBFF !important; }
.interface-complementary-area .block-editor-block-card__description, .interface-complementary-area .components-base-control__help { color: #A9A6C9 !important; }
.interface-complementary-area a, .interface-complementary-area .components-external-link, .interface-complementary-area .components-external-link__contents, .interface-complementary-area .components-external-link__icon, .interface-complementary-area .block-editor-skip-to-selected-block { color: #C084FC !important; }
.interface-complementary-area svg { color: #CFCDEA; fill: currentColor; }
.interface-complementary-area .components-toggle-group-control { background: rgba(255,255,255,.05) !important; border-color: rgba(255,255,255,.18) !important; }
.interface-complementary-area .components-toggle-group-control > div[role=presentation], .interface-complementary-area .components-toggle-group-control [class*="backdrop"] { background: rgba(180,92,240,.45) !important; }
.interface-complementary-area .components-toggle-group-control button, .interface-complementary-area .components-toggle-group-control [class*="ButtonContentView"], .interface-complementary-area .components-toggle-group-control [class*="LabelView"] { color: #CFCDEA !important; }
.interface-complementary-area .components-toggle-group-control button[aria-checked=true], .interface-complementary-area .components-toggle-group-control button[data-active-item] { color: #fff !important; }
.interface-complementary-area .block-editor-block-styles__item { background: rgba(255,255,255,.05) !important; border: 1px solid rgba(255,255,255,.18) !important; color: #EDEBFF !important; border-radius: 10px !important; }
.interface-complementary-area .block-editor-block-styles__item.is-active { background: linear-gradient(135deg, rgba(240,33,138,.45), rgba(180,92,240,.45)) !important; border-color: transparent !important; color: #fff !important; }
.interface-complementary-area .block-editor-block-styles__item span { color: inherit !important; }
.interface-complementary-area .block-editor-tools-panel-color-gradient-settings__item, .interface-complementary-area .block-editor-panel-color-gradient-settings__dropdown { background: rgba(255,255,255,.04) !important; border-color: rgba(255,255,255,.18) !important; color: #EDEBFF !important; }
.interface-complementary-area input[type=text], .interface-complementary-area input[type=number], .interface-complementary-area input[type=url], .interface-complementary-area select, .interface-complementary-area textarea, .interface-complementary-area .components-input-control__container { background: rgba(10,9,24,.6) !important; color: #fff !important; border-color: rgba(255,255,255,.2) !important; }
.interface-complementary-area .components-input-control__backdrop { border-color: rgba(255,255,255,.2) !important; }
/* Cores escuras em tudo (tela cheia, inserção e lateral) */
.editor-document-bar, .editor-document-bar__command, .editor-document-bar__command:focus { background: rgba(255,255,255,.06) !important; color: #EDEBFF !important; }
.editor-document-bar:hover, .editor-document-bar__command:hover { background: rgba(255,255,255,.11) !important; }
.block-editor-block-inspector__no-blocks { background: transparent !important; color: #A9A6C9 !important; border: 0 !important; }
.interface-interface-skeleton__secondary-sidebar, .editor-inserter-sidebar, .editor-list-view-sidebar, .block-editor-tabbed-sidebar, .block-editor-tabbed-sidebar__tablist-and-close-button, .block-editor-inserter__search, .block-editor-inserter__panel-header, .block-editor-inserter__tabs { background: #14112A !important; border-color: rgba(255,255,255,.08) !important; color: #EDEBFF; }
.block-editor-tabbed-sidebar__close-button { background: transparent !important; color: #CFCDEA !important; }
.block-editor-tabbed-sidebar [role=tab] { color: #CFCDEA !important; }
.block-editor-tabbed-sidebar [role=tab][aria-selected=true] { color: #fff !important; }
.components-search-control { background: transparent !important; }
.editor-visual-editor, .edit-post-visual-editor { background: #0B0A1A !important; }
.editor-layout__toggle-publish-panel { background: #14112A !important; border-color: rgba(255,255,255,.08) !important; }
.components-form-toggle__track { background: rgba(255,255,255,.08) !important; border-color: rgba(255,255,255,.35) !important; }
.components-form-toggle.is-checked .components-form-toggle__track { background: #B45CF0 !important; border-color: #B45CF0 !important; }
.components-form-toggle__thumb { background: #CFCDEA !important; border-color: #CFCDEA !important; }
.rank-math-toolbar-score { background: rgba(255,255,255,.06) !important; color: #EDEBFF !important; border-color: rgba(255,255,255,.14) !important; }
.interface-interface-skeleton *, .editor-header * { scrollbar-color: rgba(180,92,240,.45) transparent; scrollbar-width: thin; }
/* Busca de comandos (Ctrl+K) */
.commands-command-menu { background: #14112A !important; border: 1px solid rgba(255,255,255,.14) !important; border-radius: 18px !important; }
.commands-command-menu__header { border-bottom: 1px solid rgba(255,255,255,.1) !important; }
.commands-command-menu__header input, .commands-command-menu__header input[type=text], .commands-command-menu input[cmdk-input] { background: transparent !important; color: #fff !important; box-shadow: none !important; border: 0 !important; }
.commands-command-menu__header input::placeholder, .commands-command-menu input[cmdk-input]::placeholder { color: #8F8BAE !important; }
.commands-command-menu__header svg { fill: #C084FC !important; }
.commands-command-menu [cmdk-item] { color: #CFCDEA !important; border-radius: 10px; }
.commands-command-menu [cmdk-item] svg { fill: currentColor !important; }
.commands-command-menu [cmdk-item][aria-selected=true], .commands-command-menu [cmdk-item][data-selected=true], .commands-command-menu [cmdk-item]:hover { background: linear-gradient(90deg, rgba(240,33,138,.4), rgba(180,92,240,.4)) !important; color: #fff !important; }
.commands-command-menu [cmdk-group-heading], .commands-command-menu [cmdk-empty] { color: #8F8BAE !important; }
/* Pré-visualização dos blocos no "+" */
.block-editor-inserter__preview-container { background: #14112A !important; border: 1px solid rgba(255,255,255,.14) !important; border-radius: 16px !important; box-shadow: 0 30px 70px rgba(0,0,0,.7) !important; overflow: hidden; }
.block-editor-inserter__preview, .block-editor-inserter__preview-content { background: transparent !important; }
.block-editor-inserter__preview-content-missing { background: rgba(255,255,255,.04) !important; color: #C084FC !important; border-radius: 12px; }
.block-editor-inserter__preview-container .block-editor-block-card__title, .block-editor-inserter__preview-container .block-editor-block-card__name { color: #fff !important; }
.block-editor-inserter__preview-container .block-editor-block-card__description { color: #A9A6C9 !important; }
.block-editor-inserter__preview-container .block-editor-block-icon { color: #C084FC !important; fill: #C084FC !important; }
/* Lateral: sem branco ao passar o mouse */
.interface-complementary-area .components-panel__body-title, .interface-complementary-area .components-panel__body-toggle.components-button { background: transparent !important; border-radius: 17px; }
.interface-complementary-area .components-panel__body.is-opened > .components-panel__body-title, .interface-complementary-area .components-panel__body.is-opened > .components-panel__body-title .components-panel__body-toggle { border-radius: 17px 17px 0 0; }
.interface-complementary-area .components-panel__body-title:hover, .interface-complementary-area .components-panel__body-toggle.components-button:hover, .interface-complementary-area .components-panel__body-toggle.components-button:focus { background: rgba(180,92,240,.18) !important; color: #fff !important; box-shadow: none !important; }
.interface-complementary-area .components-panel__body-toggle.components-button:hover .components-panel__arrow { color: #fff !important; }
.interface-complementary-area .editor-post-card-panel .components-button:hover, .interface-complementary-area .editor-post-panel__row .components-button:hover, .interface-complementary-area .editor-sidebar__panel-tabs [role=tab]:hover { background: rgba(255,255,255,.08) !important; color: #fff !important; }
/* Listas de escolha (Fonte, Tamanho da fonte) */
.components-custom-select-control button[role=combobox], .components-custom-select-control__button { background: rgba(10,9,24,.6) !important; color: #fff !important; border-color: rgba(255,255,255,.2) !important; }
[role=listbox]:has(.components-custom-select-control__item), .components-custom-select-control__menu { background: #14112A !important; border: 1px solid rgba(255,255,255,.14) !important; border-radius: 12px !important; color: #EDEBFF !important; box-shadow: 0 24px 60px rgba(0,0,0,.6) !important; }
.components-custom-select-control__item { color: #EDEBFF !important; font-size: 15px !important; }
.components-custom-select-control__item[aria-selected=true], .components-custom-select-control__item[data-active-item], .components-custom-select-control__item:hover { background: rgba(180,92,240,.35) !important; color: #fff !important; }
.components-custom-select-control__item-hint { color: #A9A6C9 !important; }
/* Barra dos blocos só com texto marcado + painel Formatação */
body.ce-sem-selecao .block-editor-block-list__block-popover:has(.block-editor-block-contextual-toolbar), body.ce-sem-selecao .block-editor-block-contextual-toolbar { display: none !important; }
.ce-fmt-item, .ce-fmt { grid-column: 1 / -1; }
.ce-fmt-tit { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #EDEBFF; margin: 4px 0 8px; }
.ce-fmt-tit small { text-transform: none; letter-spacing: 0; font-weight: 500; color: #8F8BAE; }
.ce-fmt-linha { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
.ce-fmt .components-button.ce-fmt-btn { min-width: 36px; height: 36px; padding: 0 8px; justify-content: center; border-radius: 10px; border: 1px solid rgba(255,255,255,.18) !important; background: rgba(10,9,24,.6) !important; color: #fff !important; font-size: 15px; }
.ce-fmt .components-button.ce-fmt-btn:hover { background: linear-gradient(135deg, rgba(240,33,138,.5), rgba(180,92,240,.5)) !important; border-color: transparent !important; }
/* Uma aba só (Artigo) com o bloco selecionado no topo */
.editor-sidebar__panel-tabs [role=tab]:not([aria-selected=true]) { display: none !important; }
.editor-sidebar__panel-tabs [role=tab][aria-selected=true] { font-size: 0 !important; flex: 1; }
.editor-sidebar__panel-tabs [role=tab][aria-selected=true]::before { content: "Configurações"; font-size: 13.5px; font-weight: 700; font-family: 'Poppins', sans-serif; }
.interface-complementary-area div:has(> .ce-bloco-panel) { display: flex; flex-direction: column; }
.interface-complementary-area div:has(> .ce-bloco-panel) > * { order: 3; }
.interface-complementary-area div:has(> .ce-bloco-panel) > .ce-bloco-panel { order: 0; }
.interface-complementary-area div:has(> .ce-bloco-panel) > :first-child:not(.ce-bloco-panel) { order: 1; }
.interface-complementary-area div:has(> .ce-bloco-panel) > .ce-pronto { order: 2; }
.ce-bloco-panel .block-editor-block-inspector { background: transparent !important; }
.ce-bloco-panel .block-editor-block-inspector .components-panel__body { margin: 10px 0 !important; box-shadow: none; }
.ce-bloco-panel.is-opened > .components-panel__body-title + * { padding: 0; }
/* Varredura: tirar o branco de botões, painel de publicar, links e padrões */
.editor-header .components-button.is-secondary, .editor-header .components-button.is-tertiary, .editor-header a.components-button.is-secondary { background: rgba(255,255,255,.04) !important; color: #EDEBFF !important; box-shadow: inset 0 0 0 1px rgba(255,255,255,.18) !important; border-radius: 12px !important; }
.editor-header .components-button.is-secondary:hover, .editor-header .components-button.is-tertiary:hover { background: rgba(255,255,255,.1) !important; color: #fff !important; }
.block-editor-inserter__category-panel, .block-editor-inserter__patterns-category-panel, .block-editor-inserter__category-panel .block-editor-inserter__patterns-category-panel-header, .block-editor-block-patterns-explorer, .block-editor-block-patterns-explorer__sidebar, .block-editor-block-patterns-explorer__list { background: #14112A !important; border-color: rgba(255,255,255,.08) !important; color: #EDEBFF !important; }
.block-editor-inserter__category-panel .components-heading, .block-editor-inserter__category-panel .components-text, .block-editor-block-patterns-explorer .components-heading { color: #EDEBFF !important; }
.block-editor-link-control, .block-editor-link-control__search-item, .block-editor-link-control__search-item-title, .block-editor-link-control__tools, .block-editor-link-control__settings { color: #EDEBFF !important; background: transparent !important; border-color: rgba(255,255,255,.1) !important; }
.block-editor-link-control__search-item-info, .block-editor-link-control__search-item-details { color: #A9A6C9 !important; }
.block-editor-link-control__search-item.is-selected, .block-editor-link-control__search-item:hover { background: rgba(180,92,240,.2) !important; }
.block-editor-link-control .components-input-control__container, .block-editor-link-control input[type=text], .block-editor-link-control input[type=url], .block-editor-url-input input, .block-editor-url-input__input { background: rgba(10,9,24,.6) !important; color: #fff !important; border-color: rgba(255,255,255,.2) !important; }
.editor-post-publish-panel, .editor-post-publish-panel__header, .editor-post-publish-panel__content, .editor-post-publish-panel__prepublish, .editor-post-publish-panel__footer, .editor-post-publish-panel .components-panel__body, .editor-post-publish-panel .components-panel__body.is-opened, .post-publish-panel__postpublish, .post-publish-panel__postpublish-header { background: #14112A !important; color: #EDEBFF !important; border-color: rgba(255,255,255,.1) !important; }
.editor-post-publish-panel p, .editor-post-publish-panel strong, .editor-post-publish-panel h2, .editor-post-publish-panel label, .editor-post-publish-panel .components-site-name, .editor-post-publish-panel .components-checkbox-control__label, .editor-post-publish-panel .components-panel__body-toggle, .editor-post-publish-panel .components-panel__body-toggle span, .post-publish-panel__postpublish-header a { color: #EDEBFF !important; }
.editor-post-publish-panel .components-site-home, .editor-post-publish-panel .components-site-card span:not(.components-site-name) { color: #A9A6C9 !important; }
.editor-post-publish-panel .editor-post-publish-panel__link, .editor-post-publish-panel a { color: #C084FC !important; }
.editor-post-publish-panel .components-site-card, .editor-post-publish-panel .components-site-icon { background: rgba(255,255,255,.05) !important; border-radius: 12px; }
.editor-post-publish-panel .components-button.is-secondary, .editor-post-publish-panel .editor-post-publish-panel__header-cancel-button .components-button { background: rgba(255,255,255,.05) !important; color: #EDEBFF !important; box-shadow: inset 0 0 0 1px rgba(255,255,255,.18) !important; }
.editor-post-publish-panel .components-panel__body-toggle:hover { background: rgba(180,92,240,.18) !important; }
.editor-post-publish-panel input, .post-publish-panel__postpublish input { background: rgba(10,9,24,.6) !important; color: #fff !important; border-color: rgba(255,255,255,.2) !important; }
.components-form-toggle__input { background: transparent !important; }
.block-editor-warning { background: #FFF6FB !important; }
/* Lista de categorias (Padrões / Mídia) sem branco na opção escolhida */
.block-editor-inserter__category-tablist [role=tab] { color: #CFCDEA !important; border-radius: 10px !important; background: transparent !important; }
.block-editor-inserter__category-tablist [role=tab]:hover, .block-editor-inserter__category-tablist [role=tab][aria-selected=true], .block-editor-inserter__category-tablist [role=tab][data-active-item] { color: #fff !important; }
.block-editor-inserter__category-tablist [role=tab]:hover { background: rgba(255,255,255,.06) !important; }
.block-editor-inserter__category-tablist::before { background: linear-gradient(90deg, rgba(240,33,138,.35), rgba(180,92,240,.35)) !important; border-radius: 10px !important; }
/* Menus suspensos (alinhar, mais, estilos, etc.) com letra clara */
.components-popover .components-dropdown-menu__menu-item, .components-popover .components-menu-item__button, .components-popover [role=menuitem], .components-popover [role=menuitemradio], .components-popover [role=menuitemcheckbox], .components-popover [role=option], .components-popover .components-menu-item__item, .components-popover .components-truncate, .components-popover .block-editor-block-styles__item-text, .components-popover .block-editor-block-switcher__label, .components-popover .components-menu-group__label { color: #CFCDEA !important; }
.components-popover .components-dropdown-menu__menu-item svg, .components-popover .components-menu-item__button svg, .components-popover [role^=menuitem] svg { fill: currentColor !important; color: inherit !important; }
.components-popover .components-dropdown-menu__menu-item:hover, .components-popover .components-dropdown-menu__menu-item:focus, .components-popover .components-dropdown-menu__menu-item.is-active, .components-popover [role^=menuitem]:hover, .components-popover [role^=menuitem]:focus, .components-popover [role^=menuitem][aria-checked=true], .components-popover [role=option]:hover, .components-popover [role=option][aria-selected=true] { background: rgba(180,92,240,.28) !important; color: #fff !important; box-shadow: none !important; outline: none !important; }
.components-popover .components-dropdown-menu__menu-item.is-active svg, .components-popover [role^=menuitem][aria-checked=true] svg { fill: #fff !important; }
.components-popover .components-menu-item__shortcut, .components-popover .components-menu-item__info { color: #8F8BAE !important; }
.components-popover .components-menu-group { border-color: rgba(255,255,255,.1) !important; }
.components-popover__content legend, .components-popover__content label, .components-popover__content h2, .components-popover__content h3, .components-popover__content p, .components-popover__content .components-text, .components-popover__content .components-heading, .components-popover__content .components-base-control__label, .components-popover__content .components-color-palette__custom-color-name, .components-popover__content .block-editor-duotone-control__description { color: #EDEBFF !important; }
.components-popover__content .components-base-control__help, .components-popover__content .components-color-palette__custom-color-value { color: #A9A6C9 !important; }
/* Celular: barra de cima numa linha só e aba Configurações embaixo do texto, como no computador */
@media (max-width: 781px) {
	.editor-header, .edit-post-header { display: flex !important; flex-direction: row !important; flex-wrap: wrap !important; align-items: center !important; justify-content: center !important; overflow: visible !important; min-height: 60px !important; height: auto !important; padding: 8px !important; gap: 4px 6px !important; }
	.interface-interface-skeleton__header { height: auto !important; }
	.editor-header__settings { display: contents !important; }
	.editor-header__toolbar { order: 0 !important; }
	.editor-header .editor-post-publish-button, .editor-header .editor-post-publish-panel__toggle { order: 1 !important; margin-left: auto !important; }
	.editor-header::after { content: ""; order: 2; flex: 0 0 100%; height: 0; }
	.editor-header__settings > * { order: 3; }
	.editor-header .editor-header__post-preview-button, .editor-header .editor-post-preview, .editor-header .editor-preview-dropdown { order: 4 !important; }
	.editor-header .editor-post-save-draft, .editor-header .editor-post-saved-state, .editor-header .editor-header__settings > .components-button.is-tertiary.has-icon { order: 5 !important; width: 40px !important; min-width: 40px !important; height: 40px !important; padding: 0 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; }
	.editor-header .editor-post-save-draft svg, .editor-header .editor-post-saved-state svg, .editor-header .editor-header__settings > .components-button.is-tertiary.has-icon svg { margin: 0 !important; }
	.editor-header .interface-pinned-items { order: 6 !important; }
	.editor-header .editor-header__settings > .components-dropdown-menu { order: 7 !important; }
	.interface-interface-skeleton__sidebar:empty { display: none !important; }
	.editor-header__toolbar, .editor-header__settings, .editor-header .editor-document-tools, .editor-header .editor-document-tools__left { flex: 0 0 auto !important; width: auto !important; max-width: none !important; flex-wrap: nowrap !important; gap: 6px !important; padding: 0 !important; margin: 0 !important; }
	.editor-header .store-setup-button-container { display: none !important; }
	.editor-header .editor-post-publish-button, .editor-header .editor-post-publish-panel__toggle { padding: 0 16px !important; min-width: 0 !important; height: 40px !important; }
	.interface-interface-skeleton__body { display: flex !important; flex-direction: column !important; }
	.interface-interface-skeleton__content { flex: 1 1 45% !important; min-height: 0 !important; }
	.interface-interface-skeleton__sidebar { position: relative !important; inset: auto !important; flex: 0 0 55% !important; height: 55% !important; max-height: 55% !important; width: 100% !important; overflow: auto !important; border-left: 0 !important; border-top: 1px solid rgba(192,132,252,.45) !important; border-radius: 18px 18px 0 0; box-shadow: 0 -18px 40px rgba(0,0,0,.55); }
	.interface-interface-skeleton__sidebar .interface-complementary-area, .interface-interface-skeleton__sidebar .editor-sidebar { width: 100% !important; }
}
/* Painel "Pronto para publicar?" */
.ce-stats { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12.5px; color: #A9A6C9; margin: 2px 0 12px; }
.ce-stats b { color: #fff; }
.ce-check { margin: 0; padding: 0; list-style: none; }
.ce-check li { display: flex; gap: 10px; align-items: center; font-size: 13px; color: #CFCDEA; padding: 5px 0; margin: 0; }
.ce-check li::before { content: "✓"; width: 18px; height: 18px; border-radius: 50%; flex: none; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; color: #0B0A1A; background: #8CE05A; }
.ce-check li.todo { color: #FFB991; }
.ce-check li.todo::before { content: ""; background: transparent; border: 2px solid #FF8A4C; width: 14px; height: 14px; }
/* Barra de formatação dos blocos */
.block-editor-block-contextual-toolbar, .block-editor-block-toolbar, .block-editor-block-contextual-toolbar.is-fixed {
	background: rgba(20,17,42,.96) !important; border: 1px solid rgba(255,255,255,.14) !important; border-radius: 14px !important; box-shadow: 0 20px 50px rgba(0,0,0,.5) !important; overflow: hidden;
}
.block-editor-block-toolbar .components-button, .block-editor-block-contextual-toolbar .components-button { color: #CFCDEA !important; }
.block-editor-block-toolbar .components-button:hover { color: #fff !important; }
.block-editor-block-toolbar .components-button.is-pressed, .block-editor-block-toolbar .components-button.is-pressed:hover { background: rgba(180,92,240,.28) !important; color: #fff !important; }
.block-editor-block-toolbar .components-toolbar-group, .block-editor-block-toolbar .components-toolbar, .block-editor-block-toolbar__block-controls { border-color: rgba(255,255,255,.12) !important; background: transparent !important; }
.block-editor-block-toolbar .block-editor-block-parent-selector { background: transparent; }
/* Janelas, menus e inserção de blocos */
.components-popover__content, .components-dropdown__content .components-popover__content, .block-editor-inserter__popover .components-popover__content {
	background: #14112A !important; color: #EDEBFF; border: 1px solid rgba(255,255,255,.14) !important; border-radius: 16px !important; box-shadow: 0 30px 70px rgba(0,0,0,.7) !important; outline: none !important;
}
.components-popover__content .components-menu-item__button, .components-popover__content .components-button.components-menu-item__button { color: #CFCDEA; }
.components-popover__content .components-menu-item__button:hover { color: #fff; background: rgba(180,92,240,.18); }
.components-popover__content .components-menu-group + .components-menu-group { border-color: rgba(255,255,255,.1); }
.components-popover__content .components-menu-group__label, .block-editor-inserter__panel-title { color: #8F8BAE !important; letter-spacing: .12em; }
.block-editor-inserter__quick-inserter, .block-editor-inserter__menu, .block-editor-inserter__main-area, .block-editor-tabbed-sidebar { background: #14112A !important; }
.block-editor-block-types-list__item { border-radius: 12px !important; border: 1px solid rgba(255,255,255,.08) !important; background: rgba(255,255,255,.04) !important; margin: 4px !important; padding: 12px 6px !important; }
.block-editor-block-types-list__item:hover, .block-editor-block-types-list__item:focus { background: rgba(180,92,240,.2) !important; border-color: rgba(192,132,252,.5) !important; }
.block-editor-block-types-list__item-title { color: #EDEBFF !important; font-weight: 600; }
.components-search-control input[type=search], .block-editor-inserter__search input { background: rgba(255,255,255,.06) !important; color: #fff !important; border-radius: 10px !important; }
.block-editor-inserter__toggle.components-button.has-icon, .editor-document-tools__inserter-toggle.components-button.has-icon {
	background: linear-gradient(135deg, #FF8A4C, #F0218A 55%, #B45CF0) !important; color: #fff !important; border: 0 !important; border-radius: 11px !important; box-shadow: 0 8px 20px -6px rgba(240,33,138,.6);
}
.block-editor-button-block-appender, .block-editor-default-block-appender .block-editor-inserter__toggle { color: #fff !important; }
/* Rodapé de blocos e caixas extras */
.interface-interface-skeleton__footer, .block-editor-block-breadcrumb { background: #0F0D22 !important; border-color: rgba(255,255,255,.08) !important; color: #A9A6C9; }
.block-editor-block-breadcrumb__button { color: #CFCDEA !important; }
.edit-post-layout__metaboxes, .edit-post-meta-boxes-area, .edit-post-meta-boxes-main, .editor-meta-boxes-area, .edit-post-meta-boxes-main__presenter, .edit-post-meta-boxes-main__liner {
	background: #100E24 !important; color: #CFCDEA !important; border-color: rgba(255,255,255,.08) !important;
}
.edit-post-meta-boxes-main__presenter button, .edit-post-meta-boxes-main__presenter { color: #CFCDEA !important; }
.edit-post-meta-boxes-area .postbox, .edit-post-layout__metaboxes .postbox { background: #14112A !important; border-color: rgba(255,255,255,.1) !important; color: #CFCDEA; }
.edit-post-meta-boxes-area .postbox .hndle, .edit-post-meta-boxes-area .postbox h2 { color: #fff !important; }
/* Janela de boas-vindas */
.components-modal__frame { background: #14112A; color: #EDEBFF; border: 1px solid rgba(255,255,255,.14); border-radius: 22px; }
.components-modal__header { background: #14112A; border-color: rgba(255,255,255,.08); }
.components-modal__header-heading, .edit-post-welcome-guide__heading, .components-guide__heading { color: #fff !important; font-family: 'Poppins', sans-serif; }
.components-guide__page, .edit-post-welcome-guide__text, .editor-welcome-guide__text { color: #CFCDEA; }
CSS;
		case 'content':
			return <<<'CSS'
.editor-styles-wrapper {
	background:
		radial-gradient(700px 500px at 0% 0%, rgba(180,92,240,.28), transparent 60%),
		radial-gradient(600px 500px at 100% 100%, rgba(31,193,214,.14), transparent 60%),
		#0B0A1A !important;
	padding: 36px 24px 90px !important;
	min-height: 100vh;
}
.editor-styles-wrapper .edit-post-visual-editor__post-title-wrapper,
.editor-styles-wrapper .editor-visual-editor__post-title-wrapper {
	max-width: 900px; margin: 0 auto !important; padding: 44px 52px 6px !important; box-sizing: border-box;
	background: linear-gradient(160deg, rgba(255,255,255,.08), rgba(255,255,255,.03)); border: 1px solid rgba(255,255,255,.12); border-bottom: 0; border-radius: 26px 26px 0 0;
}
.editor-styles-wrapper .is-root-container.block-editor-block-list__layout,
.editor-styles-wrapper > .is-root-container,
.editor-styles-wrapper .wp-block-post-content.is-root-container {
	max-width: 900px; margin: 0 auto !important; padding: 10px 52px 48px !important; box-sizing: border-box;
	background: linear-gradient(160deg, rgba(255,255,255,.05), rgba(255,255,255,.02)); border: 1px solid rgba(255,255,255,.12); border-top: 0; border-radius: 0 0 26px 26px;
	box-shadow: 0 30px 70px -16px rgba(0,0,0,.6);
}
.editor-styles-wrapper .is-root-container > * { max-width: 100%; }
.editor-styles-wrapper .wp-block-post-title, .editor-styles-wrapper .editor-post-title__input { font-size: 44px !important; line-height: 1.12 !important; letter-spacing: -1.2px !important; margin: 0 !important; }
.editor-styles-wrapper h2 { font-size: 28px; font-weight: 700; margin-top: 1.4em; }
.editor-styles-wrapper h3 { font-size: 22px; font-weight: 700; }
.editor-styles-wrapper p { color: #CFCDEA; }
.editor-styles-wrapper [data-rich-text-placeholder]::after, .editor-styles-wrapper .block-editor-default-block-appender__content { color: #8F8BAE !important; opacity: 1 !important; }
.editor-styles-wrapper .wp-block-post-title [data-rich-text-placeholder]::after { color: rgba(255,255,255,.35) !important; }
.editor-styles-wrapper .block-editor-block-list__block.is-selected:not(.is-root-container)::after,
.editor-styles-wrapper .block-editor-block-list__block.is-highlighted::after { box-shadow: 0 0 0 2px #B45CF0 !important; border-radius: 10px; outline: none !important; }
.editor-styles-wrapper .wp-block-image img, .editor-styles-wrapper .wp-block-cover { border-radius: 18px; }
.editor-styles-wrapper .wp-block-file { padding: 16px 18px; border-radius: 16px; border: 1px dashed rgba(240,33,138,.5); background: rgba(240,33,138,.08); }
.editor-styles-wrapper .wp-block-file .wp-block-file__button { background: linear-gradient(90deg,#FF8A4C,#F0218A,#B45CF0) !important; border-radius: 12px; color: #fff; }
.editor-styles-wrapper table td, .editor-styles-wrapper table th { border-color: rgba(255,255,255,.14); }
/* Folha branca para escrever */
.editor-styles-wrapper .edit-post-visual-editor__post-title-wrapper,
.editor-styles-wrapper .editor-visual-editor__post-title-wrapper {
	background: linear-gradient(90deg, #FF8A4C, #F0218A 40%, #B45CF0 70%, #1FC1D6) top / 100% 6px no-repeat, #FFFFFF !important;
	border-color: rgba(192,132,252,.55) !important;
}
.editor-styles-wrapper .is-root-container.block-editor-block-list__layout,
.editor-styles-wrapper > .is-root-container,
.editor-styles-wrapper .wp-block-post-content.is-root-container {
	background: #FFFFFF !important; border-color: rgba(192,132,252,.55) !important;
	box-shadow: 0 30px 80px -20px rgba(180,92,240,.55), 0 0 0 1px rgba(255,255,255,.04) !important;
	color: #2A2545 !important;
}
.editor-styles-wrapper .wp-block-post-title, .editor-styles-wrapper .editor-post-title__input { color: #1A1433 !important; }
.editor-styles-wrapper .wp-block-post-title [data-rich-text-placeholder]::after { color: #C9C4DE !important; }
.editor-styles-wrapper .is-root-container p, .editor-styles-wrapper .is-root-container li, .editor-styles-wrapper .is-root-container td, .editor-styles-wrapper .is-root-container figcaption { color: #3A3556 !important; }
.editor-styles-wrapper .is-root-container h1, .editor-styles-wrapper .is-root-container h2, .editor-styles-wrapper .is-root-container h3, .editor-styles-wrapper .is-root-container h4, .editor-styles-wrapper .is-root-container h5, .editor-styles-wrapper .is-root-container h6 { color: #1A1433 !important; }
.editor-styles-wrapper .is-root-container a { color: #B4239A !important; }
.editor-styles-wrapper .is-root-container [data-rich-text-placeholder]::after, .editor-styles-wrapper .block-editor-default-block-appender__content { color: #9A95B5 !important; }
.editor-styles-wrapper .is-root-container blockquote { color: #3A3556 !important; border-color: #B45CF0 !important; }
.editor-styles-wrapper .is-root-container table td, .editor-styles-wrapper .is-root-container table th { border-color: #E3DFF0 !important; }
.editor-styles-wrapper .is-root-container hr, .editor-styles-wrapper .is-root-container .wp-block-separator { border-color: #E3DFF0 !important; color: #E3DFF0 !important; }
.editor-styles-wrapper .is-root-container strong, .editor-styles-wrapper .is-root-container b, .editor-styles-wrapper .is-root-container em, .editor-styles-wrapper .is-root-container i, .editor-styles-wrapper .is-root-container u, .editor-styles-wrapper .is-root-container s, .editor-styles-wrapper .is-root-container sup, .editor-styles-wrapper .is-root-container sub, .editor-styles-wrapper .wp-block-post-title strong, .editor-styles-wrapper .wp-block-post-title em { color: inherit !important; }
.editor-styles-wrapper .is-root-container strong, .editor-styles-wrapper .is-root-container b { font-weight: 800; }
.editor-styles-wrapper ::selection { background: rgba(180,92,240,.28); color: inherit; }
.editor-styles-wrapper .ce-justificado, .editor-styles-wrapper .ce-justificado li { text-align: justify !important; }
.editor-styles-wrapper .ce-capa { margin: 0 0 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
.editor-styles-wrapper .ce-capa-add { width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 26px 18px; border: 2px dashed #D3C6F0; border-radius: 18px; background: #FAF7FF; color: #7A3FC4; font-size: 15px; font-weight: 700; cursor: pointer; transition: .15s; }
.editor-styles-wrapper .ce-capa-add:hover { border-color: #B45CF0; background: #F3EAFF; color: #5B23A8; }
.editor-styles-wrapper .ce-capa-load { padding: 26px; text-align: center; color: #9A95B5; border-radius: 18px; background: #F6F4FB; }
.editor-styles-wrapper .ce-capa-img { position: relative; height: 300px; border-radius: 18px; background-size: cover; background-position: center; box-shadow: 0 12px 30px -14px rgba(26,20,51,.45); }
.editor-styles-wrapper .ce-capa-acoes { position: absolute; right: 12px; bottom: 12px; display: flex; gap: 8px; }
.editor-styles-wrapper .ce-capa-acoes button { background: rgba(18,14,40,.82); color: #fff; border: 1px solid rgba(255,255,255,.25); border-radius: 10px; padding: 8px 14px; font-size: 13px; font-weight: 700; cursor: pointer; }
.editor-styles-wrapper .ce-capa-acoes button:hover { background: linear-gradient(90deg,#FF8A4C,#F0218A,#B45CF0); border-color: transparent; }
.editor-styles-wrapper mark.has-inline-color[style*="background-color"]:not([style*=";color"]):not([style*="; color"]):not([style^="color"]) { color: #1A1433 !important; }
@media (max-width: 600px) {
	html .editor-styles-wrapper { padding: 14px 8px 70px !important; }
	html .editor-styles-wrapper .edit-post-visual-editor__post-title-wrapper, html .editor-styles-wrapper .editor-visual-editor__post-title-wrapper { padding: 30px 18px 4px !important; border-radius: 20px 20px 0 0 !important; }
	html .editor-styles-wrapper .is-root-container.block-editor-block-list__layout, html .editor-styles-wrapper > .is-root-container, html .editor-styles-wrapper .wp-block-post-content.is-root-container { padding: 8px 18px 32px !important; border-radius: 0 0 20px 20px !important; }
	html .editor-styles-wrapper .wp-block-post-title, html .editor-styles-wrapper .editor-post-title__input { font-size: 30px !important; letter-spacing: -.6px !important; }
	html .editor-styles-wrapper .ce-capa { margin-bottom: 18px; }
	html .editor-styles-wrapper .ce-capa-add { padding: 18px 12px; font-size: 14px; }
	html .editor-styles-wrapper .ce-capa-img { height: 190px; }
}
CSS;
	}
	return '';
}

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
.login .cf-turnstile, .login .cf-turnstile-div { margin: 16px auto 10px !important; display: flex !important; justify-content: center; width: 100%; }
.login .cf-turnstile iframe { margin: 0 auto !important; }
.login .cf-turnstile-br { display: none; }
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
