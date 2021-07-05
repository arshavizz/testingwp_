<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

function pp_bootstrap() {
	if ( ! is_callable( '\ScssPhp\ScssPhp\Compiler' ) ) {
		require_once POWERPACK_ELEMENTS_PATH . 'scss-compiler/scss.inc.php';
	}
}

function pp_scss_test() {
	try {
		pp_bootstrap();
		$scss = new \ScssPhp\ScssPhp\Compiler();
		if ( is_wp_error( $scss ) ) {
			throw new Exception( $scss->get_error_message() );
		} else {
			return 'success';
		}
	} catch( Exception $e ) {
		return $e->get_message();
	}
}

function pp_scss_compile( $widgets_info = array(), $widgets_compile = false ) {
	pp_bootstrap();

	$scss = new \ScssPhp\ScssPhp\Compiler();
	$scss->setImportPaths( POWERPACK_ELEMENTS_PATH . 'assets/scss/');

	// frontend.scss
	try {
		$content = @file_get_contents( POWERPACK_ELEMENTS_PATH . 'assets/scss/frontend.scss' );
		$compiled = $scss->compile( $content );
		if ( ! empty( $compiled ) ) {
			@file_put_contents( POWERPACK_ELEMENTS_PATH . 'assets/css/frontend.css', $compiled );
		}
	} catch( Exception $e ) {
		@error_log( 'PPE SCSS Compile Error: ' . $e->getMessage() );
	}

	if ( ! $widgets_compile ) {
		return;
	}

	$scss_includes = @file_get_contents( POWERPACK_ELEMENTS_PATH . 'assets/scss/_includes.scss' );

	foreach ( $widgets_info as $widget ) {
		$dir = $widget['dir'];
		$widget_files = glob( $dir . '/widgets/*' );
		$scss_files = array();
		if ( is_array( $widget_files ) && ! empty( $widget_files ) ) {
			$scss_files = array_map( function( $file ) {
				return str_replace( '.php', '', basename( $file ) );
			}, $widget_files );
		}

		if ( empty( $scss_files ) ) {
			continue;
		}
		foreach ( $scss_files as $file ) {
			// $path = $widget['dir'] . 'js/' . $file . '.js';
			// if ( is_dir( $path ) ) {
			// 	rmdir( $path );
			// }
			// if ( ! file_exists( $path ) ) {
			// 	$content = ';(function($) {' . "\n";
			// 	$content .= '})(jQuery);' . "\n";
			// 	@file_put_contents( $path, $content );
			// }
			// continue;
			$content = @file_get_contents( POWERPACK_ELEMENTS_PATH . 'assets/scss/widgets/' . $file . '/_' . $file . '.scss' );
			try {
				$compiled = $scss->compile( $scss_includes . $content );
				if ( ! empty( $compiled ) ) {
					$path = $widget['dir'] . 'css/';
					if ( ! file_exists( $path ) ) {
						mkdir( $path );
					}
					@file_put_contents( $path . $file . '.css', $compiled );
				}
			} catch( Exception $e ) {
				@error_log( 'PPE SCSS Compile Error: ' . $file . "\r\n" . $e->getMessage() );
			}
		}
	}
}