<?php

function find_target( $group, $groups ) {
	foreach ( $groups as $k => $wxid ) {
		if ( $group == $wxid ) {
			unset( $groups[ $k ] );
		}
	}
	return ( $groups );
}