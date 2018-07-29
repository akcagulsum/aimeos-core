<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 * @package MShop
 * @subpackage Common
 */


namespace Aimeos\MShop\Common\Manager\Sub;


/**
 * Common trait for creating sub-managers
 *
 * @package MShop
 * @subpackage Common
 */
trait Traits
{
	private $subManagers = [];


	/**
	 * Adds the decorators to the manager object.
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context instance with necessary objects
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object
	 * @param array $decorators List of decorator names that should be wrapped around the manager object
	 * @param string $classprefix Decorator class prefix, e.g. "\Aimeos\MShop\Product\Manager\Decorator\"
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	protected function addDecorators( \Aimeos\MShop\Context\Item\Iface $context,
		\Aimeos\MShop\Common\Manager\Iface $manager, array $decorators, $classprefix )
	{
		$iface = '\\Aimeos\\MShop\\Common\\Manager\\Decorator\\Iface';

		foreach( $decorators as $name )
		{
			if( ctype_alnum( $name ) === false ) {
				throw new \Aimeos\MShop\Exception( sprintf( 'Invalid characters in class name "%1$s"', $name ) );
			}

			$classname = $classprefix . $name;

			if( class_exists( $classname ) === false ) {
				throw new \Aimeos\MShop\Exception( sprintf( 'Class "%1$s" not available', $classname ) );
			}

			$manager = new $classname( $manager, $context );

			if( !( $manager instanceof $iface ) ) {
				throw new \Aimeos\MShop\Exception( sprintf( 'Class "%1$s" does not implement interface "%2$s"', $classname, $iface ) );
			}
		}

		return $manager;
	}


	/**
	 * Adds the configured decorators to the given manager object.
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object
	 * @param string $managerpath Manager sub-names separated by slashes, e.g. "list/type"
	 * @param string $domain Domain name in lower case, e.g. "product"
	 */
	protected function addManagerDecorators( \Aimeos\MShop\Common\Manager\Iface $manager, $managerpath, $domain )
	{
		$config = $this->context->getConfig();

		$decorators = $config->get( 'mshop/common/manager/decorators/default', [] );
		$excludes = $config->get( 'mshop/' . $domain . '/manager/' . $managerpath . '/decorators/excludes', [] );

		foreach( $decorators as $key => $name )
		{
			if( in_array( $name, $excludes ) ) {
				unset( $decorators[$key] );
			}
		}

		$classprefix = '\\Aimeos\\MShop\\Common\\Manager\\Decorator\\';
		$manager = $this->addDecorators( $this->context, $manager, $decorators, $classprefix );

		$classprefix = '\\Aimeos\\MShop\\Common\\Manager\\Decorator\\';
		$decorators = $config->get( 'mshop/' . $domain . '/manager/' . $managerpath . '/decorators/global', [] );
		$manager = $this->addDecorators( $this->context, $manager, $decorators, $classprefix );

		$subpath = $this->createSubNames( $managerpath );
		$classprefix = 'MShop\\' . ucfirst( $domain ) . '\\Manager\\' . $subpath . '\\Decorator\\';
		$decorators = $config->get( 'mshop/' . $domain . '/manager/' . $managerpath . '/decorators/local', [] );

		return $this->addDecorators( $this->context, $manager, $decorators, $classprefix );
	}


	/**
	 * Returns a new manager the given extension name.
	 *
	 * @param string $domain Name of the domain (product, text, media, etc.)
	 * @param string $manager Name of the sub manager type in lower case (can contain a path like base/product)
	 * @param string|null $name Name of the implementation, will be from configuration (or Standard) if null
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager for different extensions
	 */
	protected function getSubManagerBase( $domain, $manager, $name )
	{
		$domain = strtolower( $domain );
		$manager = strtolower( $manager );
		$key = $domain . $manager . $name;

		if( !isset( $this->subManagers[$key] ) )
		{
			if( empty( $domain ) || ctype_alnum( $domain ) === false ) {
				throw new \Aimeos\MShop\Exception( sprintf( 'Invalid characters in domain name "%1$s"', $domain ) );
			}

			if( preg_match( '/^[a-z0-9\/]+$/', $manager ) !== 1 ) {
				throw new \Aimeos\MShop\Exception( sprintf( 'Invalid characters in manager name "%1$s"', $manager ) );
			}

			if( $name === null ) {
				$path = 'mshop/' . $domain . '/manager/' . $manager . '/name';
				$name = $this->context->getConfig()->get( $path, 'Standard' );
			}

			if( empty( $name ) || ctype_alnum( $name ) === false ) {
				throw new \Aimeos\MShop\Exception( sprintf( 'Invalid characters in manager name "%1$s"', $name ) );
			}

			$domainname = ucfirst( $domain );
			$subnames = $this->createSubNames( $manager );

			$classname = '\\Aimeos\\MShop\\' . $domainname . '\\Manager\\' . $subnames . '\\' . $name;
			$interface = '\\Aimeos\\MShop\\' . $domainname . '\\Manager\\' . $subnames . '\\Iface';

			if( class_exists( $classname ) === false ) {
				throw new \Aimeos\MShop\Exception( sprintf( 'Class "%1$s" not available', $classname ) );
			}

			$subManager = new $classname( $this->context );

			if( ( $subManager instanceof $interface ) === false ) {
				throw new \Aimeos\MShop\Exception( sprintf( 'Class "%1$s" does not implement interface "%2$s"', $classname, $interface ) );
			}

			$this->subManagers[$key] = $this->addManagerDecorators( $subManager, $manager, $domain );
		}

		return $this->subManagers[$key];
	}
}