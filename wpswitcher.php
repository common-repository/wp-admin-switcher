<?php
/*
Plugin Name: WP Admin Switcher
Plugin URI: http://www.decodephp.com
Description: Quickly switch between multiple wordpress administration panels.
Version: 1.1
Author: Sabeen Malik
Author URI: http://www.decodephp.com
*/
ob_start();
	
	if (!class_exists('ditwp_blogInfo')) {	
		
		class ditwp_blogInfo {
		
			function ditwp_blogInfo() {
				$this->blog_array = array();
				$this->restore_settings();
				add_action('admin_menu', array(&$this , 'blog_info') );
			}
			
			function blog_array_shift() {
				$total_inds = count($this->blog_array);
				$tmp_arr = array();
				$tmp_counter = 0;
				
				for ($i=0; $i<$total_inds; $i++) {
					if(!empty($this->blog_array[$i][url])) {
						$tmp_arr[$tmp_counter][name] = $this->blog_array[$i][name];
						$tmp_arr[$tmp_counter][url] = $this->blog_array[$i][url];
						$tmp_arr[$tmp_counter][user] = $this->blog_array[$i][user];
						$tmp_arr[$tmp_counter][pass] = $this->blog_array[$i][pass];
						$tmp_counter++;
					}
				}
				
				unset($this->blog_array);
				$this->blog_array = array();
				$this->blog_array = $tmp_arr;
			}
			
			function delete_blog($blog_url) {
				$total_inds = count($this->blog_array);
				
				for ($i=0; $i<$total_inds; $i++) {
					if($this->blog_array[$i][url] == $blog_url) {
						$this->blog_array[$i][url] = '';
						$this->blog_array[$i][name] = '';
					}
				}
				$this->blog_array_shift();
				$this->update_settings();
			}
			
			function edit_blog($blog_url) {
				
				$this->temp = array();
				$total_inds = count($this->blog_array);
				for( $j=0; $j<$total_inds;$j++) {
					if($this->blog_array[$j][url] == $blog_url) {
						$this->temp[name] = $this->blog_array[$j][name];
						$this->temp[url] = $this->blog_array[$j][url];
						$this->temp[user] = $this->blog_array[$j][user];
						$this->temp[pass] = $this->blog_array[$j][pass];
						break;
					}
				}
				//$this->delete_blog($_GET['blog_url']);
			}
			
			function update_settings() {
				$blog_serialize = serialize($this->blog_array);
				update_option('bloging_info',$blog_serialize);
			}
			
			function restore_settings() {
				$blog_serialize = get_option('bloging_info');
				if($blog_serialize)	{
					$unserial_str = unserialize($blog_serialize);
					
					if (!empty($unserial_str)) $this->blog_array = $unserial_str;
				}	
			}
		
			function blog_info() {
				add_menu_page('WP Admin Switcher','WP Admin Switcher', 'WP Admin Switcher', dirname(__FILE__).'/wpswitcher.php');
				add_submenu_page(dirname(__FILE__).'/wpswitcher.php', 'WP Admin Switcher', 'WP Admin Switcher', 8, basename(__FILE__), array(&$this, 'bloginfo_Page'));
			}
			
			function save_info() {
				$total_inds = count($this->blog_array);
				$burl = $_POST['blog_url'];
				
				if(substr($burl , -1) != "/"){
					$burl = $burl."/";
				}
				
				$this->blog_array[$total_inds][name] = $_POST['blog_name'];
				$this->blog_array[$total_inds][url] = $burl;
				$this->blog_array[$total_inds][user] = $_POST['blog_username'];
				$this->blog_array[$total_inds][pass] = $_POST['blog_userpassword'];
				
				$this->update_settings();
				
				if (!empty($_POST[hdn_url])) { 
					header("Location: ?page=wpswitcher.php&msg=Blog Information has been updated successfully");
				} else {
					header("Location: ?page=wpswitcher.php&msg=New Blog Information has been added successfully");
				}	
			}
			
			function bloginfo_Page() {
			
				if($_POST['blog_name']){
					$this->save_info();
				}
				
				if ($_GET['operation_delete']){
					$this->delete_blog($_GET['blog_url']);
					header("Location: ?page=wpswitcher.php&msg=Blog information has been deleted successfully");
				}
				
				if($_GET['operation_edit']) {
	
					$this->edit_blog($_GET['blog_url']);
				}
				
				if (isset($_POST[hdn_url])) {
					$this->delete_blog($_POST[hdn_url]);
				}
				
				$this->restore_settings();
			
	?>
	
	<? if (!empty($_GET['msg'])) {?>
		<div id="message" class="updated fade"><p><strong><? echo $_GET['msg']; ?></strong></p></div>
	<? } ?>
	
	
	<div class="wrap">
		<h2>Blog Information</h2>
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			
				<? if ($_GET[operation_edit]) {?>
					<tr>
						<td><span style="font-size:16px">Update Blog Information </span></td>
					</tr>
				<? } else { ?>
					<tr>
						<td><span style="font-size:16px">Add New Blog Information </span></td>
					</tr>
				<? } ?>	
				
				<tr>
					<td>&nbsp;</td>
				 
				</tr>
				<tr>
					<td><table width="100%" align="center">
						<form action="?page=wpswitcher.php" method="post" name="frm" id="frm">
						<tr>
						
							<td>&nbsp;</td>
							<td>All Fields are Required</td>
						
						</tr>
						<tr>
						
						  <td width="23%">Blog Name</td>
						  <td width="77%"><input  type="text" name="blog_name" id="blog_name" value="<? if($_GET['operation_edit']) echo $this->temp[name];?>" /></td>
						
						</tr>
						<tr>
						
						  <td>Blog URL</td>
						  <td><input type="text" name="blog_url" id="blog_url" value="<? if($_GET['operation_edit']) echo $this->temp[url];?>"/></td>
						
						</tr>
						<tr>
							
							<td>&nbsp;</td>
							<td>( Should be wp-admin url like http://www.wordpress.com/wp-admin/ )</td>
						
						</tr>
						<tr>
							<td>Blog User Name</td>
							<td><input type="text" name="blog_username" id="blog_username" value="<? if($_GET['operation_edit']) echo $this->temp[user];?>" /></td>
						
						</tr>
						<tr>
						
						  <td>Blog User Password</td>
						  <td><input type="text" name="blog_userpassword" id="blog_userpassword" value="<? if($_GET['operation_edit']) echo $this->temp[pass];?>" />
						  <input name="hdn_url" type="hidden" id="hdn_url" value="<? 
							  if (isset($_GET[operation_edit])) {
								echo $_GET['blog_url'];
							  }
						  ?>" /></td>
						
						</tr>
						<tr>
						
						  <td>&nbsp;</td>
						  <td><input type="button" name="btn_save" id="btn_save" value="Save" onclick="return validation()" /></td>
						
						</tr>
					  </form>
					</table></td>
				  
			  </tr>
				  <tr>
				  
					<td>&nbsp;</td>
				  
				  </tr>
				  <tr>
				  
					<td><hr align="left" width="875" /></td>
				  
				  </tr>
				  <tr>
				  
					<td>&nbsp;</td>
				  
				  </tr>
				  <tr>
				  
					<td><span style="font-size:16px">All Saved Blogs </span></td>
				  
				  </tr>
				  <tr>
				  
					<td>&nbsp;</td>
				  
				  </tr>
	  </table>
				
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
				  <tr>
				  
					<td width="98%"><table width="100%" border="0" align="center" cellpadding="2" cellspacing="0">
				  
				  <tr>
				  <!--<td><input type="checkbox" name="chk_chkall" id="chk_chkall" onclick="chk_all_box()" /></td>-->
					<th width="18%" align="left" scope="col">Name</th>
					<th width="38%" align="left" scope="col">URL</th>
					<th width="16%" align="left" scope="col">User Name</th>
					<th width="15%" align="left" scope="col">Password</th>
					<th width="13%" scope="col">Operations</th>
				  </tr>
	<? 
					$total_inds = count($this->blog_array);
					
					if ($total_inds>0) {
						for($i=0; $i<$total_inds; $i++) {
							if ($i%2 == 0) {
								$bgcolor = 'class="alternate"';
							} else {
								$bgcolor = '';
							}
		?>
						  <tr height="25" <? echo $bgcolor; ?>>
						   <!-- <td><input type="checkbox" name="chk_single[<? echo $i;?>]" id="chk_single[<? echo $i;?>]" /></td> -->
							<td><? echo $this->blog_array[$i][name];?></td>
							<td><? echo $this->blog_array[$i][url];?></td>
							<td><? echo $this->blog_array[$i][user];?></td>
							<td><? echo $this->blog_array[$i][pass];?></td>
							<td align="center"><a href="?page=wpswitcher.php&operation_edit=edit&blog_url=<? echo $this->blog_array[$i][url];?>" >Edit</a>&nbsp;&nbsp;<a href="?page=wpswitcher.php&operation_delete=delete&blog_url=<? echo $this->blog_array[$i][url];?>" onclick="return del_single()">Delete</a></td>
						  </tr>
		<?
						}
					}
	?>
					</table></td>
					<td width="2%">&nbsp;</td>
				  </tr>
	  </table>
	</div>
			<script language="javascript">
				
				function validation() {
					
					if(document.getElementById('blog_name').value == "") {
						alert("Please Enter Blog Name?");
						return false;
					
					}
					if(document.getElementById('blog_url').value == "") {
						alert("Please Enter Blog URL?");
						return false;
					
					}
					if(document.getElementById('blog_username').value == "") {
						alert("Please Enter User Name?");
						return false;
					}
					
					if(document.getElementById('blog_userpassword').value == "") {
						alert("Please Enter Blog Password?");
						return false;
					}
					document.frm.submit();
	
	
				}
				
				function del_single(){
					var i= window.confirm("Are You Sure to Delete this Record");
					if( i == true ){
						document.frm.submit();
						return true;	
					}else{
						return false;
					}
			
				}
			
			</script>
	
	<?			
			
			
			}
		}
		
		
		if (@is_writable(dirname(__FILE__).'/tempfiles')) {	
			$blog_obj = new ditwp_blogInfo();
		}
	} 

	
	
	
	
	if(!class_exists("dit_extractor")){
	
		$filename = dirname(__FILE__).'/class.extractor.php';
	
		include($filename);
	
	}
	
	if(!class_exists('decodeit_admin_switcher')){ 
	
		class decodeit_admin_switcher {
	
			var $osite ;
			var $allsites;
			var $postdata;
			
	
	
			function decodeit_admin_switcher() {
				if(!function_exists("curl_init")){
					add_action('admin_notices', array(&$this , 'blogswitch_admin_notices'));
					return false;
				}
				
				if (@is_writable(dirname(__FILE__).'/tempfiles')) {
					$this->allsites = array();
					$this->postdata = array();
					$this->populate_postarr();
					
					$parts = explode("wp-admin/" , $_SERVER[REQUEST_URI]);
					$part = $parts[1];
		
					$explode = explode("/virtual/" , $_SERVER[REQUEST_URI]);
					$explode = explode("/" , $explode[1]);
					$siteid = $explode[0];
					$qpart = $explode[1];
		
					$part = str_replace("virtual/$siteid/" , "" , $part); 
		
					$mypage = str_replace("/virtual/$siteid" , "" , $_SERVER[REQUEST_URI]);
		
					if($_SERVER[HTTPS]){
						$this->osite = "https://".$_SERVER[HTTP_HOST].$mypage;
					}else{
						$this->osite = "http://".$_SERVER[HTTP_HOST].$mypage	;
					}
		
					
					$this->contact_html = '<div><br>Switch To : ';
					$this->contact_html .= '<select name="dit_switcher" onchange="if(this.value==0){window.location=\''.$this->osite.'\';}else{window.location=\''.get_option("siteurl").'/wp-admin/virtual/\'+this.value+\'/'.$part.'\';}">
												<option value="0" >Parent Site</option>';
		
					foreach($this->allsites as $sid=>$blog_record){
					
						$nm = $blog_record[name];
						$sel = "";
						
						$sid = $sid + 1;
						
						if($sid == $siteid) $sel = "selected";
						$this->contact_html .= "<option value='$sid' $sel>$nm</option>";
		
					}
					
		
					$this->contact_html .=	'</select><br></div>';
		
		
					add_action('admin_footer', array(&$this, 'admin_contact_form'));
		
					add_action('template_redirect', array(&$this, 'catch404') , -100000 );
		
				 } else {//if(is_writable(dirname(__FILE__)) {
					add_action('admin_notices', array(&$this , 'blogswitch_admin_notices'));
				}	
			}

			function blogswitch_admin_notices () {
				$str = '<div class="updated" style="background-color: rgb(255, 102, 102);">
				<p><b>WP Admin Switcher Notice:</b></p>';
				
				if( !@is_dir(dirname(__FILE__).'/tempfiles')) {
					$str .= "<p>The Directory ".dirname(__FILE__)."/tempfiles is missing</p>";
				}
				
				if (!@is_writable(dirname(__FILE__).'/tempfiles')) {
					$str .= '<p>Please set the permission for directory '.dirname(__FILE__).'/tempfiles to 777.</p>';
				}
				
				if(!function_exists("curl_init")){
					$str .= '<p>CURL is not available , please make sure you have CURL compiled with PHP.</p>';
				}
				
				$str .= '</div>';
				
				echo $str;
			}
			
	
			function search($start,$end,$string, $borders=true){
	
				$reg="!".preg_quote($start)."(.*?)".preg_quote($end)."!is";
	
				preg_match_all($reg,$string,$matches);
	
				
	
				if($borders) return $matches[0];	
	
				else return $matches[1];	
	
			}
	
			
	
			function admin_contact_form() {
	
				$admin_page_contents = ob_get_contents();
	
				ob_clean();
	
				
	
				$admin_user_str = $this->search('<div id="wphead">','</div>',$admin_page_contents, false);
	
				$admin_user_str = $admin_user_str[0];
	
				$new_str = $admin_user_str.$this->contact_html;
	
				$new_str = str_replace('<p>' , '<p align="right">' , $new_str);
	
				$admin_page_contents = str_replace($admin_user_str, $new_str , $admin_page_contents);
	
				
	
				echo $admin_page_contents;
	
			}
	
	
	
			function catch404(){
				global $wp_query , $wp;
				
				if(substr_count($_SERVER[REQUEST_URI] , "/wp-admin/virtual/") && is_user_logged_in()){
					if(in_array("ob_gzhandler" , ob_list_handlers())) $gz = true;
					$pre =  ob_get_level();
					for($x = 1 ; $x <= $pre ; $x++){
						if($x == 1) {
							$full = ob_get_contents();
							@ob_end_flush();
						}else{
							@ob_end_clean();
						}	
					}
					
					if($gz){
						ob_start("ob_gzhandler");
					}
					$this->showotherdata();
					exit ;
				}
			}
	
			
			function populate_postarr() {
				$blog_serialize = get_option('bloging_info');
				if($blog_serialize){
					$unserial_str = unserialize($blog_serialize);
					if (!empty($unserial_str)) $this->allsites = $unserial_str;
				}
			}
			
	
			function showotherdata(){
				
				$this->postdata = array();
				$saved_arr = array();
				$blog_serialize = get_option('bloging_info');
				$unserial_str = unserialize($blog_serialize);
				if (!empty($unserial_str)) $saved_arr = $unserial_str;

				
				foreach ($saved_arr as $ind=>$record) {
					$sites[($ind+1)][url] = $record[url];
					$sites[($ind+1)][user] = $record[user];
					$sites[($ind+1)][pass] = $record[pass];
				}
				
	
				$explode = explode("/virtual/" , $_SERVER[REQUEST_URI]);
	
				$explode = explode("/" , $explode[1]);
	
				$siteid = $explode[0];
				
				unset($explode[0]);
				$qpart = implode("/" , $explode);
				
				$sitedata = $sites[$siteid];
				
				if($_POST[cookie]){
	
					$cooki = dirname(__FILE__)."/tempfiles/COO".$siteid;
	
					if(is_file($cooki)){
	
						$cdata = implode("" , file($cooki));
	
						
	
						if($cdata){
	
							preg_match("#wordpressuser_ ?(.*)#i", $cdata, $match);
	
							$c1 = "wordpressuser_".str_replace("\t" , "=" , trim($match[1]));
	
							
	
							preg_match("#wordpresspass_ ?(.*)#i", $cdata, $match);
	
							$c2 = "wordpresspass_".str_replace("\t" , "=" , trim($match[1]));
	
							$_POST[cookie] = str_replace("+" , "%20" , urlencode($c1."; ".$c2."; ")); 
	
						}
	
					}
	
				}
	
				
	
				$pdata = array();
	
				$pstr = "";
	
				foreach($_POST as $k=>$v){
	
					$pdata[$k] = $_REQUEST[$k];
	
					if(!is_array($v)){
	
						$pstr .= "$k=".urlencode($v)."&";
	
						$this->postdata[$k] = stripslashes($v);
	
					}else{
	
						$pstr .= $this->subArray($k , $v);	
	
						
	
					}
	
				}
	
				
	
				if(count($_FILES)){
	
					foreach($_FILES as $fk=>$fv){
	
						if(@copy($fv['tmp_name'] , 	dirname(__FILE__)."/tempfiles/".$fv['name'])){
	
							$this->postdata[$fk] = "@".dirname(__FILE__)."/tempfiles/".$fv['name'];
	
						}
	
					}
	
				}
	
	
	
				$browser = new dit_extractor(true , 10 , "COO".$siteid);
				
				
				$data = $browser->getdata($sitedata[url].$qpart , $this->postdata , "" , true , true);
				
	
				preg_match("#Location: ?(.*)#i", $data, $match);
	
				$redirto = trim($match[1]);
	
				
	
				if(substr_count($data , "loginform") || substr_count($redirto , "/wp-login.php?") ){
	
					$expl = $this->search("redirect_to\" value=\"" , "\"" , $data , false);
	
					$expl = $expl[0];
	
	
	
					$logindata['log'] = $sitedata[user];
	
					$logindata['pwd'] = $sitedata[pass];
	
					$logindata['redirect_to'] = $expl;
	
					$logindata['wp-submit'] = "Login";
					
					$strlogin = "";
					foreach($logindata as $kl=>$vl){
						$strlogin .= $kl."=".urlencode($vl)."&"	;
					}
		
					$data = $browser->getdata(str_replace("wp-admin/" , "wp-login.php" ,$sitedata[url]) , $strlogin , "" , true , true);
					
					header("Location: ".$_SERVER['REQUEST_URI']);
					exit;
	
				}
	
				
				
				$admin_user_str = $this->search('<div><br>Switch To : ','<br></div>',$data, true);
				if(!$admin_user_str[0]){
					$admin_user_str = $this->search('<div id="wphead">','</div>',$data, false);
					$admin_user_str = $admin_user_str[0];
					$new_str = $admin_user_str.$this->contact_html;
					$new_str = str_replace('<p>' , '<p align="right">' , $new_str);
					$data = str_replace($admin_user_str, $new_str , $data);
				}else{
					$admin_user_str = $admin_user_str[0];
					$new_str = $this->contact_html;
					$new_str = str_replace('<p>' , '<p align="right">' , $new_str);
					$data = str_replace($admin_user_str, $new_str , $data);
				}
	
				
				if(substr_count($data , "<title>WordPress ") && substr_count($data , " Error</title>")){
					$new_str = "<body>".$this->contact_html;
					$data = str_replace("<body>", $new_str , $data);
				}
			
	
				$explit = explode("\r\n\r\n", $data);
	
	
	
				$headers = $explit[0];
	
	
	
				if(substr_count($headers , "100 Continue")){
	
					$headers = $explit[1];
	
				}
	
				$d = explode($headers , $data);
	
				$data = $d[1];
	
				$data = trim($data);
	
			
	
				preg_match("#Location: ?(.*)#i", $headers, $match);
	
				$redirto = trim($match[1]);
	
	
	
				$expl = explode("\n" , $headers);
	
				foreach($expl as $line){
	
					if(substr_count($line , "Content-Type:")){
	
						$ctype = trim($line)."<-->";
	
						$ctype = $this->search("Content-Type:" , '<-->' , $ctype , false);
	
						$ctype = $ctype[0];
	
					}
	
					
	
					$theHeaderStringArray = preg_split("/\s*:\s*/", $line, 2) ;
	
					if (preg_match('/^HTTP/', $theHeaderStringArray[0])){
	
						$status = 	$theHeaderStringArray[0];
	
					}
	
					
	
					if(substr_count($line , "Location:")){
	
						$loc = trim($line)."<-->";
	
						$loc = $this->search("Location:" , '<-->' , $loc , false);
	
						$location = $loc[0];
	
					}
	
				}
	
				
	
				$location = trim($location);
	
				if($location){
	
					if(substr($location , 0 , 1) == "/"){
	
						$parseit = parse_url($sitedata[url]);
	
						$location = $parseit[scheme]."://".$parseit[host].$location;
	
					}
	
				}
	
				
	
				if(count($_FILES)){
	
					foreach($_FILES as $fk=>$fv){
	
						@unlink(dirname(__FILE__)."/tempfiles/".$fv['name']);
	
					}
	
				}
	
	
				if($ctype){
	
					header("Content-Type:$ctype");
	
				}
	
				
	
				if($status){
	
					header($status);
	
				}
				
				if($location && !substr_count($location , "/wp-admin/images")){
	
					header("Location:".$this->modData($sitedata[url] , $location));
	
					exit;
	
				}else{
					echo trim($this->modData($sitedata[url] , $data));
					ob_end_flush();
					exit;
				}
	
			}
	
				
	
			function subArray($k , $arr){
	
				foreach($arr as $k2=>$v2){
	
					if(!is_array($v2)){
	
						$this->postdata[$k."[$k2]"] = stripslashes($v2);
	
						$pstr .= $k."%5B%5D=".urlencode($v2)."&";
	
					}else{
	
						$pstr .= $this->subArray($k2 , $v2);
	
					}
	
				}
	
				return $pstr;
	
			}
	
	
	
			function modData($suburl , $data){
	
				$explode = explode("/virtual/" , $_SERVER[REQUEST_URI]);
	
				$explode = explode("/" , $explode[1]);
	
				$siteid = $explode[0];
	
				$data = str_replace("url('images/" , "url('".$suburl."images/" , $data);
			
				$data = str_replace("url( images/" , "url( ".$suburl."images/" , $data);
				
				$data = str_replace("url(images/" , "url(".$suburl."images/" , $data);
	
				$newurl = get_option("siteurl").'/wp-admin/virtual/'.$siteid.'/';
	
				$data = str_replace($suburl."images/" , "<---nochange-->" , $data);
	
				$data = str_replace($suburl , $newurl , $data);
	
				$data = str_replace("<---nochange-->" , $suburl."images/" , $data);
	
				$data = str_replace("\"images/" , "\"".$suburl."images/" , $data);
	
	
	
				return $data;
	
			}
	
			
	
			function settings() {
	
				$contact_html = '<a href="">contact</a>';
	
				update_option('decodeit_admin_switcher',$contact_html);
	
			}
	
			
	
		}
	
	}
	$dit_switch_obj = new decodeit_admin_switcher();

?>