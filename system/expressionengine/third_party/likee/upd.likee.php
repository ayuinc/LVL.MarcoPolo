<?php 
/*
 __    __                                   __       ___     __
/\ \__/\ \                                 /\ \__  /'___\ __/\ \__
\ \ ,_\ \ \___      __         ___   __  __\ \ ,_\/\ \__//\_\ \ ,_\
 \ \ \/\ \  _ `\  /'__`\      / __`\/\ \/\ \\ \ \/\ \ ,__\/\ \ \ \/
  \ \ \_\ \ \ \ \/\  __/     /\ \L\ \ \ \_\ \\ \ \_\ \ \_/\ \ \ \ \_
   \ \__\\ \_\ \_\ \____\    \ \____/\ \____/ \ \__\\ \_\  \ \_\ \__\
    \/__/ \/_/\/_/\/____/     \/___/  \/___/   \/__/ \/_/   \/_/\/__/
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Include config file
require_once PATH_THIRD . 'likee/config' . EXT;

class Likee_upd {

	var $version = LIKEE_VERSION;
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	C O N S T R U C T O R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function Likee_upd(){
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	I N S T A L L E R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function install(){
	
		$this->EE->load->dbforge();

		$data = array(
			'module_name' => 'Likee' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);

		$this->EE->db->insert('modules', $data);

		$fields = array(
						'like_id'			=> array('type' 		 => 'int',
													'constraint'	 => '10',
													'unsigned'		 => TRUE,
													'auto_increment' => TRUE),
						'liked'				=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'disliked'			=> array('type'	=> 'tinyint', 'constraint'=>'4'),
						'entry_id'			=> array('type'	=> 'int', 'constraint'=>'10'),
						'comment_id'		=> array('type'	=> 'int', 'constraint'=>'10'),
						'member_id'			=> array('type'	=> 'int', 'constraint'=>'10'),
						'date'				=> array('type' => 'datetime'),
						'ip_address'		=> array('type' => 'varchar', 'constraint' => '255'),
						'cookie_uid'		=> array('type' => 'varchar', 'constraint' => '255'),
						);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('like_id', TRUE);
		$this->EE->dbforge->create_table('likee');	
		unset($fields);
	
		return TRUE;
	}
	
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	U N I N S T A L L E R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function uninstall()
	{
		$this->EE->load->dbforge();

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'LikEE'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Likee');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Likee');
		$this->EE->db->delete('actions');

		$this->EE->dbforge->drop_table('likee');
		
		return TRUE;
	}



	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	U P D A T E
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	
	
	function update($current='')
	{
		return TRUE;
	}
	
}

?>