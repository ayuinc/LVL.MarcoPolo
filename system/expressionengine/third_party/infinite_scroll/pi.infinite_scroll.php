<?php

/*
=====================================================
 This ExpressionEngine add-on was created by Laisvunas
 - http://devot-ee.com/developers/laisvunas
=====================================================
 Copyright (c) Laisvunas
=====================================================
 This is commercial Software.
 One purchased license permits the use this Software on the SINGLE website.
 Unless you have been granted prior, written consent from Laisvunas, you may not:
 * Reproduce, distribute, or transfer the Software, or portions thereof, to any third party
 * Sell, rent, lease, assign, or sublet the Software or portions thereof
 * Grant rights to any other person
=====================================================
*/

$plugin_info = array(
						'pi_name'			=> 'Infinite Scroll',
						'pi_version'		=> '1.2.1',
						'pi_author'			=> 'Laisvunas',
						'pi_author_url'		=> 'http://devot-ee.com/developers/laisvunas',
						'pi_description'	=> 'Allows you to implement infinite scroll functionality in ExpressionEngine. Any ExpressionEngine tag outputting pagination links is supported!',
						'pi_usage'			=> Infinite_scroll::usage()
					);

class Infinite_scroll {
  
  var $load_next_chunk_at_percent = 97;
  
  // ----------------------------------------
  //  Constructor
  // ----------------------------------------
  
  function Infinite_scroll()
  {
    $this->EE =& get_instance();
  }
  // END FUNCTION
  
  function next_chunk_link()
  {    
    // Fetch the tagdata
    $tagdata = trim($this->EE->TMPL->tagdata);
    //echo '$tagdata: ['.$tagdata.']'.PHP_EOL;

    // fetch params
    $pagination_symbol = $this->EE->TMPL->fetch_param('pagination_symbol') ? $this->EE->TMPL->fetch_param('pagination_symbol') : 'P';
    $tag_count = $this->EE->TMPL->fetch_param('tag_count'); // added by exp:ajax_pagination:wrapper tag
    
    // Define variables

    if ($tagdata)
    {
      // add onclick attributes to the "Next" link
      $tagdata = $this->_change_pagination_links($tagdata, $pagination_symbol, $tag_count);  
    }

    return $tagdata;
  }
  // END FUNCTION
  
  function next_chunk_data()
  {    
    // Fetch the tagdata
    $tagdata = trim($this->EE->TMPL->tagdata);
    //echo '$tagdata: ['.$tagdata.']'.PHP_EOL;
    
    // fetch params
    $pagination_symbol = $this->EE->TMPL->fetch_param('pagination_symbol') ? $this->EE->TMPL->fetch_param('pagination_symbol') : 'P';
    $tag_count = $this->EE->TMPL->fetch_param('tag_count'); // added by exp:ajax_pagination:wrapper tag
    $is_ajax = $this->EE->TMPL->fetch_param('is_ajax'); // added by exp:ajax_pagination:wrapper tag
    
    if ($tagdata)
    {
      $auto_path = rtrim($tagdata, '/');
      // create div having id="infinite_scroll_next_chunk" and data-pagination-number="P30"
      $tagdata = $this->_create_next_chunk_indicator($auto_path, $pagination_symbol, $tag_count, $is_ajax);
    }
    
    return $tagdata;
  }
  // END FUNCTION
  
  function _create_next_chunk_indicator($auto_path, $pagination_symbol, $tag_count, $is_ajax)
  {
    //echo '$auto_path: ['.$auto_path.']<br><br>'.PHP_EOL;
    $last_segment = end(explode('/', $auto_path));
    //echo '$last_segment: ['.$last_segment.']<br><br>'.PHP_EOL;
    $pos = strpos($last_segment, $pagination_symbol);
    //echo '$pos: ['.$pos.']<br><br>'.PHP_EOL;
    if ($pos === 0)
    {
      $pagination_segment = $last_segment;
    }
    else
    {
      $pagination_segment = '';
    }
    
    ob_start(); 
?>

<script type="text/javascript">
//<![CDATA[

infiniteScrollData<?=@$tag_count?> = {}
infiniteScrollData<?=@$tag_count?>.continuous_scroll_pagination_segment = '<?=@$pagination_segment?>';

if (infiniteScroll<?=@$tag_count?>.ajax_container && infiniteScroll<?=@$tag_count?>.ajax_container.scrollHeight == infiniteScroll<?=@$tag_count?>.ajax_container.clientHeight) {
  infiniteScroll<?=@$tag_count?>.continuous_scroll();
}

//]]>
</script>

<?php
    $buffer = ob_get_contents();
    ob_end_clean(); 
    
    return $buffer;    
  }
  // END FUNCTION
  
  function wrapper()
  {    
    // Fetch the tagdata
    $tagdata = $this->EE->TMPL->tagdata;
    //echo '$tagdata: ['.$tagdata.']'.PHP_EOL;
    
    // fetch params
    $embed_template_url = $this->EE->TMPL->fetch_param('embed_template_url');
    $ajax_container = $this->EE->TMPL->fetch_param('ajax_container');
    $process_indicator = $this->EE->TMPL->fetch_param('process_indicator');
    $next_chunk_link = $this->EE->TMPL->fetch_param('next_chunk_link');
    $ajax_pagination_vars = $this->EE->TMPL->fetch_param('infinite_scroll_vars');
    $ajax_pagination_values = $this->EE->TMPL->fetch_param('infinite_scroll_values');
    $xid_element_id = $this->EE->TMPL->fetch_param('xid_element_id');
    
    // param "embed_template_url" is required
    if (!$embed_template_url)
    {
      echo 'ERROR! Parameter "embed_template_url" of exp:ajax_pagination tag must be defined!<br><br>'.PHP_EOL;;
    }
    // param "ajax_container" is required
    if (!$ajax_container)
    {
      echo 'ERROR! Parameter "ajax_container" of exp:ajax_pagination tag must be defined!<br><br>'.PHP_EOL;;
    }
    
    // form embed template URL
    $embed_template_url = $this->_parse_url_in_parameter($embed_template_url);
    
    // is template being loaded by AJAX?
    $is_ajax = ($this->EE->input->post('ajax') == 'yes') ? 'yes' : 'no';
    
    // embed vars received by AJAX call
    if ($is_ajax == 'yes')
    {
      
      $ajax_pagination_vars = $this->EE->input->post('ajax_pagination_vars');
      $ajax_pagination_values = $this->EE->input->post('ajax_pagination_values');
      //echo '$ajax_pagination_vars: ['.$ajax_pagination_vars.']<br><br>'.PHP_EOL;
      //echo '$ajax_pagination_values: ['.$ajax_pagination_values.']<br><br>'.PHP_EOL;
    }
    
    // js messages
    $js_messages = array();
    $js_messages['error_encountered_msg'] = 'An error was encountered: ';
    $js_messages['no_response_msg'] = 'No response from server.';
    $js_messages['no_container_msg'] = 'Ajax container not found.';
    
    // find how many exp:ajax_pagination tags there are on the page
    $tag_count = $this->_find_tag_count($ajax_pagination_vars, $ajax_pagination_values);
    
    $tagdata = str_replace(LD.'exp:infinite_scroll:next_chunk_link', LD.'exp:infinite_scroll:next_chunk_link tag_count="'.$tag_count.'" ', $tagdata);
    $tagdata = str_replace(LD.'exp:infinite_scroll:next_chunk_data', LD.'exp:infinite_scroll:next_chunk_data tag_count="'.$tag_count.'" is_ajax="'.$is_ajax.'" ', $tagdata);
    //echo '$tagdata: ['.$tagdata.']'.PHP_EOL;
    
    // parse embed vars
    $tagdata = $this->_parse_embed_vars($tagdata, $ajax_pagination_vars, $ajax_pagination_values);
    
    // fetch var pair {ajax_pagination_container_top}{/ajax_pagination_container_top}
    $ajax_pagination_container_top = $this->_getVariablePair('infinite_scroll_container_top', $tagdata);
    $ajax_pagination_container_top_tagdata = $ajax_pagination_container_top['value'];
    //echo '$ajax_pagination_container_top_tagdata: ['.$ajax_pagination_container_top_tagdata.']<br><br>'.PHP_EOL;
    
    // fetch var pair {ajax_pagination_container_bottom}{/ajax_pagination_container_bottom}
    $ajax_pagination_container_bottom = $this->_getVariablePair('infinite_scroll_container_bottom', $ajax_pagination_container_top['data_beneath']);
    $ajax_pagination_container_bottom_tagdata = $ajax_pagination_container_bottom["value"];
    //echo '$ajax_pagination_container_bottom_tagdata: ['.$ajax_pagination_container_bottom_tagdata.']<br><br>'.PHP_EOL;
    
    $ajax_tagdata = $ajax_pagination_container_bottom['data_above'];
    //echo '$ajax_tagdata: ['.$ajax_tagdata.']<br><br>'.PHP_EOL;
    
    if ($is_ajax == 'yes')
    {
      
      $out = $ajax_tagdata;
    }
    else
    {
      // form javascript
      $js = $this->_js($tag_count, $js_messages, $process_indicator, $ajax_pagination_vars, $ajax_pagination_values, $embed_template_url, $ajax_container, $next_chunk_link, $xid_element_id);
      $out = $ajax_pagination_container_top_tagdata.$js.$ajax_tagdata. $ajax_pagination_container_bottom_tagdata;
    }
    
    return $out;
  }
  // END FUNCTION
  
  function _find_tag_count($ajax_pagination_vars, $ajax_pagination_values)
  {
    $tag_count = 1;
    
    if ($ajax_pagination_vars AND $ajax_pagination_values)
    {
      $ajax_pagination_vars_arr = explode('|', $ajax_pagination_vars);
      $ajax_pagination_values_arr = explode('|', $ajax_pagination_values);
      
      for ($i = 0; $i < count($ajax_pagination_vars_arr); $i++)
      {
        if ($ajax_pagination_vars_arr[$i] == 'tag_count' AND $ajax_pagination_values_arr[$i])
        {
          $tag_count = $ajax_pagination_values_arr[$i];
        }
      }
    }
    
    return $tag_count;
  }
  // END FUNCTION
  
  function _parse_embed_vars($data, $ajax_pagination_vars, $ajax_pagination_values)
  {    
    if ($ajax_pagination_vars AND $ajax_pagination_values)
    {
      
      $ajax_pagination_vars_arr = explode('|', $ajax_pagination_vars);
      //var_dump($ajax_pagination_vars_arr);
      $ajax_pagination_values_arr = explode('|', $ajax_pagination_values);
      //var_dump($ajax_pagination_values_arr);
      
      // output embed vars
      $conds = array();
      for ($i = 0; $i < count($ajax_pagination_vars_arr); $i++)
      {
        $var_name = 'infinite_scroll_embed_'.$ajax_pagination_vars_arr[$i];
        $conds[$var_name] = (isset($ajax_pagination_values_arr[$i]) AND $ajax_pagination_values_arr[$i]) ? $ajax_pagination_values_arr[$i] : '';
        //var_dump($conds);
        $conds[$var_name] = str_replace(':PIPE:', '|', $conds[$var_name]);
        $data = $this->EE->TMPL->swap_var_single($var_name, $conds[$var_name], $data);
        //var_dump($conds);
      }
      // prepare conditionals
      $data = $this->EE->functions->prep_conditionals($data, $conds);
      // clean undefined embed vars
      $pattern =  '/\{infinite_scroll_embed_(.*)\}/Usi';
      $data = preg_replace($pattern, '', $data);
    }
    
    return $data;
  }
  // END FUNCTION
  
  function _change_pagination_links($pagination_links, $pagination_symbol, $tag_count)
  {
    $pattern = '/href=\"(.*)\"/Usi';
    
    while (preg_match($pattern, $pagination_links, $matches))
    {
      $url = trim(@$matches[1], '/');
      //echo '$url: ['.$url.']<br><br>'.PHP_EOL;
      $last_segment = end(explode('/', $url));
      //echo '$last_segment: ['.$last_segment.']<br><br>'.PHP_EOL;
      $pos = strpos($last_segment, $pagination_symbol);
      //echo '$pos: ['.$pos.']<br><br>'.PHP_EOL;
      if ($pos === 0)
      {
        $pagination_segment = $last_segment;
      }
      else
      {
        $pagination_segment = '';
      }
      //echo '$ajax_url: ['.$ajax_url.']<br><br>'.PHP_EOL;
      $onclick_param = 'onclick="infiniteScroll'.$tag_count.'.fetchData(\''.$pagination_segment.'\');"';
      $pagination_links = preg_replace($pattern, $onclick_param, $pagination_links, 1);
    }
    
    return $pagination_links;
  }
  // END FUNCTION
  
  function _getVariablePair($var_name, $data)
  {
    if (strpos($data, LD.$var_name.RD) !== FALSE AND strpos($data, LD.'/'.$var_name.RD) !== FALSE)
    {
      $opening_tag_pos = strpos($data, LD.$var_name.RD);
      //echo '$opening_tag_pos: '.$opening_tag_pos.'<br><br>'.PHP_EOL;
      $var_pair['data_above'] = substr($data, 0, $opening_tag_pos);
      //echo '$var_pair[\'data_above\']: ['.$var_pair['data_above'].']<br><br>'.PHP_EOL;
      $opening_tag_pos += strlen(LD.$var_name.RD);
      //echo '$opening_tag_pos: ['.$opening_tag_pos.']<br><br>'.PHP_EOL;
      $data_bottom = substr($data, $opening_tag_pos);
      //echo '$var_pair[\'data_beneath\']: ['.$var_pair['data_beneath'].']<br><br>'.PHP_EOL;
      $data_bottom_splitted = explode(LD.'/'.$var_name.RD, $data_bottom, 2);
      if (count($data_bottom_splitted) == 2)
      {
        $var_pair['value'] = $data_bottom_splitted[0];
        //echo '$var_pair[\'value\']: ['.$var_pair['value'].']<br><br>'.PHP_EOL;
        $var_pair['data_beneath'] = $data_bottom_splitted[1];
        $var_pair['data_without'] = $var_pair['data_above'].$var_pair['data_beneath'];
        //echo '$var_pair[\'data_without\']: ['.$var_pair['data_without'].']<br><br>'.PHP_EOL;
         
      }
      else
      {
        $var_pair['value'] = '';
        $var_pair['data_without'] = $data;
      }
    }
    else
    {
      $var_pair['value'] = '';
      $var_pair['data_without'] = $data;
      $var_pair['data_above'] = '';
      $var_pair['data_beneath'] = '';
    }
    return $var_pair;
  }
  // END FUNCTION
  
  function _parse_url_in_parameter($parameter_value)
  {    
    // parse {site_id}, {site_url}, {site_index}, {homepage}
    $site_id = $this->EE->config->item('site_id');
    $site_url = trim(stripslashes($this->EE->config->item('site_url')), '/');
    $site_index = stripslashes($this->EE->config->item('site_index'));
    $homepage = $site_url.'/'.$site_index;
    
    $parameter_value = str_replace(LD.'site_id'.RD, $site_id, $parameter_value);
    $parameter_value = str_replace(LD.'site_url'.RD, $site_url, $parameter_value);
    $parameter_value = str_replace(LD.'site_index'.RD, $site_index, $parameter_value);
    $parameter_value = str_replace(LD.'homepage'.RD, $homepage, $parameter_value);
    
    $parameter_value = str_replace('&#47;', '/', $parameter_value);
    $parameter_value = trim($parameter_value, '/').'/';
    //echo '$parameter_value: ['.$parameter_value.']<br><br>'.PHP_EOL;
    
    return $parameter_value;
  }
  // END FUNCTION
  
  function _js($tag_count, $js_messages, $process_indicator, $ajax_pagination_vars, $ajax_pagination_values, $embed_template_url, $ajax_container, $next_chunk_link, $xid_element_id)
  {
    ob_start(); 
?>

<script type="text/javascript">
//<![CDATA[

infiniteScroll<?=@$tag_count?> = {

  XHR: null,
  
  embed_template_url: "<?= @$embed_template_url ?>",
  
  ajax_container_id: "<?= @$ajax_container ?>",
  ajax_container: null,
  
  process_indicator: "<?= @$process_indicator ?>",
  next_chunk_link: "<?= @$next_chunk_link ?>",
  
  continuous_scroll_completed_segment: '',
  
  max_scroll_pos: '',
  trigger_scroll_pos: '',
  load_next_chunk_at_percent: "<?= @$this->load_next_chunk_at_percent ?>",
  
  // messages
  error_encountered_msg: "<?= @$js_messages['error_encountered_msg'] ?>",
  no_response_msg: "<?= @$js_messages['no_response_msg'] ?>",
  no_container_msg: "<?= @$js_messages['no_container_msg'] ?>",
  
  // embed vars
  ajax_pagination_vars: "<?=@$ajax_pagination_vars?>",
  ajax_pagination_values: "<?=@$ajax_pagination_values?>",
  
  //xid hash
  xid_element_id: "<?=@$xid_element_id?>",
  xid_element: "",
  xid_value: "",
  
  // loaded_data
  requested_data: [],
  
  // helper functions
  
  addEvent: function(elm, evType, fn, useCapture) { 
   	if(elm.addEventListener){
    		elm.addEventListener(evType, fn, useCapture);
    		return true;
    }
    else if(elm.attachEvent){
    		var r = elm.attachEvent("on" + evType, fn);
    		return r;
    }
    else {
    		elm["on" + evType] = fn;
    }
  },
  
  createRequestObject: function() {
    var requestObject;
    
    try {
   		 requestObject = new ActiveXObject("Msxml2.XMLHTTP");
   	}
    catch (e) {
      try {
        requestObject = new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (e2) {
        requestObject = '';
      }
    }
    if(!requestObject && typeof XMLHttpRequest != "undefined") {
      requestObject = new XMLHttpRequest();
    }
    if (!requestObject) {
      alert('This browser does not support AJAX!'); 
      return false;
    }
    else {
      return requestObject;
    }
  },
  
  trim: function(str, charlist) {
    var whitespace, l = 0, i = 0;
    str += "";
    
    if (!charlist) {
        whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    } else {
        charlist += "";
        whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, "\$1");
    }
    
    l = str.length;
    for (i = 0; i < l; i++) {
        if (whitespace.indexOf(str.charAt(i)) === -1) {
            str = str.substring(i);
            break;
        }
    }
    
    l = str.length;
    for (i = l - 1; i >= 0; i--) {
        if (whitespace.indexOf(str.charAt(i)) === -1) {
            str = str.substring(0, i + 1);
            break;
        }
    }
    
    return whitespace.indexOf(str.charAt(0)) === -1 ? str : "";
  },
  
  getElementsByClass: function(className, tag, elm) {	
    if (document.getElementsByClassName) {
   			elm = elm || document;
   			var elements = elm.getElementsByClassName(className),
   				nodeName = (tag)? new RegExp("\\b" + tag + "\\b", "i") : null,
   				returnElements = [],
   				current;
   			for(var i=0, il=elements.length; i<il; i+=1){
   				current = elements[i];
   				if(!nodeName || nodeName.test(current.nodeName)) {
   					returnElements.push(current);
   				}
   			}
   			return returnElements;
   	}
   	else if (document.evaluate) {
   			tag = tag || "*";
   			elm = elm || document;
   			var classes = className.split(" "),
   				classesToCheck = "",
   				xhtmlNamespace = "http://www.w3.org/1999/xhtml",
   				namespaceResolver = (document.documentElement.namespaceURI === xhtmlNamespace)? xhtmlNamespace : null,
   				returnElements = [],
   				elements,
   				node;
   			for(var j=0, jl=classes.length; j<jl; j+=1){
   				classesToCheck += "[contains(concat(\' \', @class, \' \'), \' " + classes[j] + " \')]";
   			}
   			try	{
   				elements = document.evaluate(".//" + tag + classesToCheck, elm, namespaceResolver, 0, null);
   			}
   			catch (e) {
   				elements = document.evaluate(".//" + tag + classesToCheck, elm, null, 0, null);
   			}
   			while ((node = elements.iterateNext())) {
   				returnElements.push(node);
   			}
   			return returnElements;
   	}
   	else {
   			tag = tag || "*";
   			elm = elm || document;
   			var classes = className.split(" "),
   				classesToCheck = [],
   				elements = (tag === "*" && elm.all)? elm.all : elm.getElementsByTagName(tag),
   				current,
   				returnElements = [],
   				match;
   			for(var k=0, kl=classes.length; k<kl; k+=1){
   				classesToCheck.push(new RegExp("(^|\\s)" + classes[k] + "(\\s|$)"));
   			}
   			for(var l=0, ll=elements.length; l<ll; l+=1){
   				current = elements[l];
   				match = false;
   				for(var m=0, ml=classesToCheck.length; m<ml; m+=1){
   					match = classesToCheck[m].test(current.className);
   					if (!match) {
   						break;
   					}
   				}
   				if (match) {
   					returnElements.push(current);
   				}
   			}
   			return returnElements;
   	}
  },
  
  hide: function(el) {
    if (!el.getAttribute('displayOld')) {
        el.setAttribute("displayOld", el.style.display);
    }
    el.style.display = "none";
  },
  
  show: function(el) {
    var getRealDisplay;
    var old;
    var nodeName;
    var body;
    var display;
    var testElem;
    
    getRealDisplay = function(elem) {
      if (elem.currentStyle) {
        return elem.currentStyle.display;
      } 
      else if (window.getComputedStyle) {
        var computedStyle = window.getComputedStyle(elem, null);
        return computedStyle.getPropertyValue('display');
      }
    };
    
    if (typeof this.displayCache == 'undefined') {
      this.displayCache = {};
    }
    
    if (getRealDisplay(el) != 'none') return;
    
    old = el.getAttribute("displayOld");
    el.style.display = old || "";
    
    if (getRealDisplay(el) === "none") {
      nodeName = el.nodeName;
      body = document.body;
      if (this.displayCache[nodeName]) {
        display = this.displayCache[nodeName];
      }
      else {
        testElem = document.createElement(nodeName);
        body.appendChild(testElem);
        display = getRealDisplay(testElem);
        if (display === "none") {
          display = "block";
        }
        body.removeChild(testElem);
        this.displayCache[nodeName] = display;
      }
      el.setAttribute('displayOld', display);
      el.style.display = display;
    }
  },
  
  parseScript: function(_source, _execute) {
  		var source = _source;
  		var scripts = new Array();
    
    //alert('Parsing of scripts started!');
  		// Strip out tags
  		while(source.indexOf("<script") > -1 || source.indexOf("</script") > -1) {
  			var s = source.indexOf("<script");
  			var s_e = source.indexOf(">", s);
  			var e = source.indexOf("</script", s);
  			var e_e = source.indexOf(">", e);
  			
  			// Add to scripts array
  			scripts.push(source.substring(s_e+1, e));
  			// Strip from source
  			source = source.substring(0, s) + source.substring(e_e+1);
  		}
    //alert('scripts.length: ' + scripts.length);
  		
  		// Loop through every script collected and eval it
    if (_execute) {
      for(var i=0; i<scripts.length; i++) {
       try {
    				eval(scripts[i]);
    			}
    			catch(ex) {
    				// do what you want here when a script fails
        //alert('Script failed to execute!');
    			}
   		}
    }
  		
  		
  		// Return the cleaned source
  		return source;
	 },
  
  // custom stuff
  
  fetchData: function(pagination_url_segment) {
    var data_string;
    var ajax_url;
    
    //console.log('fetchData');
    //alert('infiniteScroll<?=@$tag_count?>.embed_template_url: ' + infiniteScroll<?=@$tag_count?>.embed_template_url);
    //alert('infiniteScroll<?=@$tag_count?>.ajax_container_id: ' + infiniteScroll<?=@$tag_count?>.ajax_container_id);

    ajax_url = infiniteScroll<?=@$tag_count?>.embed_template_url + pagination_url_segment;
    //alert(ajax_url);
    
    infiniteScroll<?=@$tag_count?>.start_process_indicator(infiniteScroll<?=@$tag_count?>.ajax_container);
    
    if (infiniteScroll<?=@$tag_count?>.XHR) {
      infiniteScroll<?=@$tag_count?>.XHR.abort();
    }
    else {
      infiniteScroll<?=@$tag_count?>.XHR = infiniteScroll<?=@$tag_count?>.createRequestObject();
    }
    
    // put embed vars into data string
    infiniteScroll<?=@$tag_count?>.xid_element = infiniteScroll<?=@$tag_count?>.xid_element_id ?  document.getElementById(infiniteScroll<?=@$tag_count?>.xid_element_id) : '';
    infiniteScroll<?=@$tag_count?>.xid_value = infiniteScroll<?=@$tag_count?>.xid_element ? document.getElementById(infiniteScroll<?=@$tag_count?>.xid_element_id).value : infiniteScroll<?=@$tag_count?>.xid_value;
    //alert('infiniteScroll<?=@$tag_count?>.xid_value: ' + infiniteScroll<?=@$tag_count?>.xid_value); 
    data_string = 'ajax=yes&XID=' + infiniteScroll<?=@$tag_count?>.xid_value;
    if (infiniteScroll<?=@$tag_count?>.ajax_pagination_vars && infiniteScroll<?=@$tag_count?>.ajax_pagination_values) {
      data_string += '&ajax_pagination_vars=' + encodeURIComponent(infiniteScroll<?=@$tag_count?>.ajax_pagination_vars) + '&ajax_pagination_values=' + encodeURIComponent(infiniteScroll<?=@$tag_count?>.ajax_pagination_values);
    }
    //console.log('data_string: ' + data_string);
    //alert('data_string: ' + data_string);
    
    infiniteScroll<?=@$tag_count?>.XHR.open("POST", ajax_url, true);
    infiniteScroll<?=@$tag_count?>.XHR.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
    infiniteScroll<?=@$tag_count?>.XHR.setRequestHeader("Connection", "close");
    infiniteScroll<?=@$tag_count?>.XHR.send(data_string);
    infiniteScroll<?=@$tag_count?>.XHR.onreadystatechange = function() {
      if (infiniteScroll<?=@$tag_count?>.XHR.readyState == 4) {
        infiniteScroll<?=@$tag_count?>.resultOfFetchData(pagination_url_segment);
      }
    }
  },
  
  resultOfFetchData: function(pagination_url_segment) {
    var response;
    var response_without_script;
    var error_encountered;
    var error_message;
    
    //console.log('resultOfFetchData func!');
    
    if (infiniteScroll<?=@$tag_count?>.XHR.status != 200) {
      response = infiniteScroll<?=@$tag_count?>.error_encountered_msg + infiniteScroll<?=@$tag_count?>.XHR.status;
      error_encountered = true;
    }
    
    response = infiniteScroll<?=@$tag_count?>.XHR.responseText;
    //console.log('response: [' + response + ']' + infiniteScroll<?=@$tag_count?>.XHR.status);
    
    if (response == 'null' || infiniteScroll<?=@$tag_count?>.trim(response) == '') {
   			response = infiniteScroll<?=@$tag_count?>.no_response_msg;
      error_encountered = true;
  		}
    
    if (response.indexOf('html') != -1) {   // parsing EE error message 
      //alert('invalid action!');
      response = infiniteScroll<?=@$tag_count?>.parseResponseForMessage(response);
      error_encountered = true;
      //infiniteScroll<?=@$tag_count?>.fetchData(pagination_url_segment);
      //return;
    }
    
    //response = 'No response from server.';
    //error_encountered = true;
    
    //infiniteScroll<?=@$tag_count?>.stop_process_indicator(infiniteScroll<?=@$tag_count?>.ajax_container);
    
    if (error_encountered != true) {
      
      if (infiniteScroll<?=@$tag_count?>.xid_element) {
        infiniteScroll<?=@$tag_count?>.xid_element.parentNode.removeChild(infiniteScroll<?=@$tag_count?>.xid_element);
      }
      infiniteScroll<?=@$tag_count?>.remove_class_elements(infiniteScroll<?=@$tag_count?>.ajax_container, infiniteScroll<?=@$tag_count?>.process_indicator);
      infiniteScroll<?=@$tag_count?>.remove_class_elements(infiniteScroll<?=@$tag_count?>.ajax_container, infiniteScroll<?=@$tag_count?>.next_chunk_link);
      if (infiniteScroll<?=@$tag_count?>.next_chunk_indicator) {
        infiniteScroll<?=@$tag_count?>.ajax_container.removeChild(infiniteScroll<?=@$tag_count?>.next_chunk_indicator);
      }
      response_without_script = infiniteScroll<?=@$tag_count?>.parseScript(response, false);
      infiniteScroll<?=@$tag_count?>.requested_data[pagination_url_segment] = response_without_script;
      infiniteScroll<?=@$tag_count?>.ajax_container.innerHTML = infiniteScroll<?=@$tag_count?>.ajax_container.innerHTML + response_without_script;
      infiniteScroll<?=@$tag_count?>.parseScript(response, true);
      infiniteScroll<?=@$tag_count?>.ajax_container.scrollTop = infiniteScroll<?=@$tag_count?>.trigger_scroll_pos - 1;
      infiniteScroll<?=@$tag_count?>.max_scroll_pos = infiniteScroll<?=@$tag_count?>.ajax_container.scrollHeight - infiniteScroll<?=@$tag_count?>.ajax_container.clientHeight;
      infiniteScroll<?=@$tag_count?>.trigger_scroll_pos = Math.round(infiniteScroll<?=@$tag_count?>.max_scroll_pos / 100 * infiniteScroll<?=@$tag_count?>.load_next_chunk_at_percent);
      //alert('2 max_scroll_pos: ' + infiniteScroll<?=@$tag_count?>.max_scroll_pos);
      //alert('2 trigger_scroll_pos: ' + infiniteScroll<?=@$tag_count?>.trigger_scroll_pos);
      infiniteScroll<?=@$tag_count?>.continuous_scroll_completed_segment = pagination_url_segment;
    }
    else {
      alert(response);
    }
  },
  
  start_process_indicator: function() {
    var process_indicator;
    
    if (infiniteScroll<?=@$tag_count?>.process_indicator) {
      process_indicator = infiniteScroll<?=@$tag_count?>.getElementsByClass(infiniteScroll<?=@$tag_count?>.process_indicator, '', infiniteScroll<?=@$tag_count?>.ajax_container);
      //alert('process_indicator.length: ' + process_indicator.length);
      if (process_indicator.length > 0) {
        for (var i = 0; i < process_indicator.length; i++) {
          //process_indicator[i].style.border = '2px solid red';
          infiniteScroll<?=@$tag_count?>.show(process_indicator[i]);
        }
        if (infiniteScroll<?=@$tag_count?>.process_indicator_scroll == 'yes') {
          process_indicator[0].scrollIntoView(true);
        }
      }
    }
  },
  
  stop_process_indicator: function() {
    var process_indicator;
    
    if (infiniteScroll<?=@$tag_count?>.process_indicator) {
      process_indicator = infiniteScroll<?=@$tag_count?>.getElementsByClass(infiniteScroll<?=@$tag_count?>.process_indicator, '', infiniteScroll<?=@$tag_count?>.ajax_container);
      //alert('process_indicator.length: ' + process_indicator.length);
      if (process_indicator.length > 0) {
        for (var i = 0; i < process_indicator.length; i++) {
          infiniteScroll<?=@$tag_count?>.hide(process_indicator[i]);
        }
      }
    }
  },
  
  parseResponseForMessage: function(response) {
    var error_message_pattern;
    var redir_link_pattern;
    var redir_link;
    var message_part;
    var message;
    var title_pattern; 
    var title;
    
    //alert('1 response: ' + response);
    title_pattern = /<title>\s*(.+)\s*<\/title>/;
    title = title_pattern.exec(response);
    if (title && typeof title[1] != 'undefined') {
      //alert('title[1]: ' + title[1]);
      //alert('2 response: ' + response);
      error_message_pattern = /<li>\s*(.+)\s*<\/li>/g;
      message = '';
      while (message_part = error_message_pattern.exec(response)) {
        message += message_part[0];
      }
      message = message.replace(/<li>/g, '<p>');
      message =  message.replace(/<\/li>/g, '</p>');
      redir_link_pattern = /<p><a\s*(.+)\s*<\/a><\/p>/g;
      redir_link = redir_link_pattern.exec(response);
      redir_link = redir_link ? redir_link[0] : '';
      //alert('redir_link: [' + redir_link + ']');
      error_message_pattern = /<p>\s*(.+)\s*<\/p>/g;
      while (message_part = error_message_pattern.exec(response)) {
        //alert('2 message_part[0]: ' + message_part[0]);
        if (message_part[0] != redir_link) {
          message += message_part[0];
        }
      }
      return message;
    }
    
    return false;
  },
  
  remove_class_elements: function(parent_element, class_name) {
    var class_elements;
    
    if (class_name) {
      class_elements = infiniteScroll<?=@$tag_count?>.getElementsByClass(class_name, '', parent_element);
      //alert('class_elements.length: ' + class_elements.length);
      if (class_elements.length > 0) {
        for (var i = 0; i < class_elements.length; i++) {
          class_elements[i].parentNode.removeChild(class_elements[i]);
        }
      }
    }
  },
  
  continuous_scroll: function() {
    //alert('continuous_scroll');
    if (infiniteScroll<?=@$tag_count?>.ajax_container.scrollTop > infiniteScroll<?=@$tag_count?>.trigger_scroll_pos || infiniteScroll<?=@$tag_count?>.ajax_container.scrollHeight == infiniteScroll<?=@$tag_count?>.ajax_container.clientHeight)
    {
      //alert('start scroll ' + infiniteScrollData<?=@$tag_count?>.continuous_scroll_pagination_segment + '|' + infiniteScroll<?=@$tag_count?>.continuous_scroll_completed_segment);
      if (infiniteScrollData<?=@$tag_count?>.continuous_scroll_pagination_segment != infiniteScroll<?=@$tag_count?>.continuous_scroll_completed_segment && typeof infiniteScroll<?=@$tag_count?>.requested_data[infiniteScrollData<?=@$tag_count?>.continuous_scroll_pagination_segment] == 'undefined') {
        infiniteScroll<?=@$tag_count?>.fetchData(infiniteScrollData<?=@$tag_count?>.continuous_scroll_pagination_segment);
        infiniteScroll<?=@$tag_count?>.requested_data[infiniteScrollData<?=@$tag_count?>.continuous_scroll_pagination_segment] = true;
      }
    }
  },
  
  init: function() {
    var data_pagination_number;
    
    infiniteScroll<?=@$tag_count?>.ajax_container = document.getElementById(infiniteScroll<?=@$tag_count?>.ajax_container_id);
    
    //infiniteScroll<?=@$tag_count?>.XHR = infiniteScroll<?=@$tag_count?>.createRequestObject();
    if (!infiniteScroll<?=@$tag_count?>.ajax_container) {
      alert(infiniteScroll<?=@$tag_count?>.no_container_msg);
      return;
    }
    
    if (infiniteScroll<?=@$tag_count?>.next_chunk_link == '') { // there is no "More" link
      //alert('continuous_scroll_pagination_segment: ' + infiniteScrollData<?=@$tag_count?>.continuous_scroll_pagination_segment);
      if (infiniteScrollData<?=@$tag_count?>.continuous_scroll_pagination_segment) { // there are data to scroll
        infiniteScroll<?=@$tag_count?>.max_scroll_pos = infiniteScroll<?=@$tag_count?>.ajax_container.scrollHeight - infiniteScroll<?=@$tag_count?>.ajax_container.clientHeight;
        infiniteScroll<?=@$tag_count?>.trigger_scroll_pos = Math.round(infiniteScroll<?=@$tag_count?>.max_scroll_pos / 100 * infiniteScroll<?=@$tag_count?>.load_next_chunk_at_percent);
        //alert('1 max_scroll_pos: ' + infiniteScroll<?=@$tag_count?>.max_scroll_pos);
        //alert('1 trigger_scroll_pos: ' + infiniteScroll<?=@$tag_count?>.trigger_scroll_pos);  
        if (infiniteScroll<?=@$tag_count?>.ajax_container == document.body) {
          window.onscroll = infiniteScroll<?=@$tag_count?>.continuous_scroll;  
        }
        else {
          infiniteScroll<?=@$tag_count?>.ajax_container.onscroll = infiniteScroll<?=@$tag_count?>.continuous_scroll;
        } 
        infiniteScroll<?=@$tag_count?>.continuous_scroll();
      }
    }
  }
}

infiniteScroll<?=@$tag_count?>.addEvent(window, 'load', infiniteScroll<?=@$tag_count?>.init, false);

//]]>
</script>

<?php
    $buffer = ob_get_contents();
    ob_end_clean(); 
    
    return $buffer;
  }
  // END FUNCTION
  
  // ----------------------------------------
  //  Plugin Usage
  // ----------------------------------------
  
  function usage()
  {
    ob_start(); 
?>

Allows you to implement infinite scroll functionality in ExpressionEngine. Any ExpressionEngine tag outputting pagination links is supported!

THE TAG {exp:infinite_scroll:wrapper}

This tag is used to wrap all contents of the embed template (see the examples' code).
It has following parameters:

1) embed_template_url - required. Allows you to specify URL of the embed template. 
You must specify full URL, i.e. starting with "http". This parameter accepts inside its value
the following ExpressionEngine variables: site_id, site_url, site_index, homepage.

2) ajax_container - required. Allows you to specify HTML id parameter of the HTML element
which will act as the container to place data loaded by AJAX.

3) process_indicator - optional. Allows you to specify HTML class parameter of the HTML element
which will act as the indicator of the page being loaded by AJAX.

4) next_chunk_link - optional. Allows you to specify HTML class parameter of the HTML element
which will act as the button which should be clicked in order to load next piece of data ("More" button).
This parameter MUST BE defined when you use "More" button to load next piece of data.

5) infinite_scroll_vars - optional. Should be used when there is a need to output embed variables inside embed template.
Inside main template you should specify embed variable "infinite_scroll_vars" which contains pipe-delimited list of variables, 
e.g. infinite_scroll_vars="channel|category". Then in the embed template you should add parameter infinite_scroll_vars="{embed:infinite_scroll_vars}". 
Thus listed variables then can be used by adding to their names "infinite_scroll_embed_", e.g. {infinite_scroll_embed_channel}, {infinite_scroll_embed_category}
(see complex example's code).


6) infinite_scroll_values - optional. Should be used when there is a need to output embed variables inside embed template. 
Inside main template you should specify embed variable "infinite_scroll_values" which contains pipe-delimited list of values of embed variables, 
e.g. infinite_scroll_values="technical|279". Then in the embed template you should add parameter infinite_scroll_values="{embed:infinite_scroll_values}".
Thus listed variables then can be used by adding to their names "infinite_scroll_embed_", e.g. {infinite_scroll_embed_channel}, {infinite_scroll_embed_category}
 (see complex example's code). If the value of certain variable contains pipe character, encode it by using the string ":PIPE:".
 
7) xid_element_id - required for EE 2.7+ Allows you to specify id attribute of HTML input element containing the value of XID hash. 
This element should be included into embed template. 

THE TAG {exp:infinite_scroll:next_chunk_link}

This tag is used to output the button which should be clicked in order to load next piece of data ("More" button).
It has following parameter:

1) pagination_symbol - optional. Allows you to specify symbol which precedes pagination number in URL. Default value is "P".

THE TAG {exp:infinite_scroll:next_chunk_data}

This tag is used to output next piece of data when the "More" button is not used.
It has following parameter:

1) pagination_symbol - optional. Allows you to specify symbol which precedes pagination number in URL. Default value is "P".

USAGE

I. Infinite scrolling with "More" button

Main template (e.g. technical/infinite_scroll):

<html>

<head>

<title>Infinite Scroll demo (link "More" used)</title>

<style type="text/css">

/*Common*/

body {
color: black;
font-family: "arial unicode ms", helvetica, arial, sans-serif;
margin: 0;
font-size: small;
padding: 0;
}

a {
color: #4372aa;
text-decoration: none;
cursor: pointer;
}

a:hover {
text-decoration: underline;
}

/*Main content*/

#maincontent {
width: 48em;
position: relative;
left: 50%;
margin-left: -24em;
padding-bottom: 1em;
}

/* Headings */

h1 {
font-size: 1.5em;
font-weight: normal;
}

h2 {
font-size: 1.1em;
font-weight: bold;
}

/* Indicator */

.process_indicator {
background-image: url({site_url}/images/ajax-loader.gif);
background-repeat: no-repeat;
background-position: bottom left;
height: 3em;
width: 100%;
text-align: left;
display: none;
}

</style>

</head>

<body>

<div id="maincontent">

<h1>Infinite Scroll demo (link "More" used)</h1>


{embed="technical/embed_infinite_scroll"}


</div><!-- End of #maincontent  -->

</body>

</html>

Embed template (e.g. technical/embed_infinite_scroll):

{exp:infinite_scroll:wrapper ajax_container="ajax_container" embed_template_url="{homepage}/technical/embed_infinite_scroll" xid_element_id="infinite_scroll_xid" process_indicator="process_indicator" next_chunk_link="next_chunk_link" parse="inward"}

{infinite_scroll_container_top}
<div id="ajax_container">
{/infinite_scroll_container_top}

<input type="hidden" id="infinite_scroll_xid" value="{XID_HASH}">

{exp:channel:entries channel="mychannel" sort="asc" orderby="title" limit="5" paginate="bottom" dynamic="no" parse="inward"}

<p>{absolute_count}. <a href="{path="technical/demo/{url_title}"}">{title}</a></p>

{if count == total_results}
<div class="process_indicator">
Loading...
</div>
{/if}

{paginate}{exp:infinite_scroll:next_chunk_link}{if next_page}<a href="{auto_path}" class="next_chunk_link">More</a>{/if}{/exp:infinite_scroll:next_chunk_link}{/paginate}

{/exp:channel:entries}

{infinite_scroll_container_bottom}
</div><!-- End of #ajax_container -->
{/infinite_scroll_container_bottom}

{/exp:infinite_scroll:wrapper}


II. Infinite scrolling without "More" button

Main template (e.g. technical/infinite_scroll_no_link_more):

<html>

<head>

<title>Infinite Scroll demo (no link "More")</title>

<style type="text/css">

/*Common*/

body {
color: black;
font-family: "arial unicode ms", helvetica, arial, sans-serif;
margin: 0;
font-size: small;
padding: 0;
}

a {
color: #4372aa;
text-decoration: none;
cursor: pointer;
}

a:hover {
text-decoration: underline;
}

/*Main content*/

#maincontent {
width: 48em;
position: relative;
left: 50%;
margin-left: -24em;
padding-bottom: 1em;
}

/* Headings */

h1 {
font-size: 1.5em;
font-weight: normal;
}

h2 {
font-size: 1.1em;
font-weight: bold;
}

/* Indicator */

.process_indicator {
background-image: url({site_url}/images/ajax-loader.gif);
background-repeat: no-repeat;
background-position: bottom left;
height: 3em;
width: 100%;
text-align: left;
}

#ajax_container {
height: 50em;
border: solid 1px silver;
overflow-y: scroll;
padding: 0.8em;
}

</style>

</head>

<body>

<div id="maincontent">

<h1>Infinite Scroll demo (no link "More")</h1>


{embed="technical/embed_infinite_scroll_no_link_more"}


</div><!-- End of #maincontent  -->

</body>

</html>

Embed template (e.g. technical/embed_infinite_scroll_no_link_more):

{exp:infinite_scroll:wrapper ajax_container="ajax_container" embed_template_url="{homepage}/technical/embed_infinite_scroll_no_link_more" xid_element_id="infinite_scroll_xid" process_indicator="process_indicator" parse="inward"}

{infinite_scroll_container_top}
<div id="ajax_container">
{/infinite_scroll_container_top}

<input type="hidden" id="infinite_scroll_xid" value="{XID_HASH}">

{exp:channel:entries channel="mychannel" sort="asc" orderby="title" limit="30" paginate="bottom" dynamic="no" parse="inward"}

<p>{absolute_count}. <a href="{path="technical/demo/{url_title}"}">{title}</a></p>

{paginate}
{if next_page}
<div class="process_indicator">
Loading...
</div>
{exp:infinite_scroll:next_chunk_data}{auto_path}{/exp:infinite_scroll:next_chunk_data}
{/if}
{/paginate}

{/exp:channel:entries}

{infinite_scroll_container_bottom}
</div><!-- End of # ajax_container -->
{/infinite_scroll_container_bottom}

{/exp:infinite_scroll:wrapper}

III. Displaying several infinite scroll areas in one template

Displaying several paginated lists in one template is easy.
You only need to define additional variable "tag_count" for each embed template. For example:

{embed="technical/embed_infinite_scroll1" infinite_scroll_vars="tag_count" infinite_scroll_values="1"}

{embed="technical/embed_infinite_scroll2" infinite_scroll_vars="tag_count" infinite_scroll_values="2"}

{embed="technical/embed_infinite_scroll3" infinite_scroll_vars="tag_count" infinite_scroll_values="3"}

IV. Pinterest.com style infinite scrolling

To get Pinterest.com style infinite scrolling you need to make a couple of simple changes in the template
created to get infinite scrolling without "More" button. So, the main template (e.g. technical/infinite_scroll_pinterest_com_style)
would be as this:

<html>

<head>

{embed="embeds/meta_content_type"}

{embed="embeds/meta_favicon"}

<title>Infinite Scroll demo (pinterest.com style)</title>

<style type="text/css">

{embed="stylesheets/css_technical"}

html {
height:100%; 
max-height:100%;  
padding:0; 
margin:0; 
border:0;  
overflow: hidden; 
}

body {height:100%; max-height:100%; overflow:hidden; padding:0; margin:0; border:0;}
#ajax_container {display:block; height:100%; max-height:100%; overflow:auto; position:relative; z-index:3;}

.process_indicator {
background-image: url({site_url}/images/ajax-loader.gif);
background-repeat: no-repeat;
background-position: bottom left;
height: 3em;
width: 100%;
text-align: left;
}

p {
margin: 3em 0 3em 0;
text-size: 1.5em;
}

</style>

</head>

<body>

{embed="technical/embed_infinite_scroll_pinterest_com_style"}

</body>

</html> 

Embed template (e.g. technical/embed_infinite_scroll_pinterest_com_style) will also require only minimal changes:

{exp:infinite_scroll:wrapper ajax_container="ajax_container" embed_template_url="{homepage}/technical/embed_infinite_scroll_pinterest_com_style" xid_element_id="infinite_scroll_xid" process_indicator="process_indicator" parse="inward"}

{infinite_scroll_container_top}
<div id="ajax_container">
{/infinite_scroll_container_top}

<input type="hidden" id="infinite_scroll_xid" value="{XID_HASH}">

{exp:weblog:entries channel="mychannel" sort="asc" orderby="title" limit="30" paginate="bottom" dynamic="no" parse="inward"}

{if absolute_count == 1}<h1>Infinite scroll demo (pinterest.com style)</h1>{/if}

<p>{absolute_count}. <a href="{path="technical/demo/{url_title}"}">{title}</a></p>

{paginate}
{if next_page}
<div class="process_indicator">
Loading...
</div>
{exp:infinite_scroll:next_chunk_data}{auto_path}{/exp:infinite_scroll:next_chunk_data}
{/if}
{/paginate}

{/exp:weblog:entries}

{infinite_scroll_container_bottom}
</div><!-- End of # ajax_container -->
{/infinite_scroll_container_bottom}

{/exp:infinite_scroll:wrapper}


<?php
    $buffer = ob_get_contents();
    	
    ob_end_clean(); 
    
    return $buffer;
  }
  // END FUNCTION
}
// END CLASS
?>