<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Rating - Data Models
 *
 * @package		Solspace:Rating
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2015, Solspace, Inc.
 * @link		http://solspace.com/docs/rating
 * @license		http://www.solspace.com/license_agreement
 * @version		3.3.1
 * @filesource	rating/data.rating.php
 */

require_once 'addon_builder/data.addon_builder.php';

class Rating_data extends Addon_builder_data_rating
{
	public $cached = array();

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct()
	{
		parent::__construct();

		$this->EE =& get_instance();
	}
	/* END constructor */

	// --------------------------------------------------------------------

	/**
	 * List of Rating's Fields
	 *
	 * @access	public
	 * @param	params	MySQL clauses, if necessary
	 * @return	array
	 */

	function get_rating_fields_list( $params = array())
	{
		/** --------------------------------------------
		/**  Prep Cache, Return if Set
		/** --------------------------------------------*/

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		/** --------------------------------------------
		/**  Perform the Actual Work
		/** --------------------------------------------*/

		$query = ee()->db->query( "SELECT field_name, field_label FROM exp_rating_fields" );

		foreach($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][$row['field_name']] = $row['field_label'];
		}

		/** --------------------------------------------
		/**  Return Data
		/** --------------------------------------------*/

		return $this->cached[$cache_name][$cache_hash];
	}
	/* END get_rating_fields() */


	// --------------------------------------------------------------------

	/**
	 * Full Data for all of Rating's Fields
	 *
	 * @access	public
	 * @param	params	MySQL clauses, if necessary
	 * @return	array
	 */

	function get_rating_fields_data( $params = array())
	{
		/** --------------------------------------------
		/**  Prep Cache, Return if Set
		/** --------------------------------------------*/

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		/** --------------------------------------------
		/**  Perform the Actual Work
		/** --------------------------------------------*/

		$query = ee()->db->query( "SELECT * FROM exp_rating_fields ORDER BY field_order ASC" );

		foreach($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][$row['field_name']] = $row;
		}

		/** --------------------------------------------
		/**  Return Data
		/** --------------------------------------------*/

		return $this->cached[$cache_name][$cache_hash];
	}
	/* END get_rating_fields_data() */

	// --------------------------------------------------------------------
}
// END CLASS Rating_data