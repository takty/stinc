<?php
namespace st;

/**
 *
 * Utilities
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-07
 *
 */


function get_src_uri( $dir, $src ) {
	#パスの区切りをスラッシュに統一する(windows対策)
	$tfp = wp_normalize_path( get_theme_file_path() );#テーマファイルが存在するディレクトリの絶対パス
	$tfu = get_theme_file_uri(); 					#テーマファイルが存在するディレクトリのuri
	$dir_m = wp_normalize_path( $dir );
	$src_m = wp_normalize_path( $src );

	#テーマファイルが存在するディレクトリの絶対パスをuriに置き換える
	$replace = str_replace( $tfp, $tfu, $dir_m );
	#現ディレクトリのuriの最後にスラッシュを挿入する
	$uri_base = $replace . '/';

	#もし、入力引数の最初にスラッシュがある場合、二重になるのを避けるために、取り除く
	$src_rem = ltrim( $src_m, '/' );

	#現ディレクトリのuriとソースファイルの相対パスを合わせる
	return $uri_base . $src_rem;
}
