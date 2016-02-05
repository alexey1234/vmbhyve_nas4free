#!/usr/local/bin/php-cgi -f
<?php
/*
	file: bhyve_start.php
*/
header_remove('x-powered-by');
header("content-type: none");
header_remove("content-type");
require_once ("config.inc");
if ( ! is_array($config['bhyve']) ) {echo "Bhyve not set\n"; exit;}
unlink_if_exists("/usr/local/etc/rc.d/vm");
if ( ! symlink ( $config['bhyve']['homefolder']."/conf/rc.d/vm", "/etc/rc.d/vm"))  exec ("logger Failed copy rc script"); 
//chmod("/usr/local/etc/rc.d/vm", 0755);
unlink_if_exists("/usr/local/sbin/vm");
if ( ! symlink ( $config['bhyve']['homefolder']."/conf/bin/vm", "/usr/local/sbin/vm")) exec ("logger Failed copy binary vm"); 
//chmod("/usr/local/bin/vm", 0755);
unlink_if_exists("/usr/local/lib/vm-bhyve");
if ( ! symlink ( $config['bhyve']['homefolder']."/conf/lib", "/usr/local/lib/vm-bhyve")) exec ("logger Failed copy vm libs"); 

if ( is_dir( '/usr/local/www/ext/bhyve') ) {
	exec ( "rm -rf /usr/local/www/ext/bhyve");
}

// Get a list of all the symlinks or files from extensiom that are currently 
// in the webroot, and destroy them.
foreach ( glob('/usr/local/www/extensions_bhyve*.php') as $link) {
	unlink( $link );
}

exec( "mkdir -p /usr/local/www/ext" );
$vm_ext = $config['bhyve']['homefolder']."/conf/ext";
// Link the entire folder into the extension location
exec( "ln -s ".$vm_ext." /usr/local/www/ext/bhyve");
// Create a list of all the php files that need to be linked into the webroot
$php_list = glob( $vm_ext."/*.php" ); 
// We need to extract just the file name so the symbolic links make sense
foreach ( $php_list as $php_file ) {
	// Cut off the prefix to obtain the filename
	$php_file = str_replace( $vm_ext, "", $php_file);
	// Link the real storage location to the webroot
	exec ( "ln -s ".$vm_ext.$php_file." /usr/local/www/".$php_file);
}
if (isset($config['bhyve']['enable'])) {
	exec ("rconf service enable vm");
	exec ("rconf attribute set vm_dir " . $config['bhyve']['homefolder']);
}
?>