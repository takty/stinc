<?php
namespace st\retrop;

/**
 *
 * Utilities for Retrop
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-24
 *
 */


const FS_TYPE       = 'type';
const FS_KEY        = 'key';
const FS_TAXONOMY   = 'taxonomy';
const FS_FILTER     = 'filter';
const FS_AUTO_ADD   = 'auto_add';
const FS_REQUIRED   = 'required';
const FS_FOR_DIGEST = 'for_digest';

const FS_TYPE_TITLE   = 'post_title';
const FS_TYPE_CONTENT = 'post_content';
const FS_TYPE_META    = 'post_meta';
const FS_TYPE_TERM    = 'term';

const FS_FILTER_CONTENT   = 'post_content';
const FS_FILTER_NORM_DATE = 'norm_date';


function make_digest( $text ) {
	$text = normalize_key_text( $text );
	$text = str_replace( ' ', '', $text );
	return hash( 'sha224', $text );
}

function normalize_key_text( $text ) {
	$text = strip_tags( trim( $text ) );
	$text = mb_convert_kana( $text, 'rnasKV' );
	$text = mb_strtolower( $text );
	$text = preg_replace( '/[\s!-\/:-@[-`{-~]|[、。，．・：；？！´｀¨＾￣＿―‐／＼～∥｜…‥‘’“”（）〔〕［］｛｝〈〉《》「」『』【】＊※]/u', ' ', $text );
	$text = preg_replace( '/\s(?=\s)/', '', $text );
	$text = trim( $text );
	return $text;
}
