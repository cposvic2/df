<?php

class DeedfaxProperty {
	protected $id;
	protected $old_id;
	protected $latitude = null;
	protected $longitude = null;
	protected $parish_id = null;
	protected $district_id = 1;
	protected $subdivision_id = 1;
	protected $street_id = 1;
	protected $township = 0;
	protected $square = '';
	protected $lot = '';
	protected $size = '';
	protected $house = '';
	protected $code = '';
	protected $price = 0;
	protected $price_text = '';
	protected $purchaser = '';
	protected $entry = '';
	protected $seller = '';
	protected $sell_date;
	protected $remarks = '';
	protected $publication_date;
	protected $date_added;
	protected $default_datetime_format = 'Y-m-d H:i:s';

	protected $parish;
	protected $district;
	protected $subdivision;
	protected $street;

	public function getId() {
		return $this->id;
	}

	public function setId( $id ) {
		$this->id = $id;
	}

	public function getOldId() {
		return $this->old_id;
	}

	public function setOldId( $old_id ) {
		$this->old_id = $old_id;
	}

	public function getLatitude() {
		if ((float)$this->latitude)
			return (float)$this->latitude;
		return null;
	}

	public function setLatitude( $latitude ) {
		$this->latitude = $latitude;
	}

	public function getLongitude() {
		if ((float)$this->longitude)
			return (float)$this->longitude;
		return null;
	}

	public function setLongitude( $longitude ) {
		$this->longitude = $longitude;
	}

	public function getParishId() {
		return $this->parish_id;
	}

	public function setParishId( $parish_id ) {
		$this->parish_id = $parish_id;
	}

	public function getDistrictId() {
		return $this->district_id;
	}

	public function setDistrictId( $district_id ) {
		$this->district_id = $district_id;
	}

	public function getTownship() {
		return $this->township;
	}

	public function setTownship( $township ) {
		$this->township = $township;
	}

	public function getSubdivisionId() {
		return $this->subdivision_id;
	}

	public function setSubdivisionId( $subdivision_id ) {
		$this->subdivision_id = $subdivision_id;
	}

	public function getSquare() {
		return $this->square;
	}

	public function setSquare( $square ) {
		$this->square = $square;
	}

	public function getLot() {
		return $this->lot;
	}

	public function setLot( $lot ) {
		$this->lot = $lot;
	}

	public function getSize() {
		return $this->size;
	}

	public function setSize( $size ) {
		$this->size = $size;
	}

	public function getHouse() {
		return $this->house;
	}

	public function setHouse( $house ) {
		$this->house = $house;
	}

	public function getStreetId() {
		return $this->street_id;
	}

	public function setStreetId( $street_id ) {
		$this->street_id = $street_id;
	}

	public function getCode() {
		return $this->code;
	}

	public function setCode( $code ) {
		$this->code = $code;
	}

	public function getPrice() {
		return $this->price;
	}

	public function getPrettyPrice() {
		return '$'.number_format($this->price);
	}

	public function setPrettyPrice( $price ) {
		$price = str_replace('$', '', $price);
		$price = str_replace(',', '', $price);
		$this->price = floatval($price);
	}

	public function setPrice( $price ) {
		$this->price = $price;
	}

	public function getPriceText() {
		return $this->price_text;
	}

	public function setPriceText( $price_text ) {
		$this->price_text = $price_text;
	}

	public function getPurchaser() {
		return $this->purchaser;
	}

	public function setPurchaser( $purchaser ) {
		$this->purchaser = $purchaser;
	}

	public function getEntry() {
		return $this->entry;
	}

	public function setEntry( $entry ) {
		$this->entry = $entry;
	}

	public function getSeller() {
		return $this->seller;
	}

	public function setSeller( $seller ) {
		$this->seller = $seller;
	}

	public function getSellDate( $format = null ) {
		return $this->getDateTimeText( $this->sell_date, $format );
	}

	public function setSellDate( $sell_date, $format = null ) {
		$this->sell_date = $this->setDateTime( $sell_date, $format );
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

	public function getRemarks() {
		return $this->remarks;
	}

	public function setRemarks( $remarks ) {
		$this->remarks = $remarks;
	}

	public function getPublicationDate( $format = null ) {
		return $this->getDateTimeText( $this->publication_date, $format );
	}

	public function setPublicationDate( $publication_date, $format = null ) {
			$this->publication_date = $this->setDateTime( $publication_date, $format );
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

	public function getDistrict() {
		if (!$this->district)
			$this->setDistrict(DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->load($this->district_id));
		return $this->district;
	}

	public function setDistrict( $district ) {
		$this->district = $district;
	}

	public function getDistrictName() {
		if (is_null($this->getDistrict())) {
			return null;
		}
		return $this->district->getName();
	}

	public function getStreet() {
		if (!$this->street)
			$this->setStreet(DeedfaxDAOFactory::getDAO('DeedfaxStreetDAO')->load($this->street_id));
		return $this->street;
	}

	public function setStreet( $street ) {
		$this->street = $street;
	}

	public function getStreetName() {
		if (is_null($this->getStreet())) {
			return null;
		}
		return $this->street->getName();
	}

	public function getSubdivision() {
		if (!$this->subdivision)
			$this->setSubdivision(DeedfaxDAOFactory::getDAO('DeedfaxSubdivisionDAO')->load($this->subdivision_id));
		return $this->subdivision;
	}

	public function setSubdivision( $subdivision ) {
		$this->subdivision = $subdivision;
	}

	public function getSubdivisionName() {
		if (is_null($this->getSubdivision())) {
			return null;
		}
		return $this->subdivision->getName();
	}

	private function getDateTimeText( $dateTime, $format ) {
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
		return menu_page_url( 'deedfax-properties', false ).'&'.http_build_query($query);
	}
}

class DeedfaxPropertyDAO extends DeedfaxBaseDAO {
	protected $_tableName = DEEDFAX_PROPERTY_TABLE;
	protected $_columns = array(
		'id' => '%d',
		'latitude' => '%f',
		'longitude' => '%f',
		'parish_id' => '%d',
		'street_id' => '%d',
		'subdivision_id' => '%d',
		'district_id' => '%d',
		'township' => '%s',
		'square' => '%s',
		'lot' => '%s',
		'size' => '%s',
		'house' => '%s',
		'code' => '%s',
		'price' => '%f',
		'purchaser' => '%s',
		'entry' => '%s',
		'seller' => '%s',
		'sell_date' => '%s',
		'remarks' => '%s',
		'publication_date' => '%s',
		'date_added' => '%s'
	);

	public function insert($object){
		global $wpdb;
		$wpdb->insert(
			$this->_tableName,
			array(
				'latitude' => $object->getOldId(), 
				'latitude' => $object->getLatitude(), 
				'longitude' => $object->getLongitude(),
				'parish_id' => $object->getParishId(),
				'district_id' => $object->getDistrictId(),
				'township' => $object->getTownship(),
				'subdivision_id' => $object->getSubdivisionId(),
				'square' => $object->getSquare(),
				'lot' => $object->getLot(),
				'size' => $object->getSize(),
				'house' => $object->getHouse(),
				'street_id' => $object->getStreetId(),
				'code' => $object->getCode(),
				'price' => $object->getPrice(),
				'price' => $object->getPriceText(),
				'purchaser' => $object->getPurchaser(),
				'entry' => $object->getEntry(),
				'seller' => $object->getSeller(),
				'sell_date' => $object->getSellDate(),
				'remarks' => $object->getRemarks(),
				'publication_date' => $object->getPublicationDate(),
				'date_added' => $object->getDateAddedNow(),
			),
			array( 
				'%d',
				'%f',
				'%f',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%f',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
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
				'latitude' => $object->getOldId(), 
				'latitude' => $object->getLatitude(), 
				'longitude' => $object->getLongitude(),
				'parish_id' => $object->getParishId(),
				'district_id' => $object->getDistrictId(),
				'township' => $object->getTownship(),
				'subdivision_id' => $object->getSubdivisionId(),
				'square' => $object->getSquare(),
				'lot' => $object->getLot(),
				'size' => $object->getSize(),
				'house' => $object->getHouse(),
				'street_id' => $object->getStreetId(),
				'code' => $object->getCode(),
				'price' => $object->getPrice(),
				'price' => $object->getPriceText(),
				'purchaser' => $object->getPurchaser(),
				'entry' => $object->getEntry(),
				'seller' => $object->getSeller(),
				'sell_date' => $object->getSellDate(),
				'remarks' => $object->getRemarks(),
				'publication_date' => $object->getPublicationDate(),
				'date_added' => $object->getDateAddedNow(),
			),
			array( 'id' => $object->getId() ),
			array( 
				'%d',
				'%f',
				'%f',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%f',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
	}

	public function queryByParish($value){
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM $this->_tableName WHERE parish_id = %d", $value)
		);
		return $this->getList($results);
	}

	public function searchQueryCount($parish_id, $subdivision_ids, $street_ids, $district_ids, $min_price, $max_price, $start_date, $end_date){
		global $wpdb;
		$values = array();
		$search = "SELECT COUNT(*) FROM $this->_tableName WHERE parish_id = %d";
		$values[] = $parish_id;

		if (count($subdivision_ids)) {
			$search .= " AND subdivision_id IN (%d";
			for ($i=1; $i < count($subdivision_ids); $i++) { 
				$search .= ", %d";
			}
			$search .= ")";
			$values = array_merge($values, $subdivision_ids);
		}

		if (count($street_ids)) {
			$search .= " AND street_id IN (%d";
			for ($i=1; $i < count($street_ids); $i++) { 
				$search .= ", %d";
			}
			$search .= ")";
			$values = array_merge($values, $street_ids);
		}

		if (count($district_ids)) {
			$search .= " AND district_id IN (%d";
			for ($i=1; $i < count($district_ids); $i++) { 
				$search .= ", %d";
			}
			$search .= ")";
			$values = array_merge($values, $district_ids);
		}

		if ($min_price) {
			$search .= " AND price >= %f";
			$values[] = $min_price;
		}

		if ($max_price) {
			$search .= " AND price <= %f";
			$values[] = $max_price;
		}

		if ($start_date) {
			$search .= " AND sell_date >= %s";
			$values[] = $start_date;
		}

		if ($end_date) {
			$search .= " AND sell_date <= %s";
			$values[] = $end_date;
		}

		$results = $wpdb->get_var(
			$wpdb->prepare($search, $values)
		);

		return $results;
	}

	public function searchQuery($parish_id, $subdivision_ids, $street_ids, $district_ids, $min_price, $max_price, $start_date, $end_date, $rowStart = 0, $rowCount = 20){
		global $wpdb;
		$values = array();
		$search = "SELECT * FROM $this->_tableName WHERE parish_id = %d";
		$values[] = $parish_id;

		if (count($subdivision_ids)) {
			$search .= " AND subdivision_id IN (%d";
			for ($i=1; $i < count($subdivision_ids); $i++) { 
				$search .= ", %d";
			}
			$search .= ")";
			$values = array_merge($values, $subdivision_ids);
		}

		if (count($street_ids)) {
			$search .= " AND street_id IN (%d";
			for ($i=1; $i < count($street_ids); $i++) { 
				$search .= ", %d";
			}
			$search .= ")";
			$values = array_merge($values, $street_ids);
		}

		if (count($district_ids)) {
			$search .= " AND district_id IN (%d";
			for ($i=1; $i < count($district_ids); $i++) { 
				$search .= ", %d";
			}
			$search .= ")";
			$values = array_merge($values, $district_ids);
		}

		if ($min_price) {
			$search .= " AND price >= %f";
			$values[] = $min_price;
		}

		if ($max_price) {
			$search .= " AND price <= %f";
			$values[] = $max_price;
		}

		if ($start_date) {
			$search .= " AND sell_date >= %s";
			$values[] = $start_date;
		}

		if ($end_date) {
			$search .= " AND sell_date <= %s";
			$values[] = $end_date;
		}

		$search .=" LIMIT ".$rowStart.",".$rowCount;

		$results = $wpdb->get_results(
			$wpdb->prepare($search, $values)
		);
		return $this->getList($results);
	}

	protected function readRow($row){
		$object = new DeedfaxProperty();

		$object->setId( $row->id );
		$object->setOldId( $row->old_id );
		$object->setLatitude( $row->latitude );
		$object->setLongitude( $row->longitude );
		$object->setParishId( $row->parish_id );
		$object->setDistrictId( $row->district_id );
		$object->setTownship( $row->township );
		$object->setSubdivisionId( $row->subdivision_id );
		$object->setSquare( $row->square );
		$object->setLot( $row->lot );
		$object->setSize( $row->size );
		$object->setHouse( $row->house );
		$object->setStreetId( $row->street_id );
		$object->setCode( $row->code );
		$object->setPrice( $row->price );
		$object->setPriceText( $row->price_text );
		$object->setPurchaser( $row->purchaser );
		$object->setEntry( $row->entry );
		$object->setSeller( $row->seller );
		$object->setSellDate( $row->sell_date );
		$object->setRemarks( $row->remarks );
		$object->setPublicationDate( $row->publication_date );
		$object->setDateAdded( $row->date_added );

		return $object;
	}
}