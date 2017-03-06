<?php


function myexec($ch) {
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $result = array('header' => '',
            'body' => '',
            'curl_error' => '',
            'http_code' => '',
            'last_url' => '');
        if ($error != "") {
            $result['curl_error'] = $error;
            return $result;
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result['header'] = substr($response, 0, $header_size);
        $result['body'] = substr($response, $header_size);
        $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        return $result;
    }

function get_web_page( $url, $cookie ){
        $options = array( 
	    CURLOPT_RETURNTRANSFER => true, // to return web page
            CURLOPT_HEADER         => true, // to return headers in addition to content
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_ENCODING       => "",   // to handle all encodings
            CURLOPT_AUTOREFERER    => true, // to set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,  // set a timeout on connect
            CURLOPT_TIMEOUT        => 120,  // set a timeout on response
            CURLOPT_MAXREDIRS      => 10,   // to stop after 10 redirects
            //CURLINFO_HEADER_OUT    => true, // no header out
            CURLOPT_SSL_VERIFYPEER => 0,// to disable SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_VERBOSE        => 1,
        );

        $handle = curl_init( $url );
        curl_setopt_array( $handle, $options );
 
    // additional for storing cookie 
        curl_setopt($handle, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($handle, CURLOPT_COOKIEFILE, $cookie);

        $raw_content = curl_exec( $handle );
        $err = curl_errno( $handle );
        $errmsg = curl_error( $handle );
        $header = curl_getinfo( $handle ); 
        $last_url = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
        curl_close( $handle );
 
        $header_content = substr($raw_content, 0, $header['header_size']);
        $body_content = trim(str_replace($header_content, '', $raw_content));
    
    // let's extract cookie from raw content for the viewing purpose         
        $cookiepattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m"; 
        preg_match_all($cookiepattern, $header_content, $matches); 
        $cookiesOut = implode("; ", $matches['cookie']);

        $header['called'] = $url; 
        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['headers']  = $header_content;
        $header['content'] = htmlspecialchars($body_content);
        $header['cookies'] = $cookiesOut;
        $header['last_url'] = $last_url;
        echo 'get called <pre>'.print_r($header).'</pre><br/>';
    return $header;
}

function post_web_page( $url, $postfields, $cookie ){
        $options = array( 
	    CURLOPT_RETURNTRANSFER => true, // to return web page
            CURLOPT_HEADER         => true, // to return headers in addition to content
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_ENCODING       => "",   // to handle all encodings
            CURLOPT_AUTOREFERER    => true, // to set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,  // set a timeout on connect
            CURLOPT_TIMEOUT        => 120,  // set a timeout on response
            CURLOPT_MAXREDIRS      => 10,   // to stop after 10 redirects
 //           CURLINFO_HEADER_OUT    => true, // no header out
            CURLOPT_SSL_VERIFYPEER => 0,// to disable SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST           => true, 
            CURLOPT_VERBOSE        => 1,
            CURLOPT_POSTFIELDS     => $postfields,
        );

        $handle = curl_init( $url );
        curl_setopt_array( $handle, $options );
 
    // additional for storing cookie 
        curl_setopt($handle, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($handle, CURLOPT_COOKIEFILE, $cookie);

        $raw_content = curl_exec( $handle );
        $err = curl_errno( $handle );
        $errmsg = curl_error( $handle );
        $header = curl_getinfo( $handle ); 
        $last_url = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
        curl_close( $handle );
 
        $header_content = substr($raw_content, 0, $header['header_size']);
        $body_content = trim(str_replace($header_content, '', $raw_content));
    
    // let's extract cookie from raw content for the viewing purpose         
        $cookiepattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m"; 
        preg_match_all($cookiepattern, $header_content, $matches); 
        $cookiesOut = implode("; ", $matches['cookie']);

        $header['called'] = $url; 
        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['headers']  = $header_content;
        $header['content'] = htmlspecialchars($body_content);
        $header['cookies'] = $cookiesOut;
        $header['last_url'] = $last_url;
        echo 'post called <pre>'.print_r($header).'</pre><br/>';
    return $header;
}

    // extract value from tag
    function extractValue($haystack, $needle, $tag) {
        $section = strpos($haystack, $needle);
        if ($section == FALSE)
            return FALSE;
        $td = strpos($haystack, '<' . $tag, $section);
        if ($td == FALSE)
            return FALSE;
        $closetag = strpos($haystack, '>', $td);
        if ($closetag == FALSE)
            return FALSE;
        $fintd = strpos($haystack, '</' . $tag . '>', $td);
        if ($fintd == FALSE)
            return FALSE;
        $longueur = $fintd - $closetag - 1;
        $val = substr($haystack, $closetag + 1, $longueur);
        return $val;
    }
    
$tmpfname = tempnam(sys_get_temp_dir(), 'JCOOKIE');
$result=get_web_page('http://192.168.0.1/logout.html',$tmpfname);
echo ">>>>>>>>>>logout (".$result['last_url'].")<br/>";
$result=get_web_page('http://192.168.0.1/', $tmpfname);
echo ">>>>>>>>>>load main (".$result['last_url'].")<br/>";
    echo "<pre>".print_r($result['headers'], true)."</pre>";
//echo '<br/>MAIN *****************************************************************************************************<br>';
//echo "<pre>".print_r($header, true)."</pre>";
//echo '<br/>CONFIG *****************************************************************************************************<br>';
$result=get_web_page('http://192.168.0.1/config.html', $tmpfname);
echo ">>>>>>>>>>load config (".$result['last_url'].")<br/>";
    echo "<pre>".print_r($result['headers'], true)."</pre>";
$alreadyconnected = strpos($result['content'], 'TRY AGAIN');
$loggedin = strpos($result['content'], 'SE DECONNECTER');
if ($alreadyconnected) {
    echo ">>>>>>>>>>logged in already, try logout then login again<br/>";
} 

if ($loggedin) {
    echo ">>>>>>>>>>logged in so reset<br/>";
    $result=post_web_page('http://192.168.0.1/goform/WebUiOnlyReboot', '',$tmpfname);
} else {
    echo '>>>>>>>>>>not logged in so try to login<br/>';
    $result=post_web_page('http://192.168.0.1/goform/login', "loginUsername=admin&loginPassword=password",$tmpfname);
echo ">>>>>>>>>>load goform/login (".$result['last_url'].")<br/>";
    $result=get_web_page('http://192.168.0.1/config.html', $tmpfname);
echo ">>>>>>>>>>load config (".$result['last_url'].")<br/>";
    $alreadyconnected = strpos($result['content'], 'TRY AGAIN');
    $loggedin = strpos($result['content'], 'SE DECONNECTER');
    if ($alreadyconnected) {
        echo ">>>>>>>>>>logged in already, try logout then login again<br/>";
    }  else
if ($loggedin) 
    echo ">>>>>>>>>>logged in so reset<br/>";
else         
echo ">>>>>>>>>>abort";
    }
    unlink ($tmpfname);
die();




echo "<pre>".print_r($header, true)."</pre>";
//echo '<br/>*****************************************************************************************************<br>';
//$header=get_web_page('http://192.168.0.1/login.html', $tmpfname);
//echo "<pre>".print_r($header, true)."</pre>";
echo '<br/>*****************************************************************************************************<br>';
$header=post_web_page('http://192.168.0.1/goform/login', "loginUsername=admin&loginPassword=password",$tmpfname);
echo "<pre>".print_r($header, true)."</pre>";
echo '<br/>*****************************************************************************************************<br>';
$header=get_web_page('http://192.168.0.1/logout.html', $tmpfname);
echo "<pre>".print_r($header, true)."</pre>";
echo '<br/>*****************************************************************************************************<br>';
$header=get_web_page('http://192.168.0.1/login.html', "loginUsername=admin&loginPassword=password",$tmpfname);
echo "<pre>".print_r($header, true)."</pre>";
echo '<br/>*****************************************************************************************************<br>';
$header=post_web_page('http://192.168.0.1/goform/login', "loginUsername=admin&loginPassword=password",$tmpfname);
echo "<pre>".print_r($header, true)."</pre>";

die();
//echo $output;
echo '<br/>-----------------------------------------------------------------------------------------------------<br>';
echo '<pre>'.print_r($info, true).'</pre>';
echo '<br/>-----------------------------------------------------------------------------------------------------<br>';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://192.168.0.1/".$_GET['u']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);

$data = array(
    'loginUsername' => 'admin',
    'loginPassword' => 'password',
    'checkPWD' => 'OK'
);
$dataurl = 'loginUsername=admin&loginPassword=password&checkPWD=OK';

curl_setopt($ch, CURLOPT_POSTFIELDS, $dataurl);

curl_setopt($ch, CURLOPT_COOKIE, 1);
curl_setopt($ch, CURLOPT_COOKIEJAR, $tempcookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $tempcookie);

$output = myexec($ch); 
$info = curl_getinfo($ch);
curl_close($ch);
echo 'dataurl = '.$dataurl;
echo '<br/>-----------------------------------------------------------------------------------------------------<br>';
echo '<pre>'.print_r($info, true).'</pre>';
echo '<br/>-----------------------------------------------------------------------------------------------------<br>';
die();

$post_value = '?loginUsername=admin&loginPassword=password';
$data_length = strlen($post_value);
 
$WebConnector = fsockopen('http://192.168.0.1/goform/login', 80);
  
fputs($WebConnector, "POST  /target_url.php  HTTP/1.1\r\n");
fputs($WebConnector, "Host: 192.168.0.22 \r\n");
fputs($WebConnector,"Content-Type: application/x-www-form-urlencoded\r\n");
fputs($WebConnector, "Content-Length: $data_length \r\n");
fputs($WebConnector, "Connection: close\r\n\r\n");
fputs($WebConnector, $post_value);
 
echo $WebConnector;
//closing the connection
fclose($WebConnector);