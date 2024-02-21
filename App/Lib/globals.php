<?php

	function getSession($key=null)
	{
		session_start();
		$data = $key ? ($_SESSION[$key]??null) : $_SESSION;
		session_write_close();
		return $data;
	}

	function setSession($key,$data)
	{
		session_start();
		$_SESSION[$key] = $data;
		session_write_close();
	}

	function normalize($txt)
	{
		if(is_array($txt))
		{
			$e = new Exception(print_r($txt,1)." es un array");
			echo $e->getTraceAsString();
			throw(new Exception($e->getMessage()."\n".$e->getTraceAsString()));
		}

		$txt = str_replace(chr(194).chr(160),' ',$txt);
		return preg_replace('/\s\s+/', ' ', strtr(trim($txt), array( "\t"=>' ' , "\n"=>' ' , "\r" => ' ') ));
	}

	function rel2abs($base,$rel)
	{
		return replace_url_path($base,$rel);
	}

	function replace_url_path($base,$rel)
	{
		if(!is_string($rel))
		{
			$e = new Exception();
			pre($e->getTraceAsString());
			echo "url: ";pre($rel);
		}

		/* return if already absolute URL */
		if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

		if(!$rel)
			return $base;

		if(strstr($base,'?')!==false)
			list($base,$query) = explode('?',$base);

		/* queries and anchors */
		if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;

		$qry = "";
		if( preg_match('/(.*?)\?(.*)/', $rel, $match) )
		{
			$rel = $match[1];
			$qry = "?".$match[2];
		}

		/* parse base URL and convert to local variables:
		   $scheme, $host, $path */
		extract(parse_url($base));

		if(preg_match('/^\/\//',$rel))
			return "{$scheme}:{$rel}{$qry}";

		if(preg_match('/^:\/\//',$rel))
			return "{$scheme}{$rel}{$qry}";

		if(!isset($path))
			$path='';

		/* remove non-directory element from path */
		$path = preg_replace('#/[^/]*$#', '', $path);

		/* destroy path if relative url points to root */
		if ($rel[0] == '/') $path = '';

		if(empty($host))
		{
			$err = new Exception();
			throw new Exception("$base sin hostname\n".$err->getTraceAsString());
		}

		$port = isset($port) ? ":$port" : '';

		/* dirty absolute URL */
		$abs = "$host$port$path/$rel";

		/* replace '//' or '/./' or '/foo/../' with '/' */
		$re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
		for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

		/* absolute URL is ready! */
		return $scheme.'://'.$abs.$qry;
	}

	function ver($html){
		return str_replace(array("&","<",">","\n"),array("&amp;","&lt;","&gt;","<br>"),$html);
	}





	global $specialchars;


	$specialchars = get_html_translation_table (HTML_ENTITIES);
	$specialchars2 = array('&apos;'=>'&#39;', '&tilde;'=>'&#152;', '&yuml;'=>'&#255;', '&OElig;'=>'&#338;', '&oelig;'=>'&#339;', '&Scaron;'=>'&#352;', '&scaron;'=>'&#353;', '&Yuml;'=>'&#376;', '&fnof;'=>'&#402;', '&circ;'=>'&#710;', '&Alpha;'=>'&#913;', '&Beta;'=>'&#914;', '&Gamma;'=>'&#915;', '&Delta;'=>'&#916;', '&Epsilon;'=>'&#917;', '&Zeta;'=>'&#918;', '&Eta;'=>'&#919;', '&Theta;'=>'&#920;', '&Iota;'=>'&#921;', '&Kappa;'=>'&#922;', '&Lambda;'=>'&#923;', '&Mu;'=>'&#924;', '&Nu;'=>'&#925;', '&Xi;'=>'&#926;', '&Omicron;'=>'&#927;', '&Pi;'=>'&#928;', '&Rho;'=>'&#929;', '&Sigma;'=>'&#931;', '&Tau;'=>'&#932;', '&Upsilon;'=>'&#933;', '&Phi;'=>'&#934;', '&Chi;'=>'&#935;', '&Psi;'=>'&#936;', '&Omega;'=>'&#937;', '&alpha;'=>'&#945;', '&beta;'=>'&#946;', '&gamma;'=>'&#947;', '&delta;'=>'&#948;', '&epsilon;'=>'&#949;', '&zeta;'=>'&#950;', '&eta;'=>'&#951;', '&theta;'=>'&#952;', '&iota;'=>'&#953;', '&kappa;'=>'&#954;', '&lambda;'=>'&#955;', '&mu;'=>'&#956;', '&nu;'=>'&#957;', '&xi;'=>'&#958;', '&omicron;'=>'&#959;', '&pi;'=>'&#960;', '&rho;'=>'&#961;', '&sigmaf;'=>'&#962;', '&sigma;'=>'&#963;', '&tau;'=>'&#964;', '&upsilon;'=>'&#965;', '&phi;'=>'&#966;', '&chi;'=>'&#967;', '&psi;'=>'&#968;', '&omega;'=>'&#969;', '&thetasym;'=>'&#977;', '&upsih;'=>'&#978;', '&piv;'=>'&#982;', '&ensp;'=>'&#8194;', '&emsp;'=>'&#8195;', '&thinsp;'=>'&#8201;', '&zwnj;'=>'&#8204;', '&zwj;'=>'&#8205;', '&lrm;'=>'&#8206;', '&rlm;'=>'&#8207;', '&ndash;'=>'&#8211;', '&mdash;'=>'&#8212;', '&lsquo;'=>'&#8216;', '&rsquo;'=>'&#8217;', '&sbquo;'=>'&#8218;', '&ldquo;'=>'&#8220;', '&rdquo;'=>'&#8221;', '&bdquo;'=>'&#8222;', '&dagger;'=>'&#8224;', '&Dagger;'=>'&#8225;', '&bull;'=>'&#8226;', '&hellip;'=>'&#8230;', '&permil;'=>'&#8240;', '&prime;'=>'&#8242;', '&Prime;'=>'&#8243;', '&lsaquo;'=>'&#8249;', '&rsaquo;'=>'&#8250;', '&oline;'=>'&#8254;', '&frasl;'=>'&#8260;', '&euro;'=>'&#8364;','&image;'=>'&#8465;', '&weierp;'=>'&#8472;', '&real;'=>'&#8476;', '&trade;'=>'&#8482;', '&alefsym;'=>'&#8501;', '&larr;'=>'&#8592;', '&uarr;'=>'&#8593;', '&rarr;'=>'&#8594;', '&darr;'=>'&#8595;', '&harr;'=>'&#8596;', '&crarr;'=>'&#8629;', '&lArr;'=>'&#8656;', '&uArr;'=>'&#8657;', '&rArr;'=>'&#8658;', '&dArr;'=>'&#8659;', '&hArr;'=>'&#8660;', '&forall;'=>'&#8704;', '&part;'=>'&#8706;', '&exist;'=>'&#8707;', '&empty;'=>'&#8709;', '&nabla;'=>'&#8711;', '&isin;'=>'&#8712;', '&notin;'=>'&#8713;', '&ni;'=>'&#8715;', '&prod;'=>'&#8719;', '&sum;'=>'&#8721;', '&minus;'=>'&#8722;', '&lowast;'=>'&#8727;', '&radic;'=>'&#8730;', '&prop;'=>'&#8733;', '&infin;'=>'&#8734;', '&ang;'=>'&#8736;', '&and;'=>'&#8743;', '&or;'=>'&#8744;', '&cap;'=>'&#8745;', '&cup;'=>'&#8746;', '&int;'=>'&#8747;', '&there4;'=>'&#8756;', '&sim;'=>'&#8764;', '&cong;'=>'&#8773;', '&asymp;'=>'&#8776;', '&ne;'=>'&#8800;', '&equiv;'=>'&#8801;', '&le;'=>'&#8804;', '&ge;'=>'&#8805;', '&sub;'=>'&#8834;', '&sup;'=>'&#8835;', '&nsub;'=>'&#8836;', '&sube;'=>'&#8838;', '&supe;'=>'&#8839;', '&oplus;'=>'&#8853;', '&otimes;'=>'&#8855;', '&perp;'=>'&#8869;', '&sdot;'=>'&#8901;', '&lceil;'=>'&#8968;', '&rceil;'=>'&#8969;', '&lfloor;'=>'&#8970;', '&rfloor;'=>'&#8971;', '&lang;'=>'&#9001;', '&rang;'=>'&#9002;', '&loz;'=>'&#9674;', '&spades;'=>'&#9824;', '&clubs;'=>'&#9827;', '&hearts;'=>'&#9829;', '&diams;'=>'&#9830;');
	$specialchars3 = array(

		'&nbsp;'	=> '&#160;',
		'&amp;'		=> '&#38;',
		'&hellip;'	=> '&#133;',
		'&euro;'	=> '&#128;',
		'&quot;'	=> '&#34;',

		'&lt;'		=> '&#60;',
		'&gt;'		=> '&#62;',
	);

	$specialchars = array_flip($specialchars);
	$specialchars = array_merge( $specialchars, $specialchars2 );
	$specialchars = array_merge( $specialchars, $specialchars3 );

	function replacecallbak($matches)
	{
		global $specialchars;
		@$trad = $specialchars[$matches[0]];
	#	$trad = chr($matches[1]);

		return $trad ? $trad : $matches[0];
	}

	function translate($str)
	{
		return preg_replace_callback('/&#?[0-9a-z]+;/mi', 'replacecallbak' , $str );
	}
	
	function html_decode($str)
	{
		return preg_replace_callback('/&#?([0-9a-z]+);/mi', function($m) use($str){
			return is_string($m[1]) ? html_entity_decode($m[0]) : chr($m[1]);
		}, $str);
	}

	function trans($str){
		$s = translate($str);
		do{
			$str = $s;

			$s = preg_replace_callback('/&#?([0-9a-z]+);/mi', function($m){
				return utf8_encode(chr($m[1]));
			}, $str);

		} while($s!=$str);

		return $s;
	}


	function u2a($str)
	{
		$str = utf8_decode($str);
		echo "$str<br>";
		return preg_replace_callback("/([\x80-\xFF])/",function($matches){
			return "(".ord($matches[1]).")";
		},$str);
	}

	function utf8_to_html($data)
	{
		return preg_replace_callback("/([\\xC0-\\xF7]{1,1}[\\x80-\\xBF]+)/", function($data){ return _utf8_to_html($data[1]); }, $data);
	}
/*
	function utf8_to_html ($data)
	{
		return preg_replace("/([\\xC0-\\xF7]{1,1}[\\x80-\\xBF]+)/e", '_utf8_to_html("\\1")', $data);
	}
*/
	function _utf8_to_html ($data)
	{
		$ret = 0;
		foreach((str_split(strrev(chr((ord($data[0]) % 252 % 248 % 240 % 224 % 192) + 128) . substr($data, 1)))) as $k => $v)
			$ret += (ord($v) % 128) * pow(64, $k);
		return "&#$ret;";
	}


	function arreglar($html)
	{
		global $delete_cdata;
		if($delete_cdata)
		{
//			$html = preg_replace('/\<!\[CDATA\[.*?\]>/s', "", $html);
			$html = preg_replace('/\<!\[CDATA.*?\].?\>/s', "", $html);
			$html = preg_replace('/\]\].?>/s', "", $html);
		}

		global $delete_all_script;
		if($delete_all_script)
		{
			$html = preg_replace('/\<script.*?\<\/script>/s', "", $html);
			$html = preg_replace('/\<style.*?\<\/style>/s', "", $html);
			$html = preg_replace('/\<\!\[.*?\<\!\[endif\]>/s', "", $html);
		}

		global $delete_aecoc_template;
		if($delete_aecoc_template)
			$html = preg_replace('/<%.*?%>/','',$html); // aecoc

		$html = preg_replace('/<\?xml.*?>/','',$html);
		$html = preg_replace('/<\%=.*?>/','',$html);


/*
		$html = str_replace('&nbsp;', 	'&#160;', $html);
		$html = str_replace('&amp;', 	'&#38;', $html);
		$html = str_replace('&hellip;', '&#133;', $html);
		$html = str_replace('&euro;',	'&#128;', $html);
		$html = str_replace('&quot;',	'&#34;', $html);


		$html = str_replace('&lt;',	'&#60;', $html);
		$html = str_replace('&gt;',	'&#62;', $html);
*/

#		$html = str_replace(array_keys($specialchars3), array_values($specialchars3), $html);

		$html = translate($html);
//		$html = utf8_encode($html);
//		$html = utf8_to_html($html);

		return $html;
	}


    function html_to_utf8($str)
    {
		return preg_replace_callback('/&#([0-9]+);/mi', '_html_to_utf8' , $str );
    }

    function _html_to_utf8($data)
    {
    	return chr($data[1]);
	}

	function add_log($txt,$EOL="\n")
	{
		global $_force_cli_output;
		if(php_sapi_name() == 'cli' || $_force_cli_output)
			echo "$txt{$EOL}";
		else
		{
			$txt = str_replace("\t",'&nbsp; &nbsp; &nbsp;',$txt);
			pre($txt);
		}

		return;
	}

	function pre($str,$exit=false){

		global $_force_cli_output;

		$obj = is_array($str) && isset($str[0]) ? $str[0] : $str;
		$is_item = is_object($obj) && preg_match('/App.Lib.Spider.(?:.*?Item|Selector)/',get_class($obj));

		if($is_item)
			display($str);
		else
		{
			if(php_sapi_name() == 'cli' || $_force_cli_output)
				echo print_r($str,1)."\n";
			else
			{
				global $_pre_style;
				if(!$_pre_style)
				{
					$_pre_style = true;

					echo "
					<style>
					.mypre ul{
						margin:0;
						padding-left:24px;
					}
					.mypre>ul{
						padding-left:0;
					}
					.mypre li{
						display: block;
						margin: 0;
					}
					.mypre span, .mypre li{
						font-family: monospace;
						white-space: pre;
					}
					.mypre ul.hide{
						display:none;
					}
					.mypre span{
						cursor:pointer;
						color:blue;
					}
					.mypre span:hover{
						text-decoration: underline;
					}
					</style>
					<script>
						function toggle(item){
							item.className=(item.className==='hide'?'show':'hide');						
						}
					</script>
					";
				}

				echo "<div class='mypre'>";
				printItem($str);
				echo "</div>";
			}
		}

		if($exit)
			exit;
	}

	function printItem($data)
	{
		if(is_array($data) || is_object($data))
		{
			$type = ucfirst(gettype($data));
			if($type=='Object')
			{
				$type = get_class($data).' '.$type;
				$data = (array)$data;
			}

			$js =" onclick=\"toggle(this.nextSibling)\"";
			$js2=" onclick=\"this.parentNode.nextSibling.querySelectorAll(':scope > li > ul').forEach(function(item){toggle(item)})\"";

			echo "<span id='aa1' $js>$type</span><ul><li><span $js2>(</span></li><ul>";
			foreach($data as $k => $v)
			{
				$k = preg_replace('/\*(.+)/','$1:protected',$k);
				echo "<li>[$k] => ";
				printItem($v);
				echo "</li>";
			}
			echo "</ul><li>)</li></ul>";
		}
		else {
			if (null === $data)
				$data = ':null:';
			if (true === $data)
				$data = ':true:';
			if (false === $data){
				$data = ':false:';
			}
			$data = str_replace('<','&lt;',$data);
			echo preg_replace('/:(null|true|false):/','<i>$1</i>',$data);
		}
	}

	function accessProtected($obj, $prop)
	{
		$reflection = new ReflectionClass($obj);
		$property = $reflection->getProperty($prop);
		$property->setAccessible(true);
		return $property->getValue($obj);
	}

	function profile($str='')
	{
		$EOL = php_sapi_name() == 'cli' ? "\n" : '<br>';

		global $r;
		if($r && $str)
			printf("%s: %.2fs{$EOL}", $str, microtime(true)-$r); 
		$r=microtime(true);
	}

	function debug_trace($salir=false){
		$e = new \Exception();
		echo "<pre>".$e->getTraceAsString()."</pre>";
		if($salir)
			exit;
	}



	function display($nodes)
	{
		if(!$nodes)
			return;
/*
		// convierte a array para poder visualizar los dos tipos de colección
		if( !is_array($nodes) )
		{
			if(!is_object($nodes))
			{
				echo $nodes;
				exit;
			}

			if($nodes->getParent())
				$nodes = array($nodes);
			else
				$nodes = $nodes->getChilds();
		}
*/
		if(php_sapi_name() !== 'cli')
		{
			print("<style>*{font-family:courier new;font-size:12px} .sig{cursor:pointer;cursor:hand;float:left;width:20px;position:absolute;} .mas .cabe{display:block;} .mas .tree{display:none} .men .cabe{display:none} .men .tree{display:block;background:#f0f0f0;}</style>");
			print("<style> .all .mas .cabe{display:none} .all .mas .tree{display:block} .all .sig{display:none} .all .men .tree{background:white} .sig2{cursor:pointer;cursor:hand;}</style>");
			print("<style> ul{list-style:none; padding-left:20px;margin:0 1px;} </style>");


			if( is_object($nodes) )
			{
				if(!strstr(get_class($nodes),'Selector') )
				{
					displaybase($nodes);
					return;
				}
				else
				{
					if(is_array($nodes))
					{
						foreach($nodes as $item)
							displaybase($item);
					}
					else
					{
						display($nodes->childs);
//						if(!is_array($nodes->getName()))
//							displaybase($nodes);
//						else
//							foreach($nodes->childs as $item)
//								displaybase($item);
					}
				}
			}
			else
			{
		//		print("<script>\nfunction click(obj){\nobj.className=obj.className=='mas'?'men':'mas';\n}function all(obj){\nobj.className=obj.className=='les'?'all':'les';\n}</script>");
				print("<div class='les'><div class='sig2' onclick='obj=parentNode;obj.className=obj.className==\"les\"?\"all\":\"les\";'>&lt;&gt;</div>");
				foreach($nodes as $node)
				{
					print("<div class='mas'><div class='cabe'><div class='sig' onclick='obj=parentNode.parentNode;obj.className=obj.className==\"mas\"?\"men\":\"mas\";'>+ </div>&nbsp;&nbsp; ");
					drawitem($node);
					print("</div><div class='tree'><div class='sig' onclick='obj=parentNode.parentNode;obj.className=obj.className==\"mas\"?\"men\":\"mas\";'>-</div>");
					echo "<ul>";
					displaybase($node);
					echo "</ul>";
					print("</div></div>");
				}
				print("</div>");
			}
		}
		else
		{
			foreach($nodes as $node)
			{
				displaybase($node);
			}
		}
	}

	function displaybase($node,$depth=0)
	{
		if(!$node)
			return;

		if(is_array($node))
			$node = $node[0];

		if(php_sapi_name() !== 'cli')
		{
			echo "<li>";
			drawitem($node);
			echo "</li>";

			echo "<ul>";
			foreach($node->getChilds() as $item)
				displaybase($item,$depth);
			echo "</ul>";
		}
		else
		{
			echo str_repeat("   ",$depth);

			drawitem($node);
			foreach($node->getChilds() as $item)
				displaybase($item,$depth+1);
		}
	}

	function drawitem($node)
	{
		$c = count($node->getChilds());
		$ref = $node->id??$node->childs[0]->id;
		$name = $node->getName();
		$attrs = $node->getAttrs();
		$id = @$attrs['ID'];
		$name_attr = @$attrs['NAME'];
		$clas = @$attrs['CLASS'];
		$char = $node->getText();
	#	$char = $node->getChar();
		$parent = $node->parent();

		if( $parent )
		{
	//		if( $parent->getName() == "A" )
	//			print("<a href='".$parent->getAttr('href')."'>");

			if( $parent->getName() == "LINK" || $parent->getName() == "FEEDBURNER:ORIGLINK" )
				print("<a href='".$char."'>");

			if( $parent->getName() == "SCRIPT" )
				return;
		}

		if(php_sapi_name() !== 'cli')
		{
			if( $char)
				print("<font color=black><b>$char</b></font> ($ref)<br>");
			else
			{
				print("&lt;$name");

				if($attrs)
				foreach($attrs as $atk => $atv)
				{
					if(in_array($atk,array('HREF')))
						$atv="<a href=\"$atv\">$atv</a>";
					print(" $atk='$atv'");
				}

				print("&gt;");
				print(" ($ref) - $c<br>");
			}

			if( $parent )
				if( $parent->getName() == "A" || $parent->getName() == "LINK" || $parent->getName() == "FEEDBURNER:ORIGLINK" )
					print("</a>");
		}
		else
		{
			if( $char)
				print("$char\n");
			else
			{
				echo "<$name";
				if($attrs)
				foreach($attrs as $atk => $atv)
					print(" $atk='$atv'");
				print(">\n");
			}
		}
	}
	
	function underscore($input)
	{
		return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
	}

	function debug($var,$exit=false)
	{
		print "<p align=left><pre align=left class=item>";
		print_r ($var);
		print "</pre></p>";
		
		if($exit)
			exit;
	}
	
    /**
     * Convert any passed string to a url friendly string. Converts 'My first blog post' to 'my-first-blog-post'
     *
     * @param  string $text  Text to urlize
     * @return string $text  Urlized text
     */
    function urlize($text)
    {
        // Remove all non url friendly characters with the unaccent function
        $text = sin_tildes($text);

        if (function_exists('mb_strtolower'))
        {
            $text = mb_strtolower($text);
        } else {
            $text = strtolower($text);
        }

        // Remove all none word characters
        $text = preg_replace('/\W/', ' ', $text);

        // More stripping. Replace spaces with dashes
        $text = strtolower(preg_replace('/[^A-Z^a-z^0-9^\/]+/', '-',
                           preg_replace('/([a-z\d])([A-Z])/', '\1_\2',
                           preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2',
                           preg_replace('/::/', '/', $text)))));

        return trim($text, '-');
    }

	function sha_encode($codigo, $categoria = "")
	{
		return substr(sha1($codigo.$categoria),0,8);
	}

	function array_in_array($arr1, $arr2)
	{
		foreach($arr1 as $item)
			if(!in_array($item,$arr2))
				return false;
		return true;
	}

	function strtoascii($str){
		$ret = "";
		for($i=0;$i<strlen($str);$i++)
			$ret.=$str[$i]." : ".ord($str[$i])."\n";
		return $ret;
	}

	function mb_ucfirst($str)
	{
		return ucfirst(mb_strtolower($str,'UTF-8'));
	}

	const MINUSC = 'áàçéèíìñóòúùü';
	const MAYUSC = 'ÁÀÇÉÈÍÌÑÓÒÚÙÜ';

	function mystrtolower($cadena){
	    return strtr(strtolower($cadena), MAYUSC, MINUSC);
	}
	
	function mystrtoupper($cadena){
		return strtr(strtoupper($cadena), MINUSC, MAYUSC);
	}

	function sin_tildes ($string) {
		$table = array(
			'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj', 'Ž'=>'Z', 'ž'=>'z',
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
			'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
			'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
			'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b',
			'ÿ'=>'y',
			rawurldecode("%C4%81") => 'a',
			rawurldecode("%C5%8D") => 'o',
		);

		return strtr($string, $table);
	}

	function limpiar($str){
		return normalize(preg_replace('/[^\w\s]/','',strtolower(sin_tildes(html_decode($str)))));
	}

	// retorna todos los substrings que encuentra entre dos cadenas
	function chunk($str,$lft,$rgt)
	{
		$n2 = 0;

		$ret = array();
		do
		{
			$n1 = strpos($str, $lft, $n2);
			if($n1!==false)
			{
				$n2 = strpos($str, $rgt, $n1);
				$ret[] = substr($str,$n1+strlen($lft),$n2-$n1-strlen($lft));
			}
		} while($n1!==false);
		
		return $ret;
	}

	function utf8_urldecode($str) {
		return html_entity_decode(preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($str)), null, 'UTF-8');
	}

	function arr2obj($arr){
		return json_decode(json_encode($arr));
	}

	function preg_match_1($expr, $str){
		if(preg_match($expr,$str,$m))
			return $m[1];
		return null;
	}

	function parseParams($str)
	{
		$params = [];
		if($str)
		foreach(explode('&',$str) as $param)
		{
			@list($k,$v) = explode('=',$param);
			$params[$k] = $v;
		}

		return $params;
	}

	function calcETA($i,$max)
	{
		global $_eta;
		$_eta[] = strtotime('now');

		$n = count($_eta);
		if($n>1000)
		{
			array_shift($_eta);
			$n--;
		}

		$time_by_iter = ($_eta[$n-1] - $_eta[0]) / $n;
		return $time_by_iter * ($max - $i);
	}

	function parseSeconds($secs)
	{
		$h = floor($secs/60/60);
		$m = floor($secs/60)%60;
		$s = $secs%60;

		$h = $h ? sprintf("%02d:",$h) : '';
		$m = $h||$m ? sprintf("%02d:",$m) : '';
		$s = $h||$m||$s ? sprintf("%02d",$s) : '';

		return "{$h}{$m}{$s}s";
	}

	function filesize_32b($file)
	{
		return int_32b(filesize($file));
	}

	function int_32b($value)
	{
		return $value < 0 ? ($value + PHP_INT_MAX) + PHP_INT_MAX + 2 : $value;
	}

	function camelize($lower_case_and_underscored_word)
	{
		return strtr(ucwords(strtr($lower_case_and_underscored_word, array('/' => ':: ', '_' => ' ', '-' => ' '))), array(' ' => ''));
	}


	function getmyip()
	{
		$ip = exec("ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
		if(!$ip)
			$ip = exec("/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'");
		if(!$ip)
			$ip = exec("/sbin/ifconfig ens33 | grep 'inet:' | cut -d: -f2 | awk '{ print $1}'");

		return $ip;
	}
	

	function bashColortoHtml($string)
	{
//	[1;37m[42m 200 [0m 
		$string = preg_replace_callback('/.\[(\d+);(\d+)m.\[(\d+)m(.*?).\[0m/',function($match){
			return "<span class='bash_color_{$match[2]} bash_background_{$match[3]}'>{$match[4]}</span>";
		},$string);

//  [33mSincronizar[0m
		$string = preg_replace_callback('/.\[(\d+)m(.*?).\[0m/',function($match){
			return "<span class='bash_color_{$match[1]}'>{$match[2]}</span>";
		},$string);

//	[32;1mSincronizar[0m
		$string = preg_replace_callback('/.\[(\d+);1m(.*?).\[0m/',function($match){
			return "<span class='bash_color_{$match[1]}'>{$match[2]}</span>";
		},$string);

// [37;41;1mSincronizar[0m
		$string = preg_replace_callback('/.\[(\d+);(\d+);1m(.*?).\[0m/',function($match){
			return "<span class='bash_color_{$match[1]} bash_background_{$match[2]}'>{$match[3]}</span>";
		},$string);

//	^[[32;1mSincronizar^[[0m
		$string = preg_replace_callback('/\^\[\[(\d+);1m(.*?)\^\[\[0m/',function($match){
			return "<span class='bash_color_{$match[1]}'>{$match[2]}</span>";
		},$string);

// ^[[37;41;1mSincronizar^[[0m
		$string = preg_replace_callback('/\^\[\[(\d+);(\d+);1m(.*?)\^\[\[0m/',function($match){
			return "<span class='bash_color_{$match[1]} bash_background_{$match[2]}'>{$match[3]}</span>";
		},$string);

		return $string;
	}

	use \orm\DescargasDB\DescargasDB;
	use \orm\DescargasDB\tmpSupermercado;
	use \orm\DescargasDB\RobotConfiguration;

	function _RC($key,$default=null,$force=false)
	{
		$file = debug_backtrace()[0]['file'];
		$robot_name = preg_match_1('/([^\/]+)\.php/',$file);

		global $config_map;
		if(isset($config_map[$robot_name][$key]))
			return $config_map[$robot_name][$key];

		if($force)
		{
			$default = str_replace("'","\'",$default);
			DescargasDB::query("update robot_configuration rc left join tmp_supermercado s on s.id=rc.supermercado_id set config_value='{$default}' where s.codigo='{$robot_name}' and config_name='{$key}'");
		}
		else
		{
			$config = DescargasDB::queryMap("select config_name,config_value from robot_configuration rc left join tmp_supermercado s on s.id=rc.supermercado_id where s.codigo='$robot_name'",array('config_name'));
			foreach($config as $k => $item)
				$config_map[$robot_name][$k] = $item['config_value'];

			if(isset($config[$key]))
				return $config[$key]['config_value'];

			$super = tmpSupermercado::findOneBy(array('codigo'=>$robot_name));
			RobotConfiguration::insert(array(
				'supermercado_id' => $super->getId(),
				'config_name' => $key,
				'config_value' => $default,
			));
		}

		return $config_map[$robot_name][$key] = $default;
	}


	function file_put_contents_encode($filename,$str,$flags=0){
		file_put_contents($filename,encode($str),$flags);
	}
	function encode($str){
		return iconv( mb_detect_encoding( $str ), 'Windows-1252//TRANSLIT', $str );
	}
