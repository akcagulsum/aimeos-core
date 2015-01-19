<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2013
 * @license LGPLv3, http://www.arcavias.com/en/license
 * @package Client
 * @subpackage Html
 */


/**
 * Default implementation of catalog stock HTML clients.
 *
 * @package Client
 * @subpackage Html
 */
class Client_Html_Catalog_Stock_Default
	extends Client_Html_Abstract
{
	/** client/html/catalog/stock/default/subparts
	 * List of HTML sub-clients rendered within the catalog stock section
	 *
	 * The output of the frontend is composed of the code generated by the HTML
	 * clients. Each HTML client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain HTML clients themselves and therefore a
	 * hierarchical tree of HTML clients is composed. Each HTML client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the HTML code generated by the parent is printed, then
	 * the HTML code of its sub-clients. The order of the HTML sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural HTML, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2014.03
	 * @category Developer
	 */
	private $_subPartPath = 'client/html/catalog/stock/default/subparts';
	private $_subPartNames = array();
	private $_cache;


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return string HTML code
	 */
	public function getBody( $uid = '', array &$tags = array(), &$expire = null )
	{
		try
		{
			$view = $this->_setViewParams( $this->getView(), $tags, $expire );

			$html = '';
			foreach( $this->_getSubClients() as $subclient ) {
				$html .= $subclient->setView( $view )->getBody( $uid, $tags, $expire );
			}
			$view->stockBody = $html;

			/** client/html/catalog/stock/default/template-body
			 * Relative path to the HTML body template of the catalog stock client.
			 *
			 * The template file contains the HTML code and processing instructions
			 * to generate the result shown in the body of the frontend. The
			 * configuration string is the path to the template file relative
			 * to the layouts directory (usually in client/html/layouts).
			 *
			 * You can overwrite the template file configuration in extensions and
			 * provide alternative templates. These alternative templates should be
			 * named like the default one but with the string "default" replaced by
			 * an unique name. You may use the name of your project for this. If
			 * you've implemented an alternative client class as well, "default"
			 * should be replaced by the name of the new class.
			 *
			 * @param string Relative path to the template creating code for the HTML page body
			 * @since 2014.03
			 * @category Developer
			 * @see client/html/catalog/stock/default/template-header
			 */
			$tplconf = 'client/html/catalog/stock/default/template-body';
			$default = 'catalog/stock/body-default.html';

			return $view->render( $this->_getTemplate( $tplconf, $default ) );
		}
		catch( Exception $e )
		{
			$this->_getContext()->getLogger()->log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
		}
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return string String including HTML tags for the header
	 */
	public function getHeader( $uid = '', array &$tags = array(), &$expire = null )
	{
		try
		{
			$view = $this->_setViewParams( $this->getView(), $tags, $expire );

			$html = '';
			foreach( $this->_getSubClients() as $subclient ) {
				$html .= $subclient->setView( $view )->getHeader( $uid, $tags, $expire );
			}
			$view->stockHeader = $html;

			/** client/html/catalog/stock/default/template-header
			 * Relative path to the HTML header template of the catalog stock client.
			 *
			 * The template file contains the HTML code and processing instructions
			 * to generate the HTML code that is inserted into the HTML page header
			 * of the rendered page in the frontend. The configuration string is the
			 * path to the template file relative to the layouts directory (usually
			 * in client/html/layouts).
			 *
			 * You can overwrite the template file configuration in extensions and
			 * provide alternative templates. These alternative templates should be
			 * named like the default one but with the string "default" replaced by
			 * an unique name. You may use the name of your project for this. If
			 * you've implemented an alternative client class as well, "default"
			 * should be replaced by the name of the new class.
			 *
			 * @param string Relative path to the template creating code for the HTML page head
			 * @since 2014.03
			 * @category Developer
			 * @see client/html/catalog/stock/default/template-body
			 */
			$tplconf = 'client/html/catalog/stock/default/template-header';
			$default = 'catalog/stock/header-default.html';

			return $view->render( $this->_getTemplate( $tplconf, $default ) );
		}
		catch( Exception $e )
		{
			$this->_getContext()->getLogger()->log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
		}
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return Client_Html_Interface Sub-client object
	 */
	public function getSubClient( $type, $name = null )
	{
		return $this->_createSubClient( 'catalog/stock/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables.
	 */
	public function process()
	{
		try
		{
			parent::process();
		}
		catch( Exception $e )
		{
			$this->_getContext()->getLogger()->log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
		}
	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param MW_View_Interface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return MW_View_Interface Modified view object
	 */
	protected function _setViewParams( MW_View_Interface $view, array &$tags = array(), &$expire = null )
	{
		if( !isset( $this->_cache ) )
		{
			$context = $this->_getContext();
			$siteConfig = $context->getLocale()->getSite()->getConfig();

			/** client/html/catalog/stock/sort
			 * Sortation key if stock levels for different warehouses exist
			 *
			 * Products can be shipped from several warehouses with a different
			 * stock level for each one. The stock levels for each warehouse will
			 * be shown in the product detail page. To get a consistent sortation
			 * of this list, the configured key of the product warehouse manager
			 * will be used.
			 *
			 * @param string Key for sorting
			 * @since 2014.03
			 * @category Developer
			 * @see client/html/catalog/stock/low
			 */
			$sortkey = $context->getConfig()->get( 'client/html/catalog/stock/sort', 'product.stock.warehouseid' );
			$productIds = $view->param( 's_prodid' );

			if( !is_array( $productIds ) ) {
				$productIds = explode( ' ', $productIds );
			}


			$stockManager = MShop_Factory::createManager( $context, 'product/stock' );

			$search = $stockManager->createSearch( true );
			$expr = array( $search->compare( '==', 'product.stock.productid', $productIds ) );

			if( isset( $siteConfig['warehouse'] ) ) {
				$expr[] = $search->compare( '==', 'product.stock.warehouse.code', $siteConfig['warehouse'] );
			}

			$expr[] = $search->getConditions();

			$sortations = array(
				$search->sort( '+', 'product.stock.productid' ),
				$search->sort( '+', $sortkey ),
			);

			$search->setConditions( $search->combine( '&&', $expr ) );
			$search->setSortations( $sortations );
			$search->setSlice( 0, 0x7fffffff );

			$stockItems = $stockManager->searchItems( $search );


			if( !empty( $stockItems ) )
			{
				$warehouseIds = $stockItemsByProducts = array();

				foreach( $stockItems as $item )
				{
					$warehouseIds[ $item->getWarehouseId() ] = null;
					$stockItemsByProducts[ $item->getProductId() ][] = $item;
				}

				$warehouseIds = array_keys( $warehouseIds );


				$warehouseManager = MShop_Factory::createManager( $context, 'product/stock/warehouse' );

				$search = $warehouseManager->createSearch();
				$search->setConditions( $search->compare( '==', 'product.stock.warehouse.id', $warehouseIds ) );
				$search->setSlice( 0, count( $warehouseIds ) );


				$view->stockWarehouseItems = $warehouseManager->searchItems( $search );
				$view->stockItemsByProducts = $stockItemsByProducts;
			}


			$view->stockProductIds = $productIds;

			$this->_cache = $view;
		}

		return $this->_cache;
	}
}
