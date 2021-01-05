<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
 * @package MW
 * @subpackage View
 */


namespace Aimeos\MW\View\Engine;


/**
 * Common interface for all view engine classes
 *
 * @package MW
 * @subpackage View
 */
interface Iface
{
	/**
	 * Renders the output based on the given template file name and the key/value pairs
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param string $filename File name of the view template
	 * @param array $values Associative list of key/value pairs
	 * @return string|null Output generated by the template or null for none
	 * @throws \Aimeos\MW\View\Exception If the template couldn't be rendered
	 */
	public function render( \Aimeos\MW\View\Iface $view, string $filename, array $values ) : ?string;
}
