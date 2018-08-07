<?php

class DeedfaxSubdivision {
	protected $id;
	protected $slug;
	protected $name;
	protected $parish_id;
	protected $parish;
	protected $date_added;
	protected $default_datetime_format = 'Y-m-d H:i:s';

	public function getId() {
		return $this->id;
	}

	public function setId( $id ) {
		$this->id = $id;
	}

	public function getSlug() {
		return $this->slug;
	}

	public function setSlug( $slug ) {
		$this->slug = $slug;
	}

	public function getName() {
		return $this->name;
	}

	public function setName( $name ) {
		$this->name = $name;
	}

	public function getParishId() {
		return $this->parish_id;
	}

	public function setParishId( $parish_id ) {
		$this->parish_id = $parish_id;
	}

	public function getParish() {
		if (!$this->parish)
			$this->setParish(DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->load($this->parish_id));
		return $this->parish;
	}

	public function setParish( $parish ) {
		$this->parish = $parish;
	}

	public function getParishName() {
		if (is_null($this->getParish())) {
			return null;
		}
		return $this->parish->getName();
	}

	public function getDateAdded( $format = null ) {
		return $this->getDateTimeText( $this->date_added, $format );
	}

	public function setDateAdded( $date_added, $format = null ) {
		$this->date_added = $this->setDateTime( $date_added, $format );
	}

	public function getDateAddedNow( $format = null ) {
		$this->setDateAddedNow();
		return $this->getDateTimeText( $this->date_added, $format );
	}

	public function setDateAddedNow( $format = null ) {
		$this->date_added = $this->setDateTime( date('Y-m-d H:i:s'), $format );
	}

	private function getDateTimeText( $dateTime, $format = null ) {
		if ( empty( $dateTime ) )
			return '';

		if ( empty($format) )
			$format = $this->default_datetime_format;

		return $dateTime->format($format);
	}

	private function setDateTime( $text, $format = null ) {
		if ( empty($format) )
			$format = $this->default_datetime_format;

		return DateTime::createFromFormat($format, $text);
	}

	public function getEditLink() {
		$query = array(
			'action' => 'edit',
			'id' => $this->getId(),
		);
		return menu_page_url( 'deedfax-subdivisions', false ).'&'.http_build_query($query);
	}
}

class DeedfaxSubdivisionDAO extends DeedfaxBaseDAO {
	protected $_tableName = DEEDFAX_SUBDIVISION_TABLE;
	protected $_columns = array(
		'id' => '%d',
		'slug' => '%s',
		'name' => '%s',
		'parish_id' => '%d',
		'date_added' => '%s',
	);

	public function insert($object){
		global $wpdb;
		$wpdb->insert(
			$this->_tableName,
			array(
				'name' => $object->getName(), 
				'slug' => $object->getSlug(),
				'parish_id' => $object->getParishId(),
				'date_added' => $object->getDateAddedNow(),
			),
			array( 
				'%s',
				'%s',
				'%d',
				'%s',
			)
		);
		return $wpdb->insert_id;
	}

	public function update($object){
		global $wpdb;
		return $wpdb->update(
			$this->_tableName,
			array(
				'name' => $object->getName(), 
				'slug' => $object->getSlug(),
				'parish_id' => $object->getParishId(),
				'date_added' => $object->getDateAddedNow(),
			),
			array( 'id' => $object->getId() ),
			array( 
				'%s',
				'%s',
				'%d',
				'%s',
			)
		);
	}

	public function queryByParish($value, $orderColumn = 'id'){
		global $wpdb;
		$columns = array_keys($this->_columns);
		if (!in_array($orderColumn, $columns)) {
			$orderColumn = 'id';
		}
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM $this->_tableName WHERE parish_id = %d ORDER BY ".$orderColumn, $value)
		);
		return $this->getList($results);
	}

	public function loadBySlug($value){
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM $this->_tableName WHERE slug = %s", $value)
		);
		return $this->getRow($results);
	}

	public function loadByNameAndParish($value, $parish_id){
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM $this->_tableName WHERE name = %s AND parish_id = %d", $value, $parish_id)
		);
		return $this->getRow($results);
	}

	public function loadBySlugAndParish($value, $parish_id){
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM $this->_tableName WHERE slug = %s AND parish_id = %d", $value, $parish_id)
		);
		return $this->getRow($results);
	}

	protected function readRow($row){
		$object = new DeedfaxSubdivision();
		
		$object->setId( $row->id );
		$object->setSlug( $row->slug );
		$object->setName( $row->name );
		$object->setDateAdded( $row->date_added );
		$object->setParishId( $row->parish_id );

		return $object;
	}
}