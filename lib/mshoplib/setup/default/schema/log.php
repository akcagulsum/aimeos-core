<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2021
 */


return array(
	'table' => array(
		'madmin_log' => function( \Aimeos\Upscheme\Schema\Table $table ) {

			$table->engine = 'InnoDB';

			$table->bigid()->primary( 'pk_mslog_id' );
			$table->string( 'siteid' )->default( '' );
			$table->string( 'facility', 32 );
			$table->datetime( 'timestamp' );
			$table->smallint( 'priority' );
			$table->text( 'message', 0x1ffff );
			$table->string( 'request', 32 );

			$table->index( ['siteid', 'timestamp', 'facility', 'priority'], 'idx_malog_sid_time_facility_prio' );
		},
	),
);
