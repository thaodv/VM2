<?php

# Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

# Load the view framework
if(!class_exists('VmView'))require(JPATH_VM_SITE.DS.'helpers'.DS.'vmview.php');

/**
 * Default HTML View class for the VirtueMart Component
 * @todo Find out how to use the front-end models instead of the backend models
 */
class VirtueMartViewFilter extends VmView {

	public function display($tpl = null){
	
		$this->addHelperPath(JPATH_VM_ADMINISTRATOR.DS.'helpers');
		require_once(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php'); //dont remove that file it is actually in every view
		$show_prices  = VmConfig::get('show_prices',1);
		if($show_prices == '1'){
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		}
	
		/*
		 * get the category
		* This only needs in order to avoid undefined variables
		*/
		if (!class_exists('VirtueMartModelCategory')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'category.php');
		$vendorId = 1;
	
		$jinput=JFactory::getApplication()->input;
		$categories=$jinput->get('virtuemart_category_id',array(),'array');
	
		/*If there is only one category selected and is not zero, display children categories*/
		if(count($categories)==1 && $categories[0]>0){
			$categoryId=$categories[0];
			$category_haschildren=true;
		}else{
			$categoryId=0;
			$category_haschildren=false;
		}
	
		$categoryModel = VmModel::getModel('category');
		$category = $categoryModel->getCategory($categoryId);
		$category->haschildren=$category_haschildren;
		if($category_haschildren){
			$category->children = $categoryModel->getChildCategoryList($vendorId, $categoryId);
			$categoryModel->addImages($category->children);
		}
		$perRow = empty($category->products_per_row)? VmConfig::get('products_per_row',3):$category->products_per_row;
		// 		$categoryModel->setPerRow($perRow);
		$this->assignRef('perRow', $perRow);	
		/*
		 * show base price variables
		*/
		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		$showBasePrice = Permissions::getInstance()->check('admin'); //todo add config settings
	
	
		/*
		 * get the products from the cf model
		*/
		$model= VmModel::getModel('filter');
		$products	= $model->getProductListing();
		$model->addImages($products);
		//add stock
		foreach($products as $product){
			$product->stock = $model->getStockIndicator($product);
		}
	
		//currency
		if ($products) {
			if(!class_exists('CurrencyDisplay'))require_once(JPATH_VM_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'currencydisplay.php');
			$currency = CurrencyDisplay::getInstance( );
			$this->assignRef('currency', $currency);
		}
	
		//rating
		if(method_exists('VmModel','getModel')){
			$ratingModel = VmModel::getModel('ratings');
			$showRating = $ratingModel->showRating();
		}
			
		//Pagination
		$u=JFactory::getURI();
		$query=$u->getQuery();
		//$paginationAction=JRoute::_(JURI::base().'index.php?virtuemart_category_id[0]=3&virtuemart_category_id[1]=1&virtuemart_category_id[2]=5&option=com_virtuemart&view=products');
		$pagination = $model->getPagination(true);
	
		/*
		 * Get the order by list
		*/
		$orderByList = $this->get('OrderByList');
		$search=null;
		$this->assignRef('show_prices', $show_prices);
		$this->assignRef('orderByList', $orderByList);
		$this->assignRef('products', $products);
		$this->assignRef('category', $category);
		$this->assignRef('showBasePrice', $showBasePrice);
		$this->assignRef('show_prices', $show_prices);
		$this->assignRef('vmPagination', $pagination);
		$this->assignRef('paginationAction', $paginationAction);
		$this->assignRef('perRow', $perRow);
		$this->assignRef('search', $search);
		$this->assignRef('showRating', $showRating);
	
		$template = VmConfig::get('vmtemplate','default');
	
		if (is_dir(JPATH_THEMES.DS.$template)) {
			$mainframe = JFactory::getApplication();
			$mainframe->set('setTemplate', $template);
		}
		//$this->setLayoutExt('com_virtuemart');
		$this->_prepareDocument();
		parent::display($tpl);
	}
	
	
	/**
	* Prepares the document
	 */
	 protected function _prepareDocument()
	 {
	
	 //JHTML::stylesheet('vmsite-ltr.css',JURI::base().'components/com_virtuemart/assets/css/');
	 	JHTML::script('jquery.min.js','http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/');
		JHTML::script('jquery-ui.min.js','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/');
	
		//use the vm functions for loading scripts and css
	 	if (!class_exists( 'VmConfig' )) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'config.php');
		VmConfig::loadConfig();
	 	vmJsApi::cssSite();
	 	vmJsApi::jSite();
	 	
	 	vmJsApi::css('filter');
	
	 	//layout
	 	$this->_setPath('template',(JPATH_BASE.DS.'components'.DS.'com_virtuemart'.DS.'views'.DS.'category'.DS.'tmpl'));
	 	$layout = 'default';
		$this->setLayout($layout);
			//echo $layout;
	
	 	//$title=$params->get('results_page_title',JText::_('SEARCH_RESULTS'));
		$title = JText::_('RESULT');
		$this->document->setTitle($title);
	
			//add pathway
	 	$app=JFactory::getApplication();
	 	$pathway = $app->getPathway();
	 	$pathway->addItem($title);
	
	 	//load the virtuemart language files
	 	$language=JFactory::getLanguage();
	 	$language->load('com_virtuemart');
	
	}
	
}
# pure php no closing tag