<?php 

abstract class DeedfaxBaseDAO {
	protected $_tableName = '';
	protected $_columns;

	public function load($id){
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM $this->_tableName WHERE id = %d",$id)
		);
		return $this->getRow($results);
	}

	public function queryAll(){
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM $this->_tableName");
		return $this->getList($results);
	}

	public function count(){
		global $wpdb;
		$results = $wpdb->get_var("SELECT COUNT(*) FROM $this->_tableName");
		return $results;
	}

	public function queryAllOrderByAndPaginate($orderColumn = 'id', $order = 'asc', $rowStart = 0, $rowCount = 10){
		global $wpdb;
		if (!(int)$rowStart) {
			$rowStart = 0;
		}
		if (!(int)$rowCount) {
			$rowCount = 10;
		}
		$columns = array_keys($this->_columns);
		if (!in_array($orderColumn, $columns)) {
			$orderColumn = 'id';
		}
		switch ($order) {
			case 'asc':
				$order = 'desc';
				break;
			default:
				$order = 'asc';
				break;
		}
		$results = $wpdb->get_results("SELECT * FROM $this->_tableName ORDER BY ".$orderColumn." ".$order." LIMIT ".$rowStart.",".$rowCount);
		return $this->getList($results);
	}

	public function delete($id){
		global $wpdb;
		$results = $wpdb->query(
			$wpdb->prepare("DELETE FROM $this->_tableName WHERE id = %d",$id)
		);
		return $results;
	}

	public function clean(){
		global $wpdb;
		$results = $wpdb->query(
			$wpdb->prepare("DELETE FROM $this->_tableName")
		);
		return $results;
	}

	public function query($queries){
		global $wpdb;
		$columns = array_keys($this->_columns);
		$searches = $values = array();
		foreach ($queries as $key => $value) {
			if (in_array($key, $columns)) {
				$searches[] = $key.' = '.$this->_columns[$key];
				$values[] = $value;
			}
		}
		if (count($searches)) {
			$query = "SELECT * FROM $this->_tableName WHERE ".implode(" AND ", $searches);
			$results = $wpdb->get_results(
				$wpdb->prepare($query, $values)
			);
			return $this->getList($results);
		}
		return null;
	}

	public function queryCount($queries){
		global $wpdb;
		$columns = array_keys($this->_columns);
		$searches = $values = array();
		foreach ($queries as $key => $value) {
			if (in_array($key, $columns)) {
				$searches[] = $key.' = '.$this->_columns[$key];
				$values[] = $value;
			}
		}
		if (count($searches)) {
			$query = "SELECT * FROM $this->_tableName WHERE ".implode(" AND ", $searches);
			$results = $wpdb->get_var(
				$wpdb->prepare($query, $values)
			);
			return $results;
		}
		return null;
	}

	protected function getList($results){
		$ret = array();
		for($i=0;$i<count($results);$i++){
			$ret[$i] = $this->readRow($results[$i]);
		}
		return $ret;
	}

	protected function getRow($results){
		if(count($results)==0){
			return null;
		}
		return $this->readRow($results[0]);		
	}
}