<?php
/**
 * Search filter for option sets, used for searching {@link Order} statuses in the CMS.
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage search
 */
class ShopSearchFilter_OptionSet extends SearchFilter {

  /**
   * Apply filter query SQL to a search query
   * 
   * @see SearchFilter::apply()
   * @return SQLQuery
   */
	public function apply(DataQuery $query) {
		
		$this->model = $query->applyRelation($this->relation);
		$values = $this->getValue();
		
		if (count($values)) {
			foreach ($values as $value) {
				$matches[] = sprintf("%s LIKE '%s%%'",
					$this->getDbName(),
					Convert::raw2sql(str_replace("'", '', $value))
				);
			}

			return $query->where(implode(" OR ", $matches));
		}
		return $query;
	}

	/**
	 * Determine whether the filter should be applied, depending on the 
	 * value of the field being passed
	 * 
	 * @see SearchFilter::isEmpty()
	 * @return Boolean
	 */
	public function isEmpty() {

		if(is_array($this->getValue())) {
			return count($this->getValue()) == 0;
		}
		else {
			return $this->getValue() == null || $this->getValue() == '';
		}
	}
}

/**
 * Search filter for determining whether an {@link Order} has a {@link Payment} attached.
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage search
 */
class ShopSearchFilter_Payment extends SearchFilter {

  /**
   * Apply filter query SQL to a search query
   * 
   * @see SearchFilter::apply()
   */
	public function apply(DataQuery $query) {

		$this->model = $query->applyRelation($this->relation);
		$value = array_pop($this->getValue());

		if ($value == 2 || $value == 1) {
			$query->leftJoin(
				$table = "Payment", // framework already applies quotes to table names here!
				$onPredicate = "\"Payment\".\"OrderID\" = \"Order\".\"ID\"",
				$tableAlias = 'Payment'
			);
			
			if ($value == 2) $query->where('"Payment"."ID" IS NULL');
			if ($value == 1) $query->where('"Payment"."ID" IS NOT NULL');
		}
		return $query;
	}

	/**
	 * Determine whether the filter should be applied, depending on the 
	 * value of the field being passed
	 * 
	 * @see SearchFilter::isEmpty()
	 * @return Boolean
	 */
	public function isEmpty() {
		return $this->getValue() == null || $this->getValue() == ''; //|| $this->getValue() == 0;
	}
}

/**
 * Search filter for {@link Product} categories, filtering search results for 
 * certain {@link ProductCategory}s in the CMS.
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage search
 */
class ShopSearchFilter_ProductCategory extends SearchFilter {

  /**
   * Apply filter query SQL to a search query
   * 
   * @see SearchFilter::apply()
   * @return SQLQuery
   */
	public function apply(DataQuery $query) {

	  $this->model = $query->applyRelation($this->relation);
	  $value = $this->getValue();

	  if ($value) {

	    $query->innerJoin(
  			'ProductCategory_Products',
  			"\"ProductCategory_Products\".\"ProductID\" = \"SiteTree\".\"ID\""
  		);
  		$query->innerJoin(
  			'SiteTree_Live',
  			"\"SiteTree_Live\".\"ID\" = \"ProductCategory_Products\".\"ProductCategoryID\""
  		);
  		$query->where("\"SiteTree_Live\".\"Title\" LIKE '%" . Convert::raw2sql($value) . "%'");
	  }
	  return $query;
	}

	/**
	 * Determine whether the filter should be applied, depending on the 
	 * value of the field being passed
	 * 
	 * @see SearchFilter::isEmpty()
	 * @return Boolean
	 */
	public function isEmpty() {
		return $this->getValue() == null || $this->getValue() == '';
	}
}

/**
 * Search filter for {@link Product} status, whether a {@link Product} is published
 * or unpublished.
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage search
 */
class ShopSearchFilter_PublishedStatus extends SearchFilter {

  /**
   * Apply filter query SQL to a search query
   * 
   * @see SearchFilter::apply()
   */
	public function apply(DataQuery $query) {

		return $query;
	  
	  $query = $this->applyRelation($query);
		$value = $this->getValue();

	  if ($value) {
	    if ($value == 1) return $query->where("Status = 'Published'");
	    if ($value == 2) return $query->where("Status != 'Published'");
		}
	}

	/**
	 * Determine whether the filter should be applied, depending on the 
	 * value of the field being passed
	 * 
	 * @see SearchFilter::isEmpty()
	 * @return Boolean
	 */
	public function isEmpty() {
		return $this->getValue() == null || $this->getValue() == '' || $this->getValue() == 0;
	}
}