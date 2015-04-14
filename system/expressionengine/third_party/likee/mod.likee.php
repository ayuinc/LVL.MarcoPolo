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

class Likee {

	var $version				= LIKEE_VERSION;
	var $return_data    		= '';
    var $text_like				= 'Like';
    var $text_dislike			= 'Dislike'; 
    var $class_like				= 'likee_like';
    var $class_dislike			= 'likee_dislike';
    var $like_count				= 0;
    var $dislike_count			= 0;
    var $like_score				= 0;
    var $my_feeling				= '';
    var $order_by				= 'count';
    var $query_sort				= 'desc';
    var $type					= 'liked';
    var $limit					= 0;
    var $channel_array			= array();
    var $id						= 0;
    var $idType					= 'entry';
    var $isComment				= false; 
    var $member_id				= 0;
    var $cookie_uid				= '';
    var $only_current_user		= false;
    var $ip_lockout_hours		= 24;
    

	
	function Likee()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		$this->EE->db->cache_off();	
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	L I K E
 	//
 	//		P A R A M E T E R S
    //    	------------------------
    //    		* entry_id
    //    		* url_title
    //          * comment_id
    //    		* like_text 	(optional)
    //    		* dislike_text 	(optional)
    //    		* like_class	(optional)
    //    		* dislike_class	(optional)
    //
   	//		V A R I A B L E S
    //    	------------------------------
    //    		* like
    //    		* dislike
    //    		* like_count
    //    		* dislike_count
    //			* my_feeling
    //			* like_score
    //    		
    //    	V A R I A B L E   P A I R S
    //    	------------------------------
    //    		* remove_after_liking
    //			* show_after_liking
    //
 	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function like(){
		
		$output="";
		
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Set Parameters
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        	if ($this->EE->TMPL->fetch_param("like_text")){
        		$this->text_like = $this->EE->TMPL->fetch_param("like_text");
        	}
			
			if ($this->EE->TMPL->fetch_param("dislike_text")){
				$this->text_dislike = $this->EE->TMPL->fetch_param("dislike_text");
			}
			
			if ($this->EE->TMPL->fetch_param("like_class")){
				$this->class_like = $this->EE->TMPL->fetch_param("like_class");
			}
			
			if ($this->EE->TMPL->fetch_param("dislike_class")){
				$this->class_dislike = $this->EE->TMPL->fetch_param("dislike_class");
			}
			
			// Set ID based on one of the following
	    	// entry_id, url_title or comment_id
	    	if ($this->EE->TMPL->fetch_param("comment_id")){
	    		$this->idType = 'comment';
	    		$this->id = $this->EE->TMPL->fetch_param("comment_id");
	    		$this->isComment = true;
	    	} elseif ($this->EE->TMPL->fetch_param("entry_id")) {
	        	$this->id = $this->EE->TMPL->fetch_param('entry_id');
	        } else {
	        	$this->EE->db->select("entry_id");
	        	$this->EE->db->where('url_title', $this->EE->TMPL->fetch_param("url_title"));
	        	$q = $this->EE->db->get("channel_titles",1,0); 
	        		
	        	if ($q->num_rows() > 0){
	        		$row = $q->row();
	        		$this->id = $row->entry_id;
	        	}
	        	$q->free_result();
	        }
		
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Like/Dislike Counts
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	        // Create a count of the number of likes for this particular entry
	        	$this->EE->db->select("*");
	        	$this->EE->db->where($this->idType."_id", $this->id);
	        	$this->EE->db->where("liked", "1");
	        	$q = $this->EE->db->get("likee");
				$this->like_count = $q->num_rows;
				$q->free_result();
			
			// Create a count for the number of dislikes for this particular entry
				$this->EE->db->select("*");
	        	$this->EE->db->where($this->idType."_id", $this->id);
	        	$this->EE->db->where("disliked", "1");
	        	$q = $this->EE->db->get("likee");
				$this->dislike_count = $q->num_rows;
				$q->free_result();
			
			// Create a like score by subtracting the dislike count from the like count
				$this->like_score = $this->like_count - $this->dislike_count;
				
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Cookie Info
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
			
			// Has this person liked/disliked this already?
		        $prev_liked_bool = false;
		        $prev_liked_type = "";
		        
		        if (isset($_COOKIE['exp_likee'])){
		        	$this->EE->db->select("liked");
		        	$this->EE->db->where($this->idType."_id", $this->id);
		        	$this->EE->db->where("cookie_uid", $_COOKIE['exp_likee']);
		        	$q = $this->EE->db->get("likee");
		        	if ($q->num_rows > 0){
		        		$row = $q->row();
		        		$prev_liked_bool = true;
						if ($row->liked == 1){
							$prev_liked_type = "liked";
						} else {
							$prev_liked_type = "disliked";
						}
		        	}
		        	$q->free_result();
		        }
		        	
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Logged-In User Info
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        
        	// Check to see if the user is logged in
		        if ($this->EE->session->userdata['member_id'] > 0) {
		    		$this->member_id = $this->EE->session->userdata['member_id'];
		    	}
		
			// If they are logged in check for an entry in the DB
				if ($this->member_id){
					$this->EE->db->select("liked");
		        	$this->EE->db->where($this->idType."_id", $this->id);
		        	$this->EE->db->where("member_id", $this->member_id);
		        	$q = $this->EE->db->get("likee");
		        	if ($q->num_rows > 0){
		        		$row = $q->row();
						$prev_liked_bool = true;
						if ($row->liked == 1){
							$prev_liked_type = "liked";
						} else {
							$prev_liked_type = "disliked";
						}
					}
				}			
		
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Template Single Variables
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
			
			// If available, set current member's feeling	
				if (strlen($prev_liked_type) > 0){
					if ($prev_liked_type == "liked"){
						$this->my_feeling = $this->text_like;
					} else {
						$this->my_feeling = $this->text_dislike;
					}
				}
				
			// Make some decisions about output based on member's like-status
				if ($prev_liked_bool){
					if (strlen($prev_liked_type) > 0){
						if ($prev_liked_type == "liked"){
							$like_text = "<strong>".$this->text_like."</strong>";
							$dislike_text = "<span>".$this->text_dislike."</span>";
						} else {
							$dislike_text = "<strong>".$this->text_dislike."</strong>";
							$like_text = "<span>".$this->text_like."</span>";
						}
					} else {
						$like_text = $this->text_like;
						$dislike_text = $this->text_dislike;
					}
					 
				} else {
					$like_text 		= $this->wrapLikes($this->text_like);  
		    		$dislike_text 	= $this->wrapLikes($this->text_dislike,'dislike'); 
				}
				
			$like_count 	= $this->wrapCounts($this->like_count,'like');
	        $dislike_count 	= $this->wrapCounts($this->dislike_count,'dislike');
	        $like_score 	= $this->wrapCounts($this->like_score,'score');
			
			// Set array for single variables			
				$vars[] = array(
						'like' 				=> $like_text,
						'dislike' 			=> $dislike_text,
						'like_count'		=> $like_count,
    					'dislike_count'		=> $dislike_count,
    					'like_score'		=> $like_score,
    					'my_feeling'		=> $this->my_feeling
				);
				
			// Parse single variables
				$output = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);
				
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Template Variable Pairs
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        
	    	// Loop through the variable pairs for this entry
	    		foreach ($this->EE->TMPL->var_pair as $k=>$v){

	    		// Switch through the different variable pair types
	    		switch ($k) {
	    		
	    			// remove_after_liking Variable Pair
					case "remove_after_liking":
						if ($prev_liked_bool){
							// If the entry has been liked/disliked before
							$replaceText="";
		    				$output = preg_replace("/{remove_after_liking}(.*?){\/remove_after_liking}/s", $replaceText, $output);		
	    				} else {
	    					// If the entry has NOT been liked/disliked before
							$replaceText = ($this->isComment ? "likeeReplace_c_" : "likeeReplace_");
	    					$output = preg_replace("/{".$k."}(.*?){\/".$k."}/s", "<span id='".$replaceText.$this->id."'>$1</span>", $output);
	    					
	    				
	    				}	
	    				break;
	    			
	    			// show_after_liking Variable Pair	
	    			case "show_after_liking":
	    				if ($prev_liked_bool){
							// If the entry has been liked/disliked before
	    					$output = preg_replace("/{".$k."}(.*?){\/".$k."}/s", "$1", $output);
		    					
	    				} else {
	    					// If the entry has NOT been liked/disliked before
	    					$replaceText = ($this->isComment ? "likeeShow_c_" : "likeeShow_");
	    					$output = preg_replace("/{".$k."}(.*?){\/".$k."}/s", "<span style='display:none' id='".$replaceText.$this->id."'>$1</span>", $output);
							
	    				}
	    				
	    				break;
	    				
					}
					
				} 
		return $output;
	}
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	J A V A S C R I P T
 	// 
 	//	To be added in the head section of any template
 	//	containing this module. This function also includes
 	//	all AJAX callback responses.
	//
    //    	P A R A M E T E R S
    //    	------------------------
    //    		* skip_jquery 		(optional)
    //			* ip_lockout_hours	(optional)
    //    
 	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	
	function js(){
	
		// Load CI Helpers
		$this->EE->load->helper('url');
		$this->EE->load->helper('cookie');
		
		if ($this->EE->TMPL->fetch_param("ip_lockout_hours")>0){
			$this->ip_lockout_hours = $this->EE->TMPL->fetch_param('ip_lockout_hours');
		}
		
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // AJAX Callback Response
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        
	    	// If this is an AJAX callback, do your thang.
	    	if ($this->EE->input->post("likee") || $this->EE->input->post("dislikee")){
	    		
	    		// Check to see if the user is logged in
			        if ($this->EE->session->userdata['member_id'] > 0) {
			    		$this->member_id = $this->EE->session->userdata['member_id'];
			    	}
	    		
	    		// Set Cookie
		    		if (isset($_COOKIE['exp_likee'])){
		    			$cookie = $_COOKIE['exp_likee'];
		    		} else {
		    			$cookie = md5(uniqid());
		    			setcookie("exp_likee", $cookie, time()+(60*60*24*365), "/");
		    		}
		    		
		    		if ($this->EE->input->post("isCom")=="true"){
		    			$entry_id="";
		    			$comment_id=$this->EE->input->post("entry_id");
		    			$type="comment";
		    		} else {
		    			$comment_id="";
		    			$entry_id=$this->EE->input->post("entry_id");
		    			$type="entry";
		    		}
		    		
		    	// Check to see if a user from this IP has voted on 
	    		// this particular item in the last 24 hours.
	    			$this->EE->db->select("*");
		        	$this->EE->db->where($type."_id", $this->EE->input->post("entry_id"));
		        	$this->EE->db->where("ip_address", $this->EE->input->ip_address());
		        	$this->EE->db->where("date <=", "SUBDATE( CURRENT_DATE, INTERVAL ".$this->ip_lockout_hours." HOUR)");
		        	$q = $this->EE->db->get("likee");
		        	if ($q->num_rows == 0){
	    				if ($this->EE->input->post("likee")){
	    					$data = array(
								'liked' 		=> '1', 
								'disliked' 		=> '0', 
								'entry_id'	 	=> $entry_id,
								'comment_id'	=> $comment_id,
								'member_id'		=> $this->member_id,
								'date'			=> date('Y-m-d H:i:s'),
								'ip_address'	=> $this->EE->input->ip_address(),
								'cookie_uid'	=> $cookie
			    			);
	    				} else {
	    					$data = array(
								'liked' 		=> '0', 
								'disliked' 		=> '1', 
								'entry_id'	 	=> $entry_id,
								'comment_id'	=> $comment_id,
								'member_id'		=> $this->member_id,
								'date'			=> date('Y-m-d H:i:s'),
								'ip_address'	=> $this->EE->input->ip_address(),
								'cookie_uid'	=> $cookie
			    			);
	    				}
						
						$this->EE->db->insert('likee', $data); 
	    			}
					print("Do you LikEE? http://fromtheoutfit.com/likee");
	    			$q->free_result();
	    			exit;	    		
	    	}
	    	
	    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Insert Javascript
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
    		$output = "";
    		
	    	// If this is not an AJAX callback, then insert your Javascript
	    	if ($this->EE->TMPL->fetch_param('skip_jquery') == false) {
	        	$output .= '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>';
	        }
	        $output .= '<script type="text/javascript">
	        					$(document).ready(function() {

	        					    var ee_xid = "' . $this->EE->functions->add_form_security_hash('{XID_HASH}') . '";

									$("*[id^=likee_]").click(function(){
										var ids = $(this).attr("id").split("_");
										if (ids[1]=="c"){
											id=ids[2];
											c="c_";
											isCom="true";
										} else {
											id=ids[1];
											c="";
											isCom="false";
										}

										$.ajax({
	   										type: "POST",
	   										url: "'.$this->EE->input->server("REQUEST_URI").'",
	   										data: "XID="+ee_xid+"&likee=true&entry_id="+id+"&isCom="+isCom,
	   										success: function(msg,textStatus,request){
	     										//console.log( "Data Saved: " + msg );
	     									    ee_xid = request.getResponseHeader("X-EEXID");
	   										}
	 									});
	 									
	 									$(this).replaceWith("<strong>"+$(this).html()+"</strong>");
	 									$("#dislikee_"+c+id).replaceWith("<span>"+$("#dislikee_"+c+id).html()+"</span>");
	 									$("#scoreeCount_"+c+id).html(parseInt($("#scoreeCount_"+c+id).html())+1);
	 									$("#likeeCount_"+c+id).html(parseInt($("#likeeCount_"+c+id).html())+1);
	 									$("#likeeReplace_"+c+id).replaceWith("");
	 									$("#likeeShow_"+c+id).replaceWith($("#likeeShow_"+c+id).html());
									});
									
									$("*[id^=dislikee_]").click(function(){
										var ids = $(this).attr("id").split("_");
										if (ids[1]=="c"){
											id=ids[2];
											c="c_";
											isCom="true";
										} else {
											id=ids[1];
											c="";
											isCom="false";
										}
										$.ajax({
	   										type: "POST",
	   										url: "'.$this->EE->input->server("REQUEST_URI").'",
	   										data: "XID="+ee_xid+"&dislikee=true&entry_id="+id+"&isCom="+isCom,
	   										success: function(msg,textStatus,request){
	     										//alert( "Data Saved: " + msg );
	     										ee_xid = request.getResponseHeader("X-EEXID");
	   										}
	 									});
	 									
	 									$(this).replaceWith("<strong>"+$(this).html()+"</strong>");
	 									$("#likee_"+c+id).replaceWith("<span>"+$("#likee_"+c+id).html()+"</span>");
	 									$("#scoreeCount_"+c+id).html(parseInt($("#scoreeCount_"+c+id).html())-1);
	 									$("#dislikeeCount_"+c+id).html(parseInt($("#dislikeeCount_"+c+id).html())+1);
	 									$("#likeeReplace_"+c+id).replaceWith("");
	 									$("#likeeShow_"+c+id).replaceWith($("#likeeShow_"+c+id).html());
									});
								
								});	
	        			  </script>';
	       
	        return $output;

	}
	
    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	L I K E S
 	//
 	//		P A R A M E T E R S
    //    	------------------------
    //			* channel			
    //    		* sort		 		(optional)	(asc|desc)
    //    		* limit				(optional)	(integer)
    //    		* type				(optional)	(liked|disliked)
    //			* only_current_user	(optional)	(true|false)
    //
   	//		V A R I A B L E S
    //    	------------------------------
    //    		* count
    //    		* entry_id
    //    		* opposite_count
    //			* title
    //			* url_title
    //			* channel_id
    //
 	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	
 	function likes(){
 		
 		$output="";
 		
 		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Set parameters
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        
			if ($this->EE->TMPL->fetch_param('type') == "liked" || $this->EE->TMPL->fetch_param('type') == "disliked"){
				$this->type = $this->EE->TMPL->fetch_param('type');
			}
			
			if ($this->EE->TMPL->fetch_param('sort') == "asc"){
				$this->query_sort = $this->EE->TMPL->fetch_param('sort');
			}
			
			if ($this->EE->TMPL->fetch_param('only_current_user') == "true"){
				$this->only_current_user = $this->EE->TMPL->fetch_param('only_current_user');
			}
			
			if ($this->EE->TMPL->fetch_param("limit")>0){
				$this->limit = $this->EE->TMPL->fetch_param('limit');
			}
 		
 		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Set Channels
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	

	    	if (trim($this->EE->TMPL->fetch_param('channel')) != ''){
	    		$channels = explode("|", $this->EE->TMPL->fetch_param('channel'));
	    		
    			$this->EE->db->select("channel_id, channel_name");
	        	$this->EE->db->where_in('channel_name', $channels);
	        	$q = $this->EE->db->get("channels");
	    			    		
	    		if ($q->num_rows > 0){
	    			foreach($q->result() as $row){
	    				$this->channel_array[$row->channel_id] = $row->channel_name;
	    			}
	    		}
	    		$q->free_result();
	    	}
	    	
	    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // User Information
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	    	
	    	if ($this->EE->session->userdata['member_id'] > 0) {
	    		// If the user is logged in, let's use that
	    		$this->member_id = $this->EE->session->userdata['member_id'];
	    	} else {
	    		// Otherwise, let's grab that cookie
	    		if (isset($_COOKIE['exp_likee'])){
	    			$this->cookie_uid = $_COOKIE['exp_likee'];
	    		}
	    	}
	    	
	    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Build Main SQL Query
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	    	
    		$this->EE->db->select("COUNT(l.".$this->type.") as count, 
	    							l.entry_id,
									ct.title,
									ct.url_title,
									ct.channel_id");
    		$this->EE->db->from("likee AS l");
    		$this->EE->db->join("channel_titles AS ct", "l.entry_id=ct.entry_id", "LEFT OUTER");
    		$this->EE->db->join("channels AS c", "c.channel_id=ct.channel_id", "LEFT OUTER");
    		$this->EE->db->where("l.".$this->type,"1");
    		$this->EE->db->where("l.entry_id >", 0);
    		$this->EE->db->group_by("l.entry_id");
    		$this->EE->db->order_by("count", $this->query_sort);
    		
    		// If channels are specified, narrow it down
    		if (sizeof($this->channel_array)>0){
    			$this->EE->db->where_in("c.channel_name", $channels);
    		}
    		
	    	// Narrow the results to just the current user, if specified
	    	if ($this->only_current_user){
		    	if ($this->member_id){
		    		$this->EE->db->where("l.member_id", $this->member_id);
		    	} elseif (strlen($this->cookie_uid)>0){
		    		$this->EE->db->where("l.cookie_uid", $this->cookie_uid);
		    	}
	    	}	
	    	 		 	
 		 	// If a limit has been passed, apply it to either type of query
	    	if ($this->limit > 0){
	    		$this->EE->db->limit($this->limit);
	    	}
	    	
	    	$q = $this->EE->db->get();
	    	if ($q->num_rows > 0){
    			foreach($q->result() as $row){
    			
    				//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
       				// Find opposite type count
        			//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        			
    				$opposite_type = ($this->type == 'liked' ? 'disliked' : 'liked');
    				$this->EE->db->select("count(".$opposite_type.") AS opposite_count");
    				$this->EE->db->where("entry_id", $row->entry_id);
    				$this->EE->db->where($opposite_type, "1");
    				$qOpp = $this->EE->db->get("likee");
    				$rowOpp = $qOpp->row();
    				$opposite_count = $rowOpp->opposite_count;
    				$qOpp->free_result();
	    			
	    			// Set array for single variables			
					$vars[] = array(
							'count'				=> $row->count,
    						'entry_id'			=> $row->entry_id,
    						'opposite_count'	=> $opposite_count,
    						'title'				=> $row->title,
    						'url_title'			=> $row->url_title,
    						'channel_id'		=> $row->channel_id
					
					);
				
					// Parse single variables
					$output = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);	
    			
    			}
	    	}
	    	
	    	
	    	$q->free_result();
	    	return $output;
 	}
	
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	C O M M E N T S
 	//
 	//		P A R A M E T E R S
    //    	------------------------
    //			* channel			(optional)
    //			* entry_id			(optional)
    //			* url_title			(optional)			
    //    		* sort		 		(optional)	(asc|desc)
    //    		* limit				(optional)	(integer)
    //    		* type				(optional)	(liked|disliked)
    //			* only_current_user	(optional)	(true|false)
    //
   	//		V A R I A B L E S
    //    	------------------------------
    //    		* count
    //    		* comment_id
    //			* entry_id
    //    		* opposite_count
    //			* name
    //			* email
    //			* url
    //			* location
    //			* comment
    //
 	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	
 	function comments(){
 		
 		$output="";
		
 		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Set parameters
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

			if ($this->EE->TMPL->fetch_param('type') == "liked" || $this->EE->TMPL->fetch_param('type') == "disliked"){
				$this->type = $this->EE->TMPL->fetch_param('type');
			}
			
			if ($this->EE->TMPL->fetch_param('sort') == "asc"){
				$this->query_sort = $this->EE->TMPL->fetch_param('sort');
			}
				
			
			if ($this->EE->TMPL->fetch_param('entry_id')) {
	        	$this->id = $this->EE->TMPL->fetch_param('entry_id');
	        } elseif ($this->EE->TMPL->fetch_param('url_title')) {
	        	$this->EE->db->select("entry_id");
	        	$this->EE->db->where("url_title", $this->EE->TMPL->fetch_param("url_title"));
	        	$q = $this->EE->db->get("channel_titles",1,0);
	        
	        	if ($q->num_rows > 0){
	        		$row = $q->row();
					$this->id = $row->entry_id;
				}
				$q->free_result();
	        }
			
			if ($this->EE->TMPL->fetch_param('only_current_user') == "true"){
				$this->only_current_user = $this->EE->TMPL->fetch_param('only_current_user');
			}
			
			if ($this->EE->TMPL->fetch_param("limit")>0){
				$this->limit = $this->EE->TMPL->fetch_param('limit');
			}
		
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Set Channels
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>	

	    	if (trim($this->EE->TMPL->fetch_param('channel')) != ''){
	    		$channels = explode("|", $this->EE->TMPL->fetch_param('channel'));
	    		
    			$this->EE->db->select("channel_id, channel_name");
	        	$this->EE->db->where_in('channel_name', $channels);
	        	$q = $this->EE->db->get("channels");
	    			    		
	    		if ($q->num_rows > 0){
	    			foreach($q->result() as $row){
	    				$this->channel_array[$row->channel_id] = $row->channel_name;
	    			}
	    		}
	    		$q->free_result();
	    	}
	    	
	    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // User Information
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
	    	
	    	if ($this->EE->session->userdata['member_id'] > 0) {
	    		// If the user is logged in, let's use that
	    		$this->member_id = $this->EE->session->userdata['member_id'];
	    	} else {
	    		// Otherwise, let's grab that cookie
	    		if (isset($_COOKIE['exp_likee'])){
	    			$this->cookie_uid = $_COOKIE['exp_likee'];
	    		}
	    	}
		
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Build Main SQL Query
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
    	   	$this->EE->db->select("COUNT(l.".$this->type.") AS count,
    	   							l.comment_id,
    	   							c.name,
    	   							c.email,
    	   							c.url,
    	   							c.location,
    	   							c.comment,
    	   							c.entry_id
    	   						");
    	   	$this->EE->db->from("likee AS l");
    	   	$this->EE->db->join("comments AS c", "l.comment_id=c.comment_id", "LEFT OUTER");
    	   	$this->EE->db->join("channels AS ch", "c.channel_id=ch.channel_id", "LEFT OUTER");
    	   	$this->EE->db->where("l.".$this->type, 1);
    	   	$this->EE->db->where("l.comment_id >", 0);
    	   	$this->EE->db->group_by("l.comment_id");
    		$this->EE->db->order_by("count", $this->query_sort);
    	   	
			
			// If channels are specified, narrow it down
    		if (sizeof($this->channel_array)>0){
    			$this->EE->db->where_in("ch.channel_name", $channels);
    		}
			
			// Narrow the results to a single entry
			if ($this->id){
				$this->EE->db->where("c.entry_id", $this->id);
			}
    		   				    	
	    	// Narrow the results to just the current user, if specified
	    	if ($this->only_current_user){
		    	if ($this->member_id){
		    		$this->EE->db->where("l.member_id", $this->member_id);
		    	} elseif (strlen($this->cookie_uid)>0){
		    		$this->EE->db->where("l.cookie_uid", $this->cookie_uid);
		    	}
	    	}	
	    				
		    // If a limit has been passed, apply it to either type of query
	    	if ($this->limit > 0){
	    		$this->EE->db->limit($this->limit);
	    	}
	    	
	    	$q = $this->EE->db->get();
	    	
	    	if ($q->num_rows > 0){
    			foreach($q->result() as $row){
    			
    				//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
       				// Find opposite type count
        			//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        			
    				$opposite_type = ($this->type == 'liked' ? 'disliked' : 'liked');
    				$this->EE->db->select("count(".$opposite_type.") AS opposite_count");
    				$this->EE->db->where("comment_id", $row->comment_id);
    				$this->EE->db->where($opposite_type, "1");
    				$qOpp = $this->EE->db->get("likee");
    				$rowOpp = $qOpp->row();
    				$opposite_count = $rowOpp->opposite_count;
    				$qOpp->free_result();
    			
    				// Set array for single variables			
					$vars[] = array(
    						'count'				=> $row->count,
    						'comment_id'		=> $row->comment_id,
    						'entry_id'			=> $row->entry_id,
    						'opposite_count'	=> $opposite_count,
    						'name'				=> $row->name,
    						'email'				=> $row->email,
    						'url'				=> $row->url,
    						'location'			=> $row->location,
    						'comment'			=> $row->comment
					);
				
					// Parse single variables
					$output = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);    	
    			}
    		}
    		
    		$q->free_result();
	    	return $output;		
 	
 	}
	
 	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	L I K E R S
 	//
 	//		P A R A M E T E R S
    //    	------------------------
    //			* entry_id			
    //			* url_title						
    //    		* comment_id
    //			* type				(optional)	(liked|disliked|all)		
    //			* limit 			(optional) 		
    //
   	//		V A R I A B L E S
    //    	------------------------------
	//			* likee_member_id
	//			* likee_username
	//			* likee_screen_name
	//			* likee_email
	//			* likee_url
	//			* likee_location
	//			* likee_occupation
	//			* likee_interests
	//			* likee_bday_d
	//			* likee_bday_m
	//			* likee_bday_y
	//			* likee_aol_im
	//			* likee_yahoo_im
	//			* likee_msn_im
	//			* likee_icq
	//			* likee_bio
	//			* likee_signature
	//			* likee_avatar_filename
	//			* likee_avatar_width
	//			* likee_avatar_height
	//			* likee_photo_filename
	//			* likee_photo_width
	//			* likee_photo_height
    //
 	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	
 	function likers(){
 	
 		$output="";
 		
 		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Set parameters
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
			$params = array();

			// Set ID based on one of the following
	    	// entry_id, url_title or comment_id
	    	if ($this->EE->TMPL->fetch_param("comment_id")){
	    		$this->idType = 'comment';
	    		$this->id = $this->EE->TMPL->fetch_param("comment_id");
	    		$this->isComment = true;
	    	} elseif ($this->EE->TMPL->fetch_param("entry_id")) {
	        	$this->id = $this->EE->TMPL->fetch_param('entry_id');
	        } elseif ($this->EE->TMPL->fetch_param("url_title")) {
	        	$this->EE->db->select("entry_id");
	        	$this->EE->db->where('url_title', $this->EE->TMPL->fetch_param("url_title"));
	        	$q = $this->EE->db->get("channel_titles",1,0); 
	   	
	        	if ($q->num_rows() > 0){
	        		$row = $q->row();
	        		$this->id = $row->entry_id;
	        	}
	        	$q->free_result();
	        }
				
			if ($this->EE->TMPL->fetch_param("limit")>0){
				$this->limit = $this->EE->TMPL->fetch_param('limit');
			}
			
			if ($this->EE->TMPL->fetch_param('type') == "liked" || $this->EE->TMPL->fetch_param('type') == "disliked" || $this->EE->TMPL->fetch_param('type') == "all"){
				$this->type = $this->EE->TMPL->fetch_param('type');
			} else {
				$this->type = "all";
			}
 	
 		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        // Build Main SQL Query
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
        	$this->EE->db->select("l.member_id AS likee_member_id,
									m.username AS likee_username,
									m.screen_name AS likee_screen_name,
									m.email AS likee_email,
									m.url AS likee_url,
									m.location AS likee_location,
									m.occupation AS likee_occupation,
									m.interests AS likee_interests,
									m.bday_d AS likee_bday_d,
									m.bday_m AS likee_bday_m,
									m.bday_y AS likee_bday_y,
									m.aol_im AS likee_aol_im,
									m.yahoo_im AS likee_yahoo_im,
									m.msn_im AS likee_msn_im,
									m.icq AS likee_icq,
									m.bio AS likee_bio,
									m.signature AS likee_signature,
									m.avatar_filename AS likee_avatar_filename,
									m.avatar_width AS likee_avatar_width,
									m.avatar_height AS likee_avatar_height,
									m.photo_filename AS likee_photo_filename,
									m.photo_width AS likee_photo_width,
									m.photo_height AS likee_photo_height
        						");
        	$this->EE->db->from("likee AS l");
        	$this->EE->db->join("members AS m", "l.member_id=m.member_id", "LEFT OUTER");
        	$this->EE->db->where("l.member_id >", 0);
        	$this->EE->db->distinct();

			if ($this->id > 0){
				$this->EE->db->where("l.".$this->idType."_id", $this->id);
    	    }
    	    	 
    		if ($this->type !== "all"){
    			$this->EE->db->where($this->type, 1);
    		}
					    	
	    	// If a limit has been passed, apply it to either type of query
	    	if ($this->limit > 0){
	    		$this->EE->db->limit($this->limit);
	    	}
 			
 			$q = $this->EE->db->get();
	    	
	    	if ($q->num_rows > 0){
    			foreach($q->result() as $row){
    			
    				// Set array for single variables			
					$vars[] = array(
							'likee_member_id'	   	=> $row->likee_member_id,
							'likee_username'	   	=> $row->likee_username,
							'likee_screen_name'	   	=> $row->likee_screen_name,
							'likee_email'		   	=> $row->likee_email,
							'likee_url'			   	=> $row->likee_url,
							'likee_location'	   	=> $row->likee_location,
							'likee_occupation'	   	=> $row->likee_occupation,
							'likee_interests'	   	=> $row->likee_interests,
							'likee_bday_d'		   	=> $row->likee_bday_d,
							'likee_bday_m'		   	=> $row->likee_bday_m,
							'likee_bday_y'		   	=> $row->likee_bday_y,
							'likee_aol_im'		   	=> $row->likee_aol_im,
							'likee_yahoo_im'	   	=> $row->likee_yahoo_im,
							'likee_msn_im'		   	=> $row->likee_msn_im,
							'likee_icq'			   	=> $row->likee_icq,
							'likee_bio'			   	=> $row->likee_bio,
							'likee_signature'	   	=> $row->likee_signature,
							'likee_avatar_filename'	=> $row->likee_avatar_filename,
							'likee_avatar_width'   	=> $row->likee_avatar_width,
							'likee_avatar_height'  	=> $row->likee_avatar_height,
							'likee_photo_filename' 	=> $row->likee_photo_filename,
							'likee_photo_width'	   	=> $row->likee_photo_width,
							'likee_photo_height'   	=> $row->likee_photo_height
					);
				
					// Parse single variables
					$output = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars); 
    
    			}
    		}	
    	return $output;			
 	}	

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	W R A P   L I K E S 
 	// 
 	//	Simple function to wrap any like/dislike tags
 	//	in anchors and assign ids/classes accordingly
 	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>

	function wrapLikes($text, $type="like"){
		$class = ($type == 'like' ? $this->class_like : $this->class_dislike);
		$id = ($type == 'like' ? "likee" : "dislikee");
		if ($this->isComment){
			$idText = $id."_c_".$this->id;
		} else {
			$idText = $id."_".$this->id;
		}
		
		return "<a href='javascript:;' class='".$class."' id='".$idText."'>".$text."</a>";
	}	
	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	//	W R A P   C O U N T S
 	// 
 	//	Simple function to wrap any like_count/dislike_count
 	//	tags in spans and assign ids accordingly
 	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~>>
 	
 	function wrapCounts($text, $type="like"){
		
		switch ($type) {
    		case 'like':
        		$id = 'likee';
        		break;
    		case 'score':
        		$id = 'scoree';
        		break;
    		default:
       			$id = 'dislikee';
		}
		
		if ($this->isComment){
			$idText = $id."Count_c_".$this->id;
		} else {
			$idText = $id."Count_".$this->id;
		}
		return "<span id='".$idText."'>".$text."</span>";
	}
	
	
	
}