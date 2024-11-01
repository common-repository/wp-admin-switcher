<? 

	/*

	Class developed by Sab Malik 

	www.decodeit.biz

	Please donot touch unless you are absolutely sure about what your doing!! :)

	*/

	class dit_extractor{

		var $cookiefile ; 

		var $timeout ; 
		
		var $chinfo;

		var $err;
		
		var $hdr;

		

		function dit_extractor($cookie=false , $timeout=5 , $mycookiefile=""){

			$this->timeout = $timeout;

			if($cookie){

				if($mycookiefile){

					$this->cookiefile = dirname(__FILE__)."/tempfiles/".$mycookiefile;

					if(!is_file($this->cookiefile)){

						$fp = fopen ($this->cookiefile , "w");

						fclose($fp);

					}

				}else{

					$this->cookiefile = tempnam("tmp","EXT");

				}	

			}	
			$this->cleantempfiles();
		}

		function cleantempfiles(){
			$thisdir = dirname(__FILE__)."/tempfiles/";
			
			$handle = @opendir($thisdir); 	//-- open the directory to get all the files
			while (false !== ($file = readdir($handle))) { 
				if ($file != "." && $file != ".." && $file != ".htaccess") { 
					$st = stat($thisdir.$file);
					if((mktime() - $st[9]) > 86400){ 
						@unlink($thisdir.$file);
					}
				} 
			}
		}

		function getdata($url , $post=array() ,  $referer = "" , $setcookie=false , $usecookie=false , $replacing=true , $useragent="" , $proxyport="" , $proxyaddr="" , $poststr = "" , $getheader = false){

			$ch = curl_init();
			$getheader = true;
			//$poststr = true;
			if($getheader) curl_setopt ($ch, CURLOPT_HEADER, true);

			//curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);

			

			if($proxyport && $proxyaddr) curl_setopt($ch, CURLOPT_PROXY,trim($proxyport).":".trim($proxyaddr));
			
			if($usecookie)	curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefile);

			if($setcookie)	curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);

			

			if($this->timeout) curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

			

			if($referer) curl_setopt($ch, CURLOPT_REFERER, trim($referer));

			else curl_setopt($ch, CURLOPT_REFERER, trim($url));

			

			if(trim($useragent)) curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

			else curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
			
			if($post){

				curl_setopt($ch, CURLOPT_POST, true);

				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

			}
			
			if(count($_FILES)){
				//curl_setopt($ch, CURLOPT_UPLOAD, true);
			}

			if(substr_count($url , "https://")){
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				//curl_setopt($ch, CURLOPT_SSLVERSION, 2);
			}

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			curl_setopt($ch, CURLOPT_URL,$url);

			$data = curl_exec($ch);
			$err = curl_error($ch);
			$this->chinfo = curl_getinfo($ch);
			curl_close ($ch);
			
			unset($ch);
			
			//$theData = preg_split("/(\r\n){2,2}/", $data, 2) ;
			//print_r($theData);
			//$showData = $theData[(count($theData)-1)];
			
			/*$findcomments = $this->search("<!--" , "-->" , $showData);
			foreach($findcomments as $comment){
				$showData = str_replace($comment , "" , $showData);
			}*/
			
			//$this->error = $err;
			//$this->hdr = $theData[(count($theData)-2)];
			
			//$data = $showData;
			
			$scripts = $this->search("<script","</script>", $data);

			/*foreach($scripts as $script){

				$data = str_replace($script , str_replace("\n","<-n->",$script) , $data);

			}

			

			if($replacing){

				$replace_these = array("\n" , "\t" , "\r");

				$data = str_replace( $replace_these , "" , $data);

				$data = str_replace("<-n->" , "\n" , $data);

			}*/

			return $data;

		}

		

		

		function getnamedlinks($data , $name , $starturl=""){

			$form1 =array();

			$form2 =array();

			

			$form1 = $this->search("<a " , "</a>" , $data );

			$form1 = $this->search("<A " , "</A>" , $data );

			

			$finalform = array();

			$finalform =  array_merge ($form1, $form2);

			$finalform = $this->cleanup($finalform);

			

			foreach($finalform as $key=>$fullurl){

				if(trim($fullurl)){

					if(trim(strip_tags($fullurl))<>$name){

						unset($finalform[$key]);

					}

				}

			}

			$gethrefs  = $this->gettagvalues("a" , "href" , implode(" " , $finalform) , $starturl);

			return $gethrefs;

		}

		

		function getlinkswithnames($data , $starturl="" , $mustcontain=""){

			$form1 =array();

			$form2 =array();

			

			$form1 = $this->search("<a " , "</a>" , $data );

			$form1 = $this->search("<A " , "</A>" , $data );

			

			$finalform = array();

			$finalform =  array_merge ($form1, $form2);

			$finalform = $this->cleanup($finalform);

			

			if($mustcontain){

				foreach($finalform as $key=>$url){

					if(trim($url)){

						if(!substr_count($url , $mustcontain)){

							unset($finalform[$key]);			

						}

					}

				}

			}

			

			$newarray = array();

			foreach($finalform as $key=>$fullurl){

				if(trim($fullurl)){

					$name = trim(strip_tags($fullurl));

					$link = $this->gettagvalues("a" , "href" , $fullurl , $starturl);

					$link = $link[0];

					$newarray[$link] = $name;

				}

			}

			

			return $newarray;

		}

		

		function gettagvalues($tagname , $tagvalue , $data , $starturl="" , $mustcontain=""){

			$src_regex ="<"; // 1 start of the tag

			$src_regex .="\s*"; // 2 zero or more whitespace

			$src_regex .="$tagname"; // 3 the img of the tag itself

			$src_regex .="\s+"; // 4 one or more whitespace

			$src_regex .="[^>]*"; // 5 zero or more of any character that is _not_ the end of the tag

			$src_regex .="$tagvalue"; // 6 the src bit of the tag

			$src_regex .="\s*"; // 7 zero or more whitespace

			$src_regex .="="; // 8 the = of the tag

			$src_regex .="\s*"; // 9 zero or more whitespace

			$src_regex .="[\"']?"; // 10 none or one of " or '

			$src_regex .="("; // 11 opening parenthesis, start of the bit we want to capture

			$src_regex .="[^\"' >]+"; // 12 one or more of any character _except_ our closing characters

			$src_regex .=")"; // 13 closing parenthesis, end of the bit we want to capture

			$src_regex .="[\"' >]"; // 14 closing chartacters of the bit we want to capture

			

			$regex = "/"; // regex start delimiter

			$regex .= $src_regex; //

			$regex .= "/"; // regex end delimiter

			$regex .= "i"; // Pattern Modifier - makes regex case insensative

			$regex .= "s"; // Pattern Modifier - makes a dot metacharater in the pattern

			// match all characters, including newlines

			$regex .= "U"; // Pattern Modifier - makes the regex ungready

			

			$finalform = array();

			if (preg_match_all($regex, $data, $links)) {

				$finalform = $this->cleanup($links[1]);

				

				if($starturl){

					foreach($finalform as $key=>$url){

						if(trim($url)){

							if(!substr_count($url , "http://")){

								$finalform[$key] = trim($starturl.$url);			

							}

						}

					}

				}

				

				if($mustcontain){

					foreach($finalform as $key=>$url){

						if(trim($url)){

							if(!substr_count($url , $mustcontain)){

								unset($finalform[$key]);			

							}

						}

					}

					

					$newarr = array();

					foreach($finalform as $url){

						$newarr[] = trim($url);

					}

					$finalform = $newarr;

				}

			}

			return $finalform;

		}

		

		function cleanup($array){

			if(count($array) && is_array($array)){

				foreach($array as $key=>$val){

					if(!trim($val)){

						unset($array[$key]);

					}

				}

			}

			

			return $array;

		}

		

		function search($start,$end,$string, $borders=true){

			$reg="!".preg_quote($start)."(.*?)".preg_quote($end)."!is";

			preg_match_all($reg,$string,$matches);

			

			if($borders) return $matches[0];	

			else return $matches[1];	

		}

	}

?>