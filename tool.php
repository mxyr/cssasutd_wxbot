<?php

function find_target( $group, $groups ) {
	foreach ( $groups as $k => $wxid ) {
		if ( $group == $wxid ) {
			unset( $groups[ $k ] );
		}
	}
	return ( $groups );
}

function find_url( $msg ) {
	$pattern = '/<url>(.*?)<\/url>/';
	preg_match( $pattern, $msg, $match );
	return ( $match[ 1 ] );
}

function find_msg( $msg ) {
	$pattern = '/<title>(.*?)<\/title>/';
	preg_match( $pattern, $msg, $match );
	return ( $match[ 1 ] );
}

function find_name( $msg ) {
	$pattern = '/<displayname>(.*?)<\/displayname>/';
	preg_match( $pattern, $msg, $match );
	return ( $match[ 1 ] );
}

function find_content( $msg ) {
	$pattern = '/<content>(.*?)<\/content>/';
	preg_match( $pattern, $msg, $match );
	return ( $match[ 1 ] );
}