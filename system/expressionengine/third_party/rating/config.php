<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Rating - Config
 *
 * NSM Addon Updater config file.
 *
 * @package		Solspace:Rating
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2015, Solspace, Inc.
 * @link		http://solspace.com/docs/rating
 * @license		http://www.solspace.com/license_agreement
 * @version		3.3.1
 * @filesource	rating/config.php
 */

require_once 'constants.rating.php';

$config['name']									= 'Rating';
$config['version'] 								= RATING_VERSION;
$config['nsm_addon_updater']['versions_xml'] 	= 'http://www.solspace.com/software/nsm_addon_updater/rating';
