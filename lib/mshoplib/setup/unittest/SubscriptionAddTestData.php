<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2021
 */


namespace Aimeos\Upscheme\Task;


/**
 * Adds subscription test data
 */
class SubscriptionAddTestData extends Base
{
	/**
	 * Returns the list of task names which this task depends on.
	 *
	 * @return string[] List of task names
	 */
	public function after() : array
	{
		return ['Subscription', 'OrderAddTestData', 'SubscriptionMigratePeriod', 'SubscriptionMigrateProdcode'];
	}


	/**
	 * Adds subscription test data
	 */
	public function up()
	{
		$this->info( 'Adding subscription test data', 'v' );
		$this->context()->setEditor( 'core:lib/mshoplib' );

		$ds = DIRECTORY_SEPARATOR;
		$path = __DIR__ . $ds . 'data' . $ds . 'subscription.php';

		if( ( $testdata = include( $path ) ) == false ) {
			throw new \RuntimeException( sprintf( 'No file "%1$s" found for subscription domain', $path ) );
		}

		$this->addData( $testdata );
	}


	/**
	 * Adds the required subscription base data.
	 *
	 * @param array $testdata Associative list of key/list pairs
	 */
	protected function addData( array $testdata )
	{
		$subscriptionManager = \Aimeos\MShop\Subscription\Manager\Factory::create( $this->context(), 'Standard' );

		$subscriptionManager->begin();

		foreach( $testdata['subscription'] as $key => $dataset )
		{
			$ordProdItem = $this->getOrderProductItem( $dataset['ordprodid'] );

			$item = $subscriptionManager->create();
			$item->setOrderBaseId( $ordProdItem->getBaseId() );
			$item->setOrderProductId( $ordProdItem->getId() );
			$item->setProductId( $ordProdItem->getProductId() );
			$item->setDateNext( $dataset['datenext'] );
			$item->setDateEnd( $dataset['dateend'] );
			$item->setInterval( $dataset['interval'] );
			$item->setReason( $dataset['reason'] );
			$item->setPeriod( $dataset['period'] );
			$item->setStatus( $dataset['status'] );

			$subscriptionManager->save( $item );
		}

		$subscriptionManager->commit();
	}


	/**
	 * Returns the order product ID for the given test data key
	 *
	 * @param string $key Test data key
	 * @return \Aimeos\MShop\Order\Item\Base\Product\Iface Order product item
	 */
	protected function getOrderProductItem( $key )
	{
		$manager = \Aimeos\MShop\Order\Manager\Factory::create( $this->context(), 'Standard' )
			->getSubManager( 'base', 'Standard' )->getSubManager( 'product', 'Standard' );

		$parts = explode( '/', $key );

		if( count( $parts ) !== 2 ) {
			throw new \RuntimeException( sprintf( 'Invalid order product key "%1$s"', $key ) );
		}

		$search = $manager->filter();
		$expr = [
			$search->compare( '==', 'order.base.product.prodcode', $parts[0] ),
			$search->compare( '==', 'order.base.product.price', $parts[1] ),
		];
		$search->setConditions( $search->and( $expr ) );
		$result = $manager->search( $search );

		if( ( $item = $result->first() ) !== null ) {
			return $item;
		}

		throw new \RuntimeException( sprintf( 'No order product item found for key "%1$s"', $key ) );
	}
}
