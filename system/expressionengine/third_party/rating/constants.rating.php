<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Rating - Constants
 *
 * Central location for various values we need throughout the module.
 *
 * @package		Solspace:Rating
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2015, Solspace, Inc.
 * @link		http://solspace.com/docs/rating
 * @license		http://www.solspace.com/license_agreement
 * @version		3.3.1
 * @filesource	rating/constants.rating.php
 */

if ( ! defined('RATING_VERSION'))
{
	define('RATING_VERSION',	'3.3.1');
	define('RATING_DOCS_URL',	'http://solspace.com/docs/rating/');
	define('RATING_PREFS',		'can_delete_ratings|can_report_ratings|can_post_ratings|enabled_channels|quarantine_minimum|require_email|use_captcha');
}
