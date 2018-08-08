<?php

require_once DEEDFAX_PLUGIN_PATH . '/import.php';

/*
 * Import columns
 */
$import_columns = array(
	array('value' => 'subdivision', 'label' => 'Subdivision'),
	array('value' => 'district', 'label' => 'District'),
	array('value' => 'township', 'label' => 'Township'),
	array('value' => 'square', 'label' => 'Square'),
	array('value' => 'lots', 'label' => 'Lot(s)'),
	array('value' => 'size', 'label' => 'Size'),
	array('value' => 'house', 'label' => 'House'),
	array('value' => 'number', 'label' => 'Address Number'),
	array('value' => 'street', 'label' => 'Street Name'),
	array('value' => 'code', 'label' => 'Code'),
	array('value' => 'price', 'label' => 'Sale Price'),
	array('value' => 'purchaser', 'label' => 'Purchaser'),
	array('value' => 'seller', 'label' => 'Seller Name'),
	array('value' => 'entry', 'label' => 'Entry #'),
	array('value' => 'sell_date', 'label' => 'Sell Date (mm/dd/yyyy)'),
	array('value' => 'pubyear', 'label' => 'Publication Year'),
	array('value' => 'pubmonth', 'label' => 'Publication Month'),
	array('value' => 'remarks', 'label' => 'Notes/Remarks'),
	array('value' => 'other', 'label' => 'Other'),
);

/**
 * Add custom Admin page
 */
function deedfax_register_custom_admin_pages() {

	add_menu_page(
		'Properties',
		'Deedfax Properties',
		'add_deedfax_properties',
		'deedfax-properties',
		'deedfax_admin_properties_callback',
		'dashicons-admin-multisite',
		6
	);
	add_submenu_page(
		'deedfax-properties',
		'Import Properties',
		'Import Properties',
		'add_deedfax_properties',
		'deedfax-import',
		'deedfax_admin_import_callback'
	);
	add_submenu_page(
		'deedfax-properties',
		'Parishes',
		'Parishes',
		'manage_deedfax',
		'deedfax-parishes',
		'deedfax_admin_parishes_callback'
	);
	add_submenu_page(
		'deedfax-properties',
		'Districts',
		'Districts',
		'manage_deedfax',
		'deedfax-districts',
		'deedfax_admin_districts_callback'
	);
	add_submenu_page(
		'deedfax-properties',
		'Streets',
		'Streets',
		'manage_deedfax',
		'deedfax-streets',
		'deedfax_admin_streets_callback'
	);
	add_submenu_page(
		'deedfax-properties',
		'Subdivisions',
		'Subdivisions',
		'manage_deedfax',
		'deedfax-subdivisions',
		'deedfax_admin_subdivisions_callback'
	);
}
add_action( 'admin_menu', 'deedfax_register_custom_admin_pages' );

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Deedfax_Table extends WP_List_Table {
	protected $_tableName;
	protected $columns;
	protected $Dao;
	protected $perPage = 20;

	function set_type($type) {
		switch ($type) {
			case 'parish':
				$this->Dao = 'DeedfaxParishDAO';
				$this->columns = array(
					'id'=>'ID',
					'name'=>'Name',
					'slug'=>'Slug',
					'edit'=>'Edit',
				);
				break;
			case 'subdivision':
				$this->Dao = 'DeedfaxSubdivisionDAO';
				$this->columns = array(
					'id'=>'ID',
					'name'=>'Name',
					'slug'=>'Slug',
					'parish_id'=>'Parish',
					'edit'=>'Edit',
				);
				break;
			case 'property':
				$this->Dao = 'DeedfaxPropertyDAO';
				$this->columns = array(
					'id'=>'ID',
					'street_id'=>'Front Street',
					'house'=>'House Number',
					'subdivision_id'=>'Subdivision',
					'square'=>'Square',
					'lot'=>'Lot',
					'size'=>'Size',
					'code'=>'Code',
					'price'=>'Price',
					'purchaser'=>'Purchaser',
					'seller'=>'Seller',
					'entry'=>'Entry',
					'sell_date'=>'Date',
					'parish_id'=>'Parish',
					'edit'=>'Edit',
				);
				break;
			case 'street':
				$this->Dao = 'DeedfaxStreetDAO';
				$this->columns = array(
					'id'=>'ID',
					'name'=>'Name',
					'slug'=>'Slug',
					'parish_id'=>'Parish',
					'edit'=>'Edit',
				);
				break;
			case 'district':
				$this->Dao = 'DeedfaxDistrictDAO';
				$this->columns = array(
					'id'=>'ID',
					'name'=>'Name',
					'slug'=>'Slug',
					'parish_id'=>'Parish',
					'edit'=>'Edit',
				);
				break;
		}
	}

	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$data = $this->table_data2();
		$currentPage = $this->get_pagenum();
		$this->set_pagination_args( array(
			'total_items' => $this->total_items(),
			'per_page'    => $this->perPage
		) );
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $data;
	}

	public function get_columns() {
		return $this->columns;
	}

	public function get_hidden_columns() {
		return array();
	}

	public function get_sortable_columns() {
		$return = array();
		if (count($this->columns)) {
			foreach ($this->columns as $id=>$column) {
				$return[$id] = array($id, false);
			}
		}
		return $return;
	}

	private function table_data() {
		$data = DeedfaxDAOFactory::getDAO($this->Dao)->queryAll();
		return $data;
	}

	private function table_data2() {
		$orderby = 'id';
		$order = 'desc';
		if(!empty($_GET['orderby'])) {
			$orderby = $_GET['orderby'];
		}
		if(!empty($_GET['order'])) {
			$order = $_GET['order'];
		}
		$currentPage = $this->get_pagenum();
		$rowStart = ($currentPage-1)*$this->perPage;
		$data = DeedfaxDAOFactory::getDAO($this->Dao)->queryAllOrderByAndPaginate($orderby, $order, $rowStart, $this->perPage);
		return $data;
	}

	private function total_items() {
		return DeedfaxDAOFactory::getDAO($this->Dao)->count();
	}

	public function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'id':
				return $item->getId();
			case 'name':
				return $item->getName();
			case 'slug':
				return $item->getSlug();
			case 'parish_id':
				return $item->getParishName();
			case 'street_id':
				return $item->getStreetName();
			case 'house':
				return $item->getHouse();
			case 'subdivision_id':
				return $item->getSubdivisionName();
			case 'square':
				return $item->getSquare();
			case 'lot':
				return $item->getLot();
			case 'size':
				return $item->getSize();
			case 'code':
				return $item->getCode();
			case 'price':
				return $item->getPrettyPrice();
			case 'purchaser':
				return $item->getPurchaser();
			case 'seller':
				return $item->getSeller();
			case 'entry':
				return $item->getEntry();
			case 'sell_date':
				return $item->getSellDate( 'm/d/y' );
			case 'edit':
				return '<a href="'.$item->getEditLink().'">Edit</a>';
			default:
				return $item->getId();
		}
	}

	private function sort_data( $a, $b ) {
		// Set defaults
		$orderby = 'id';
		$order = 'asc';
		if(!empty($_GET['orderby'])) $orderby = $_GET['orderby'];
		if(!empty($_GET['order'])) $order = $_GET['order'];
		switch ($orderby) {
			case 'id':
				$result = $this->column_default($a, $orderby) > $this->column_default($b, $orderby);
				break;
			default:
				$result = strcmp( $this->column_default($a, $orderby), $this->column_default($b, $orderby) );
				break;
		}
		if($order === 'asc') return $result;
		return -$result;
	}
}

function callback_start() {
	$class = null;
	if (isset($_GET['deleted'])) {
		if (!!$_GET['deleted']) {
			$class = 'updated ';
		} else {
			$class = 'error ';
		}
	}
	if (isset($_GET['updated'])) {
		if (!!$_GET['updated']) {
			$class = 'updated ';
		} else {
			$class = 'error ';
		}
	}

	?>
		<div class="wrap">
		<?php if (!!$class) : ?>
			<div id="message" class="<?php echo $class; ?>notice is-dismissible"><p><?php echo sanitize_text_field($_GET['message']); ?></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
		<?php endif; ?>
	<?php
}

function callback_end() {
	?></div><?php
}