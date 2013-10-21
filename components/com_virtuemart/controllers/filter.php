<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * VirtueMart Component Controller
 *
 * @package		Filter
 */
class VirtueMartControllerFilter extends JController
{

	function __construct() {
		parent::__construct();
		if (VmConfig::get('shop_is_offline') == '1') {
		    JRequest::setVar( 'layout', 'off_line' );
	    }
	    else {
		    JRequest::setVar( 'layout', 'default' );
	    }
	}

	function filter() {

		$view = $this->getView(JRequest::getWord('view', 'filter'), 'html');

		// Display it all
		$safeurlparams = array('virtuemart_category_id'=>'INT','virtuemart_currency_id'=>'INT','return'=>'BASE64','lang'=>'CMD');
		parent::display(true, $safeurlparams);//$view->display();
	}
	public function test() {
		$productModel = VmModel::getModel('Product');
		$Product_group 	= 'random';
		$max_items		= 36;
		$show_price		= true;
		$filter_category = true;
		$category_id	= 0;
		
		$products = $productModel->getProductListing($Product_group, $max_items, $show_price, true, false,$filter_category, $category_id);
		//$productlist = $productModel->getProductListing(false,false,false,false,true);
		$productModel->addImages($products);
		JFile::write(JPATH_ROOT.DS.'products.log', print_r($products,1));
		echo json_encode($products);
	}
}
 //pure php no closing tag
