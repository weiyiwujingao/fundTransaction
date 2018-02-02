<?php
/** 
 
 * Configuration for default Minify application 
 
 * @package Minify 
 
 */  
  
   
  
   
  
/** 
 
 * In 'debug' mode, Minify can combine fileswith no minification and 
 
 * add comments to indicate line #s of theoriginal files. 
 
 * 
 
 * To allow debugging, set this option to trueand add "&debug=1" to 
 
 * a URI. E.g./min/?f=script1.js,script2.js&debug=1 
 
 */  
  
$min_allowDebugFlag= false;  
  
   
  
   
  
/** 
 
 * Set to true to log messages to FirePHP(Firefox Firebug addon). 
 
 * Set to false for no error logging (Minifymay be slightly faster). 
 
 * @link http://www.firephp.org/ 
 
 * 
 
 * If you want to use a custom error logger,set this to your logger 
 
 * instance. Your object should have a methodlog(string $message). 
 
 * 
 
 * @todo cache system does not have errorlogging yet. 
 
 */  
  
$min_errorLogger =false;  
  
   
  
   
  
/** 
 
 * Allow use of the Minify URI Builder app. Ifyou no longer need 
 
 * this, set to false. 
 
 **/  
  
$min_enableBuilder =true;  
  
   
  
   
  
/** 
 
 * For best performance, specify your tempdirectory here. Otherwise Minify 
 
 * will have to load extra code to guess. Someexamples below: 
 
 */  
  
//$min_cachePath ='c:\\WINDOWS\\Temp';  
  
//$min_cachePath= '/tmp';  
  
//$min_cachePath =preg_replace('/^\\d+;/', '', '/home/httpd/trade.buyfunds.cn/front/min/logpath/');  
$min_cachePath ='/home/httpd/trade.buyfunds.cn/front/min/logpath';  
  
   
  
   
  
/** 
 
 * Leave an empty string to use PHP's$_SERVER['DOCUMENT_ROOT']. 
 
 * 
 
 * On some servers, this value may bemisconfigured or missing. If so, set this 
 
 * to your full document root path with notrailing slash. 
 
 * E.g. '/home/accountname/public_html' or'c:\\xampp\\htdocs' 
 
 * 
 
 * If /min/ is directly inside your documentroot, just uncomment the 
 
 * second line. The third line might work onsome Apache servers. 
 
 */  
  
$min_documentRoot= '';   
  
//$min_documentRoot =substr(__FILE__, 0, strlen(__FILE__) - 15);  
  
//$min_documentRoot =$_SERVER['SUBDOMAIN_DOCUMENT_ROOT'];  
  
//$min_documentRoot ='/var/www/cychai/Cache/';  
  
   
  
   
  
/** 
 
 * Cache file locking. Set to false iffilesystem is NFS. On at least one 
 
 * NFS system flock-ing attempts stalled PHPfor 30 seconds! 
 
 */  
  
$min_cacheFileLocking= true;  
  
   
  
   
  
/** 
 
 * Combining multiple CSS files can place@import declarations after rules, which 
 
 * is invalid. Minify will attempt to detectwhen this happens and place a 
 
 * warning comment at the top of the CSSoutput. To resolve this you can either 
 
 * move the @imports within your CSS files, orenable this option, which will 
 
 * move all @imports to the top of the output.Note that moving @imports could 
 
 * affect CSS values (which is why this optionis disabled by default). 
 
 */  
  
$min_serveOptions['bubbleCssImports']= false;  
  
   
  
   
  
/** 
 
 * Maximum age of browser cache in seconds.After this period, the browser 
 
 * will send another conditional GET. Use alonger period for lower traffic 
 
 * but you may want to shorten this beforemaking changes if it's crucial 
 
 * those changes are seen immediately. 
 
 * 
 
 * Note: Despite this setting, if you include anumber at the end of the 
 
 * querystring, maxAge will be set to one year.E.g. /min/f=hello.css&123456 
 
 */  
  
$min_serveOptions['maxAge']= 315360000;  
  
   
  
   
  
/** 
 
 * If you'd like to restrict the "f"option to files within/below 
 
 * particular directories below DOCUMENT_ROOT,set this here. 
 
 * You will still need to include the directoryin the 
 
 * f or b GET parameters. 
 
 * 
 
 * // = shortcut for DOCUMENT_ROOT 
 
 */  
  
//$min_serveOptions['minApp']['allowDirs']= array('//js', '//css');  
  
   
  
/** 
 
 * Set to true to disable the "f" GETparameter for specifying files. 
 
 * Only the "g" parameter will beconsidered. 
 
 */  
  
$min_serveOptions['minApp']['groupsOnly']= false;  
  
   
  
/** 
 
 * Maximum # of files that can be specified inthe "f" GET parameter 
 
 */  
  
$min_serveOptions['minApp']['maxFiles']= 50;  
  
   
  
   
  
/** 
 
 * If you minify CSS files stored in symlink-eddirectories, the URI rewriting 
 
 * algorithm can fail. To prevent this, providean array of link paths to 
 
 * target paths, where the link paths arewithin the document root. 
 
 * 
 
 * Because paths need to be normalized for thisto work, use "//" to substitute 
 
 * the doc root in the link paths (the arraykeys). E.g.: 
 
 * <code> 
 
 * array('//symlink' => '/real/target/path')// unix 
 
 * array('//static' =>'D:\\staticStorage')  // Windows 
 
 * </code> 
 
 */  
  
$min_symlinks =array();  
  
   
  
   
  
/** 
 
 * If you upload files from Windows to anon-Windows server, Windows may report 
 
 * incorrect mtimes for the files. This maycause Minify to keep serving stale 
 
 * cache files when source file changes aremade too frequently (e.g. more than 
 
 * once an hour). 
 
 * 
 
 * Immediately after modifying and uploading afile, use the touch command to 
 
 * update the mtime on the server. If the mtimejumps ahead by a number of hours, 
 
 * set this variable to that number. If themtime moves back, this should not be 
 
 * needed. 
 
 * 
 
 * In the Windows SFTP client WinSCP, there'san option that may fix this 
 
 * issue without changing the variable below.Under login > environment, 
 
 * select the option "Adjust remotetimestamp with DST". 
 
 * @linkhttp://winscp.net/eng/docs/ui_login_environment#daylight_saving_time 
 
 */  
  
$min_uploaderHoursBehind= 0;  
  
   
  
   
  
/** 
 
 * Path to Minify's lib folder. If you happento move it, change 
 
 * this accordingly. 
 
 */  
  
$min_libPath =dirname(__FILE__) . '/lib';  
  
   
  
   
  
// try to disableoutput_compression (may not have an effect)  
  
ini_set('zlib.output_compression','0'); 
?>