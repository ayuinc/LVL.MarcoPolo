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

class Likee_mcp {
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	C O N S T R U C T O R
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function Likee_mcp(){
		
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		$this->EE->db->cache_off();
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	M O D U L E   M A I N   P A G E
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function index(){
		$this->EE->load->library('table');
		
		if (version_compare(APP_VER, '2.6.0', '<'))
        {
            $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('likee_module_name'));
        }
        else
        {
            $this->EE->view->cp_page_title = $this->EE->lang->line('likee_module_name');
        }

		$this->EE->cp->add_to_head($this->css());
		
		// Check to see if there is anything in the database
			$this->EE->db->select("like_id");
			$q = $this->EE->db->get("likee");
			
			
			if ($q->num_rows() > 0){
				
				// Let's rock some statistics
					$vars["instructions"] = false;
					$vars["likes"] = array();
					$vars["dislikes"] = array();
				
				// Entry Likes
					$this->EE->db->select("COUNT(l.liked) AS likeCount,
											l.*,
											ct.author_id,
											ct.title,
											ct.url_title,
											ct.status,
											c.channel_name,
											c.channel_title");
					$this->EE->db->from("likee AS l");
					$this->EE->db->join("channel_titles AS ct", "l.entry_id=ct.entry_id", "LEFT OUTER JOIN");
					$this->EE->db->join("channels AS c", "ct.channel_id=c.channel_id", "LEFT OUTER JOIN");
					$this->EE->db->where("l.liked", 1);
					$this->EE->db->where("l.entry_id >", 0);
					$this->EE->db->group_by("l.entry_id");
	    			$this->EE->db->order_by("likeCount", "DESC");
	    			$this->EE->db->limit(10);
	    			$query = $this->EE->db->get();
	
	    			foreach($query->result() AS $row){
	    				$vars["likes"][$row->like_id]["title"] = $row->title;
	    				$vars["likes"][$row->like_id]["count"] = $row->likeCount;
	    				$vars["likes"][$row->like_id]["channel_name"] = $row->channel_name;
	    			}
	    			$query->free_result();
    			
    			// Entry Dislikes
    				$this->EE->db->select("COUNT(l.disliked) AS dislikeCount,
											l.*,
											ct.author_id,
											ct.title,
											ct.url_title,
											ct.status,
											c.channel_name,
											c.channel_title");
					$this->EE->db->from("likee AS l");
					$this->EE->db->join("channel_titles AS ct", "l.entry_id=ct.entry_id", "LEFT OUTER JOIN");
					$this->EE->db->join("channels AS c", "ct.channel_id=c.channel_id", "LEFT OUTER JOIN");
					$this->EE->db->where("l.disliked", 1);
					$this->EE->db->where("l.entry_id >", 0);
					$this->EE->db->group_by("l.entry_id");
	    			$this->EE->db->order_by("dislikeCount", "DESC");
	    			$this->EE->db->limit(10);
	    			$query = $this->EE->db->get();
	
	    			foreach($query->result() AS $row){
	    				$vars["dislikes"][$row->like_id]["title"] = $row->title;
	    				$vars["dislikes"][$row->like_id]["count"] = $row->dislikeCount;
	    				$vars["dislikes"][$row->like_id]["channel_name"] = $row->channel_name;
	    			}
	    			$query->free_result();	
				
			} else {
				
				// If the db is empty, set flag for instructions
				$vars["instructions"] = true;
				
			}	
			$q->free_result();
			
$vars["js"] = <<<EOD
&#123;exp:likee:js&#125;	
EOD;

$vars["likeeCode"] = <<<EOD
&#123;exp:likee:like entry_id="99"&#125;
   I &#123;like&#125; it.
   I &#123;dislike&#125; it.
   &#123;like_count&#125; likes
   &#123;dislike_count&#125; dislikes	
&#123;/exp:likee:like&#125;
EOD;
		
		return $this->EE->load->view('index', $vars, TRUE);
	}
	
	function css(){
		$css = '<style type="text/css">
				#likEE {
					padding: 1em 2em 10em 2em;
					font-family: Helvetica, Arial, sans-serif;
					color: #51463D;
					font-size: 1.4em;
				}
	
				#likEE h1 {
					font-size: 2em;
					color: #51463D;
					margin-bottom: .4em;
				}
				
				#likEE h1 span {
					color: #666;
				}
								
				#likEE h2 {
					font-size: 1.4em;
					margin-bottom: 1em;
					font-weight: normal;
					font-family: Georgia;
					margin: -.4em 0 1em 0;
					color: #666;
				}
				
				#likEE h3 {
					font-size: 1.2em;
					margin-bottom: 1em;
				}
				
				#likEE .description {
					padding: 0px 20px 20px 0px;
				}
				
				#likEE .box {
					width: 47%;
					float: left;
					padding: 10px;
				}
				
				#likEE .box h2 {
					padding: 0 0 2px 0;
					margin: 0;
				}
				
				#likEE .clear {
					clear:both;
					display: block;
				}
				
				#likEE .no-likes {
					background-color: #fff;
					-moz-border-radius-bottomleft:10px;
					-moz-border-radius-bottomright:10px;
					-moz-border-radius-topleft:10px;
					-moz-border-radius-topright:10px;
					-webkit-border-top-right-radius: 10px;
					-webkit-border-bottom-right-radius: 10px;
					-webkit-border-top-left-radius: 10px;
					-webkit-border-bottom-left-radius: 10px;
					padding: 20px;
					font-size: 1.2em;
				}
				
				#likEE .no-likes p {
					font-size: 1em;
					padding: 0 0 10px 0;
				}
				
				
				#likEE .no-likes h2 {
					margin: 0;
					padding: 0;
				}
				
				#likEE .no-likes h3 {
					margin: 0;
					padding: 20px 0 0 0;
					border: none;
				}
				
				#likEE pre {
					font-size: 16px;
					color: #fff;
					padding: 0;
					margin: 0;
				}
				
				#likEE code {
					background-color: rgba(158, 171, 181, 1);
					border-bottom-left-radius: 15px 15px;
					border-bottom-right-radius: 15px 15px;
					border-top-left-radius: 15px 15px;
					border-top-right-radius: 15px 15px;
					display: block;
					font-family: Courier, monospace;
					font-size: 16px;
					padding: 20px;
					margin: 0;
				}
				
				
			</style>';
			
			return $css;
	}

}
?>