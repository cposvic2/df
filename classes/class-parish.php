<?php

class DeedfaxParish {
	protected $id;
	protected $slug;
	protected $name;

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

	public function getEditLink() {
		$query = array(
			'action' => 'edit',
			'id' => $this->getId(),
		);
		return menu_page_url( 'deedfax-parishes', false ).'&'.http_build_query($query);
	}
}

class DeedfaxParishDAO extends DeedfaxBaseDAO {
	protected $_tableName = DEEDFAX_PARISH_TABLE;
	protected $_columns = array(
		'id' => '%d',
		'slug' => '%s',
		'name' => '%s',
		'date_added' => '%s',
	);

	public function insert($object){
		global $wpdb;
		$wpdb->insert(
			$this->_tableName, 
			array(
				'name' => $object->getName(), 
				'slug' => $object->getSlug(),
			),
			array( 
				'%s', 
				'%s',
			)
		);
		return $wpdb->insert_id;
	}

	public function update($object){
		global $wpdb;
		return $wpdb->query(
			$wpdb->prepare("UPDATE $this->_tableName SET slug = %s, name = %s WHERE id = %d", $object->getSlug(), $object->getName(), $object->getId())
		);
	}

	public function loadBySlug($value){
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM $this->_tableName WHERE slug = %s", $value)
		);
		return $this->getRow($results);
	}

	protected function readRow($row){
		$object = new DeedfaxParish();
		
		$object->setId( $row->id );
		$object->setSlug( $row->slug );
		$object->setName( $row->name );

		return $object;
	}
}