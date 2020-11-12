<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2020
 */


namespace Aimeos\MW\Setup\Task;


/**
 * Adds attribute test data and all items from other domains.
 */
class AttributeListAddTestData extends \Aimeos\MW\Setup\Task\Base
{
	/**
	 * Returns the list of task names which this task depends on.
	 *
	 * @return string[] List of task names
	 */
	public function getPreDependencies() : array
	{
		return ['AttributeAddTestData', 'MediaAddTestData', 'PriceAddTestData', 'TextAddTestData'];
	}


	/**
	 * Adds attribute-list test data.
	 */
	public function migrate()
	{
		\Aimeos\MW\Common\Base::checkClass( \Aimeos\MShop\Context\Item\Iface::class, $this->additional );

		$this->msg( 'Adding attribute-list test data', 0 );
		$this->additional->setEditor( 'core:lib/mshoplib' );

		$ds = DIRECTORY_SEPARATOR;
		$path = __DIR__ . $ds . 'data' . $ds . 'attribute-list.php';

		if( ( $testdata = include( $path ) ) == false ) {
			throw new \Aimeos\MShop\Exception( sprintf( 'No file "%1$s" found for attribute domain', $path ) );
		}

		$refKeys = [];
		foreach( $testdata['attribute/lists'] as $dataset ) {
			$refKeys[$dataset['domain']][] = $dataset['refid'];
		}

		$refIds = [];
		$refIds['media'] = $this->getMediaData( $refKeys['media'] );
		$refIds['price'] = $this->getPriceData( $refKeys['price'] );
		$refIds['text'] = $this->getTextData( $refKeys['text'] );

		$this->addAttributeListData( $testdata, $refIds );

		$this->status( 'done' );
	}


	/**
	 * Gets required media item ids.
	 *
	 * @param array $keys List with referenced Ids
	 * @throws \Aimeos\MW\Setup\Exception If no type ID is found
	 */
	private function getMediaData( array $keys )
	{
		$mediaManager = \Aimeos\MShop\Media\Manager\Factory::create( $this->additional, 'Standard' );

		$urls = [];
		foreach( $keys as $dataset )
		{
			if( ( $pos = strpos( $dataset, '/' ) ) === false || ( $str = substr( $dataset, $pos + 1 ) ) === false ) {
				throw new \Aimeos\MW\Setup\Exception( sprintf( 'Some keys for ref media are set wrong "%1$s"', $dataset ) );
			}

			$urls[] = $str;
		}

		$search = $mediaManager->filter();
		$search->setConditions( $search->compare( '==', 'media.url', $urls ) );

		$refIds = [];
		foreach( $mediaManager->search( $search ) as $item ) {
			$refIds['media/' . $item->getUrl()] = $item->getId();
		}

		return $refIds;
	}


	/**
	 * Gets required text item ids.
	 *
	 * @param array $keys List with referenced Ids
	 * @throws \Aimeos\MW\Setup\Exception If no type ID is found
	 */
	private function getTextData( array $keys )
	{
		$textManager = \Aimeos\MShop\Text\Manager\Factory::create( $this->additional, 'Standard' );

		$labels = [];
		foreach( $keys as $dataset )
		{
			if( ( $pos = strpos( $dataset, '/' ) ) === false || ( $str = substr( $dataset, $pos + 1 ) ) === false ) {
				throw new \Aimeos\MW\Setup\Exception( sprintf( 'Some keys for ref text are set wrong "%1$s"', $dataset ) );
			}

			$labels[] = $str;
		}

		$search = $textManager->filter();
		$search->setConditions( $search->compare( '==', 'text.label', $labels ) );

		$refIds = [];
		foreach( $textManager->search( $search ) as $item ) {
			$refIds['text/' . $item->getLabel()] = $item->getId();
		}

		return $refIds;
	}


	/**
	 * Gets required price item ids.
	 *
	 * @param array $keys List with referenced Ids
	 * @return array $refIds List with referenced Ids
	 * @throws \Aimeos\MW\Setup\Exception If no type ID is found
	 */
	private function getPriceData( array $keys )
	{
		$value = $ship = $domain = $code = [];
		foreach( $keys as $dataset )
		{
			$exp = explode( '/', $dataset );

			if( count( $exp ) != 5 ) {
				throw new \Aimeos\MW\Setup\Exception( sprintf( 'Some keys for ref price are set wrong "%1$s"', $dataset ) );
			}

			$domain[] = $exp[1];
			$code[] = $exp[2];
			$value[] = $exp[3];
			$ship[] = $exp[4];
		}

		return $this->getPriceIds( $value, $ship );
	}


	/**
	 * Gets the attribute test data and adds attribute-list test data.
	 *
	 * @param array $testdata Associative list of key/list pairs
	 * @param array $refIds Associative list of domains and the keys/IDs of the inserted items
	 * @throws \Aimeos\MW\Setup\Exception If a required ID is not available
	 */
	private function addAttributeListData( array $testdata, array $refIds )
	{
		$attributeManager = \Aimeos\MShop\Attribute\Manager\Factory::create( $this->additional, 'Standard' );
		$attributeListManager = $attributeManager->getSubManager( 'lists', 'Standard' );

		$codes = $typeCodes = [];
		foreach( $testdata['attribute/lists'] as $dataset )
		{
			$exp = explode( '/', $dataset['parentid'] );

			if( count( $exp ) != 3 ) {
				throw new \Aimeos\MW\Setup\Exception( sprintf( 'Some keys for parentid are set wrong "%1$s"', $dataset['parentid'] ) );
			}

			$typeCodes[] = $exp[1];
			$codes[] = $exp[2];
		}


		$attributeManager->begin();

		$parentIds = $this->getAttributeIds( $codes, $typeCodes );
		$this->addAttributeListTypeItems( $testdata['attribute/lists/type'] );

		$listItem = $attributeListManager->create();
		foreach( $testdata['attribute/lists'] as $dataset )
		{
			if( !isset( $parentIds[$dataset['parentid']] ) ) {
				throw new \Aimeos\MW\Setup\Exception( sprintf( 'No attribute ID found for "%1$s"', $dataset['parentid'] ) );
			}

			if( !isset( $refIds[$dataset['domain']][$dataset['refid']] ) ) {
				throw new \Aimeos\MW\Setup\Exception( sprintf( 'No "%1$s" ref ID found for "%2$s"', $dataset['refid'], $dataset['domain'] ) );
			}

			$listItem->setId( null );
			$listItem->setParentId( $parentIds[$dataset['parentid']] );
			$listItem->setRefId( $refIds[$dataset['domain']][$dataset['refid']] );
			$listItem->setType( $dataset['type'] );
			$listItem->setDomain( $dataset['domain'] );
			$listItem->setDateStart( $dataset['start'] );
			$listItem->setDateEnd( $dataset['end'] );
			$listItem->setConfig( $dataset['config'] );
			$listItem->setPosition( $dataset['pos'] );
			$listItem->setStatus( $dataset['status'] );

			$attributeListManager->save( $listItem, false );
		}

		$attributeManager->commit();
	}


	/**
	 * Returns the attribute IDs for the given data
	 *
	 * @param array $codes Attribute codes
	 * @param array $typeCodes List of attribute type codes
	 * @return array Associative list of identifiers as keys and attribute IDs as values
	 */
	protected function getAttributeIds( array $codes, array $typeCodes )
	{
		$manager = \Aimeos\MShop\Attribute\Manager\Factory::create( $this->additional, 'Standard' );

		$search = $manager->filter();
		$expr = array(
			$search->compare( '==', 'attribute.code', $codes ),
			$search->compare( '==', 'attribute.type', $typeCodes ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );

		$parentIds = [];
		foreach( $manager->search( $search ) as $item ) {
			$parentIds[$item->getDomain() . '/' . $item->getType() . '/' . $item->getCode()] = $item->getId();
		}

		return $parentIds;
	}


	/**
	 * Adds the attribute list type items
	 *
	 * @param array $data Associative list of identifiers as keys and data sets as values
	 */
	protected function addAttributeListTypeItems( array $data )
	{
		$manager = \Aimeos\MShop\Attribute\Manager\Factory::create( $this->additional, 'Standard' );
		$listManager = $manager->getSubManager( 'lists', 'Standard' );
		$listTypeManager = $listManager->getSubManager( 'type', 'Standard' );

		$listItemType = $listTypeManager->create();

		foreach( $data as $key => $dataset )
		{
			$listItemType->setId( null );
			$listItemType->setCode( $dataset['code'] );
			$listItemType->setDomain( $dataset['domain'] );
			$listItemType->setLabel( $dataset['label'] );
			$listItemType->setStatus( $dataset['status'] );

			$listTypeManager->save( $listItemType );
		}
	}


	/**
	 * Returns the price IDs for the given data
	 *
	 * @param array $value Price values
	 * @param array $ship Price shipping costs
	 * @param array Associative list of identifiers as keys and price IDs as values
	 */
	protected function getPriceIds( array $value, array $ship )
	{
		$manager = \Aimeos\MShop\Price\Manager\Factory::create( $this->additional, 'Standard' );

		$search = $manager->filter();
		$expr = array(
			$search->compare( '==', 'price.value', $value ),
			$search->compare( '==', 'price.costs', $ship ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );

		$parentIds = [];
		foreach( $manager->search( $search ) as $item ) {
			$parentIds['price/' . $item->getDomain() . '/' . $item->getType() . '/' . $item->getValue() . '/' . $item->getCosts()] = $item->getId();
		}

		return $parentIds;
	}
}
