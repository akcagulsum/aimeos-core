<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2021
 */


return array(
	'table' => array(

		'mshop_supplier' => function( \Aimeos\Upscheme\Schema\Table $table ) {

			$table->engine = 'InnoDB';

			$table->id()->primary( 'pk_mssup_id' );
			$table->string( 'siteid' );
			$table->code();
			$table->string( 'label' );
			$table->smallint( 'status' );
			$table->meta();

			$table->unique( ['siteid', 'code'], 'unq_mssup_sid_code' );
			$table->index( ['siteid', 'status'], 'idx_mssup_sid_status' );
			$table->index( ['siteid', 'label'], 'idx_mssup_sid_label' );
		},

		'mshop_supplier_address' => function( \Aimeos\Upscheme\Schema\Table $table ) {

			$table->engine = 'InnoDB';

			$table->id()->primary( 'pk_mssupad_id' );
			$table->string( 'siteid' );
			$table->int( 'parentid' );
			$table->string( 'company', 100 );
			$table->string( 'vatid', 32 );
			$table->string( 'salutation', 8 );
			$table->string( 'title', 64 );
			$table->string( 'firstname', 64 );
			$table->string( 'lastname', 64 );
			$table->string( 'address1', 200 );
			$table->string( 'address2', 200 );
			$table->string( 'address3', 200 );
			$table->string( 'postal', 16 );
			$table->string( 'city', 200 );
			$table->string( 'state', 200 );
			$table->string( 'langid', 5 )->null( true );
			$table->string( 'countryid', 2 )->null( true );
			$table->string( 'telephone', 32 );
			$table->string( 'telefax', 32 );
			$table->string( 'email' );
			$table->string( 'website' );
			$table->float( 'longitude' )->null( true );
			$table->float( 'latitude' )->null( true );
			$table->date( 'birthday' )->null( true );
			$table->smallint( 'pos' );
			$table->meta();

			$table->index( ['siteid', 'parentid'], 'idx_mssupad_sid_rid' );
			$table->index( ['parentid'], 'fk_mssupad_pid' );

			$table->foreign( 'parentid', 'mshop_supplier', 'id', 'fk_mssupad_pid' );
		},

		'mshop_supplier_list_type' => function( \Aimeos\Upscheme\Schema\Table $table ) {

			$table->engine = 'InnoDB';

			$table->id()->primary( 'pk_mssuplity_id' );
			$table->string( 'siteid' );
			$table->string( 'domain', 32 );
			$table->code();
			$table->string( 'label' );
			$table->int( 'pos' )->default( 0 );
			$table->smallint( 'status' );
			$table->meta();

			$table->unique( ['siteid', 'domain', 'code'], 'unq_mssuplity_sid_dom_code' );
			$table->index( ['siteid', 'status', 'pos'], 'idx_mssuplity_sid_status_pos' );
			$table->index( ['siteid', 'label'], 'idx_mssuplity_sid_label' );
			$table->index( ['siteid', 'code'], 'idx_mssuplity_sid_code' );
		},

		'mshop_supplier_list' => function( \Aimeos\Upscheme\Schema\Table $table ) {

			$table->engine = 'InnoDB';

			$table->id()->primary( 'pk_mssupli_id' );
			$table->string( 'siteid' );
			$table->int( 'parentid' );
			$table->string( 'key', 134 )->default( '' );
			$table->type();
			$table->string( 'domain', 32 );
			$table->refid();
			$table->startend();
			$table->text( 'config' );
			$table->int( 'pos' );
			$table->smallint( 'status' );
			$table->meta();

			$table->unique( ['parentid', 'domain', 'siteid', 'type', 'refid'], 'unq_mssupli_pid_dm_sid_ty_rid' );
			$table->index( ['parentid', 'domain', 'siteid', 'pos', 'refid'], 'idx_mssupli_pid_dm_sid_pos_rid' );
			$table->index( ['refid', 'domain', 'siteid', 'type'], 'idx_mssupli_rid_dom_sid_ty' );
			$table->index( ['key', 'siteid'], 'idx_mssupli_key_sid' );
			$table->index( ['parentid'], 'fk_mssupli_pid' );

			$table->foreign( 'parentid', 'mshop_supplier', 'id', 'fk_mssupli_pid' );
		},
	),
);
