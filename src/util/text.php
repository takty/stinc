<?php
namespace st;
/**
 *
 * Text Processing Functions
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-06-24
 *
 */


function mb_trim( $str ) {
	return preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $str );
}

function remove_continuous_spaces( $str ) {
	$str = preg_replace( '/　/', ' ', $str );
	$str = preg_replace( '/\s+/', ' ', $str );
	return $str;
}


// -----------------------------------------------------------------------------


function separate_line( $str, $mode = 'raw', $filter = 'esc_html' ) {
	$ls = preg_split( "/　　|<\s*br\s*\/?>/ui", $str );
	switch ( $mode ) {
		case 'raw':
			return $ls;
		case 'br':
			$_ls = array_map( 'esc_html', $ls );
			return implode( '<br>', $_ls );
		case 'span':
			$_ls = array_map( $filter, $ls );
			return '<span>' . implode( '</span><span>', $_ls ) . '</span>';
		case 'div':
			$_ls = array_map( $filter, $ls );
			return '<div>' . implode( '</div><div>', $_ls ) . '</div>';
		case 'segment':
			$_ls = array_map( function ( $s ) use ( $filter ) { return \st\separate_text_and_make_spans( $s, $filter ); }, $ls );
			return '<div>' . implode( '</div><div>', $_ls ) . '</div>';
		case 'segment_raw':
			return array_map( function ( $s ) use ( $filter ) { return \st\separate_text_and_make_spans( $s, $filter ); }, $ls );
		case 'segment_small':
			$sss = array_map( '\st\separate_small', $ls );
			$newLs = [];
			foreach ( $sss as $ss ) {
				$newL = '';
				foreach ( $ss as $s ) {
					$temp = separate_text_and_make_spans( $s[0], $filter );
					if ( ! empty( $s[1] ) ) $temp = "<{$s[1]}>$temp</{$s[1]}>";
					$newL .= $temp;
				}
				$newLs[] = $newL;
			}
			return '<div>' . implode( '</div><div>', $newLs ) . '</div>';
		case 'segment_small_simple':
			$sss = array_map( '\st\separate_small', $ls );
			$newLs = [];
			foreach ( $sss as $ss ) {
				$newL = '';
				foreach ( $ss as $s ) $newL .= $s[0];
				$newLs[] = $newL;
			}
			return '<div>' . implode( '</div><div>', $newLs ) . '</div>';
	}
}

function esc_text_with_br( $str, $filter = 'esc_html' ) {
	$ls = preg_split( "/<\s*br\s*\/?>/iu", $str );
	if ( count( $ls ) === 1 ) {
		return call_user_func( $filter, $str );
	}
	return implode( '<br>', array_map( $filter, $ls ) );
}

function esc_text_with_br_to_span( $str, $filter = 'esc_html' ) {
	$ls = preg_split( "/<\s*br\s*\/?>/iu", $str );
	if ( count( $ls ) === 1 ) {
		return call_user_func( $filter, $str );
	}
	return '<span>' . implode( '</span><span>', array_map( $filter, $ls ) ) . '</span>';
}

function separate_small( $str ) {
	$ls = preg_split( "/(<small>[\s\S]*?<\/small>)/iu", $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
	$ss = [];
	foreach ( $ls as $l ) {
		preg_match( "/<small>([\s\S]*?)<\/small>/iu", $l, $matches );
		if ( empty( $matches ) ) {
			$ss[] = [ $l, '' ];
		} else {
			$ss[] = [ $matches[1], 'small' ];
		}
	}
	return $ss;
}


// -----------------------------------------------------------------------------


function separate_text_and_make_spans( $text, $filter = 'esc_html' ) {
	$parts = separate_text( $text );
	$ret = '';
	foreach ( $parts as $ws ) {
		$_w = $filter ? call_user_func( $filter, $ws[0] ) : $ws[0];
		$ret .= $ws[1] ? "<span>$_w</span>" : $_w;
	}
	return $ret;
}

function separate_text( $text ) {
	$PAIRS = ['S*' => 1, '*E' => 1, 'II' => 1, 'KK' => 1, 'HH' => 1, 'HI' => 1];
	$parts = [];
	$t_prev = '';
	$word = '';

	for ( $i = 0, $I = mb_strlen( $text ); $i < $I; $i += 1 ) {
		$c = mb_substr( $text, $i, 1 );
		$t = _get_ctype( $c );
		if ( isset( $PAIRS[ $t_prev . $t ] ) || isset( $PAIRS[ '*' . $t ] ) || isset( $PAIRS[ $t_prev . '*' ] ) ) {
			$word .= $c;
		} else if ( $t === 'O' ) {
			if ( $t_prev === 'O' ) {
				$word .= $c;
			} else {
				if ( ! empty( $word ) ) $parts[] = [ $word, true ];
				$word = $c;
			}
		} else {
			if ( ! empty( $word ) ) $parts[] = [ $word, ( $t_prev !== 'O' ) ];
			$word = $c;
		}
		$t_prev = $t;
	}
	if ( ! empty( $word ) ) $parts[] = [ $word, ( $t_prev !== 'O' ) ];
	return $parts;
}

function _get_ctype( $c ) {
	$CPATS = [
		'S' => '/[「『（［｛〈《【〔〖〘〚＜]/u',
		'E' => '/[」』）］｝〉》】〕〗〙〛＞、，。．？！を：]/u',
		'I' => '/[ぁ-んゝ]/u',
		'K' => '/[ァ-ヴーｱ-ﾝﾞｰ]/u',
		'H' => '/[一-龠々〆ヵヶ]/u',
	];
	foreach ( $CPATS as $t => $p ) {
		if ( preg_match( $p, $c ) === 1 ) return $t;
	}
	return 'O';
}
