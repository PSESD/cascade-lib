<?php

namespace cascade\components\taxonomy;

use infinite\base\exceptions\Exception;
use infinite\helpers\ArrayHelper;


class Item extends \infinite\base\collector\Item {
	public $name;
	public $systemId;
	public $systemVersion = 1;
	public $initialTaxonomies = [];
	public $models = [];
	public $modules = [];
	public $multiple = false;
	public $required = false;
	public $default = [];
	public $parentUnique = false;

	protected $_taxonomies;


	public function package($override = [])
	{
		return array_merge([
			'id' => $this->systemId,
			'name' => $this->name,
			'multiple' => $this->multiple,
			'required' => $this->required,
			'default' => $this->default,
			'taxonomies' => $this->taxonomyList
		], $override);	
	}
	/**
	 *
	 *
	 * @return unknown
	 */
	public function getTaxonomies() {
		if (is_null($this->_taxonomies)) {
			$this->_taxonomies = $this->object->taxonomies;
		}
		return $this->_taxonomies;
	}
	/**
	 *
	 *
	 * @return unknown
	 */
	public function getTaxonomyList() {
		return ArrayHelper::map($this->getTaxonomies(), 'id', 'name');
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getTaxonomy($system_id) {
		foreach ($this->getTaxonomies() as $taxonomy) {
			if ($taxonomy->system_id === $system_id) {
				return $taxonomy;
			}
		}
		return false;
	}

	public function addTaxonomy($taxonomy) {
		$this->taxonomies;
		if (is_null($this->_taxonomies)) {
			$this->taxonomies;
		}
		$this->_taxonomies[] = $taxonomy;
	}

}


?>
