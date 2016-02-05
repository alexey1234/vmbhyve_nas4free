<?php
/*
	extensions_bhyve_config.php
*/ 
define ( bhyve_VERSION, 0 );
require("auth.inc");
require("guiconfig.inc");
$pgtitle = array("Extensions", "Virtual Machine BHYVE", "Config");
if (!isset($config['bhyve']) || !is_array($config['bhyve']))
	$config['bhyve'] = array();
if (true == is_file("/tmp/bhyve.install") ) { 
		$pconfig['homefolder'] = file_get_contents("/tmp/bhyve.install"); 
	} else {
		$pconfig['homefolder'] = $config['bhyve']['homefolder'];
	}
if (isset ($_POST["submit1"]) && $_POST["submit1"] =="Save") {
	
			if (!empty($config['bhyve']['homefolder'])) {
						$input_errors[] = "Extension configured, no need push on button!";
						unlink_if_exists("/tmp/bhyve.install");
						goto out;
					}
			if (empty($_POST['homefolder'])) {
					$input_errors[] = "Homefolder must be defined";
					goto out;
					
					}	
			$config['bhyve']['homefolder'] = $_POST['homefolder'];
			$i = 0;
			if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
		    for ($i; $i < count($config['rc']['postinit']['cmd']);) {
		    if (preg_match('|ext\/bhyve/|', $config['rc']['postinit']['cmd'][$i])) 	break;
				++$i;	} 	
			$config['rc']['postinit']['cmd'][$i] = $config['bhyve']['homefolder']."/bin/bhyve_start.php";	
				}
			
			write_config();
			unlink_if_exists("/tmp/bhyve.install");
			unlink_if_exists ( "/usr/local/www/extensions_bhyve.php");
			symlink ( $config['bhyve']['homefolder']."/conf/ext/extensions_bhyve.php" , "/usr/local/www/services_bhyve.php" );
			unlink_if_exists ( "/usr/local/www/extensions_bhyve_config.php");
			symlink ( $config['bhyve']['homefolder']."/conf/ext/extensions_bhyve_config.php" , "/usr/local/www/extensions_bhyve_config.php" );
			unlink_if_exists ( "/usr/local/www/extensions_bhyve_functions.php");
			symlink ( $config['bhyve']['homefolder']."/conf/ext/extensions_bhyve_functions.php" , "/usr/local/www/_functions" );
			unlink_if_exists ( "/usr/local/www/extensions_bhyve_config.php");
			symlink ( $config['bhyve']['homefolder']."/conf/ext/extensions_bhyve_config.php" , "/usr/local/www/extensions_bhyve_config.php" );
			if ( !is_link ( "/etc/rc.d/bhyve") || !is_file ( "/etc/rc.d/bhyve")) { symlink ( $config['bhyve']['homefolder']."/ext/bhyve.sh" , "/etc/rc.d/bhyve" );}
			unlink_if_exists ( "/usr/local/www/status_services.php");
			symlink ( $config['bhyve']['homefolder']."/ext/bhyve/status_services.php" , "/usr/local/www/status_services.php" );
			unlink_if_exists ( "/usr/local/www/fbegin.inc");
			symlink ( $config['bhyve']['homefolder']."/ext/bhyve/fbegin.inc" , "/usr/local/www/fbegin.inc" );
			unlink_if_exists ( "/etc/rc.d/mdnsresponder");
			symlink ( $config['bhyve']['homefolder']."/ext/bhyve/mdnsresponder" , "/etc/rc.d/mdnsresponder" );
			header("Location: services_bhyve.php");
						exit;
}	elseif (isset($_POST['submit1']) && ($_POST['submit1'] == "Uninstall")) {
		//uninstall procedure
			 rc_stop_service('bhyve');
	
		if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
			for ($i = 0; $i < count($config['rc']['postinit']['cmd']);) {
				if (preg_match('/bhyve_start/', $config['rc']['postinit']['cmd'][$i])) {	unset($config['rc']['postinit']['cmd'][$i]);} else{}
				++$i;
			  }
		    }
		if ( is_link ( "/etc/rc.d/bhyve") ) { 	unlink("/etc/rc.d/bhyve"); }
//remowe web pages
		if (is_dir ("/usr/local/www/ext/bhyve")) mwexec ("rm -rf /usr/local/www/ext/bhyve");
		    
	    
//remove bhyve section from config.xml
		if ( is_array($config['bhyve'] ) ) { unset( $config['bhyve'] ); write_config();}
		header("Location: /");
		exit;
		} elseif  (! is_file("/tmp/bhyve.install"))   {
unlink_if_exists ("/tmp/extensions_bhyve_config.php");
$connected = @fsockopen("www.github.com", 80); 
if ( $connected ) {
	fclose($connected);
	unset($gitconfigfile);
	$gitconfigfile = file_get_contents("https://raw.githubusercontent.com/alexey1234/vmbhyve_nas4free/master/conf/ext/extensions_bhyve_config.php");
	$git_ver = preg_split ( "/bhyve_VERSION,/", $gitconfigfile);
	$git_ver = 0 + $git_ver[1];
	mwexec2 ( "fetch {$fetch_args} -o /tmp/install.sh https://raw.githubusercontent.com/alexey1234/bhyve-nas4free/simple/install.sh" , $garbage , $fetch_ret_val ) ;
				if ( is_file("/tmp/install.sh" ) ) {
					// Fetch of install.sh succeeded
					mwexec ("chmod a+x /tmp/install.sh");
				}	
				else {					
					$input_errors[]="There seems to be a networking issue. I can't reach GitHub to retrieve the file. <br />Please check <a href='/system.php'>DNS</a> and other <a href='/interfaces_lan.php'>networking settings</a>. <br />Alternatively, try it again to see if there was some transient network problem.";
				}  // end of failed install.sh fetch	
			
		} // end of successful internet connectivity test
}

out:
include("fbegin.inc");
?>
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	$('#uninstall').change(function() {
	
		if($('#uninstall').is(":checked")) {
			$('#submit1').prop('value','Uninstall');
				}else{
			$('#submit1').prop('value','Save');
				}
		});
	$('#uninstall').change();
});
</script>



	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="extensions_bhyve.php"><span><?="VM Bhyve";?></span></a></li>
			
			<li class="tabact"><a href="extensions_bhyve_config.php"><span><?="Extension config";?></span></a></li>
			<li class="tabinact"><a href="extensions_bhyve_manual.php"><span><?="VM Manual";?></span></a></li>
					</span> </a>
				</li>
		</ul>
	</td></tr>
		<tr>
			<td class="tabcont">
				
				  <?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">

				<tr><td colspan='2' class='list' height='6'></td></tr>
				<tr id='homefolder_tr'>
					<td width='22%' valign='top' class='vncell'><label for='homefolder'>Homefolder for extension</label></td>
					<td width='78%' class='vtable'>
<form action="extensions_bhyve_config.php" method="post" name="wrap1" id="wrap1">
						  <table class="formdata" width="100%" border="0">
							<tr><td width='65%'>
								  <input name='homefolder' type='text' class='formfld' id='homefolder' size='67' value=<?=$pconfig['homefolder']?>  />
								  <br /><span class='vexpl'>Path, where extension live .</span>
							    </td>
							    <td width='35%'>
								    <?php if ( false == is_file("/tmp/bhyve.install") ): ?>
								   Check  for uninstall<input name='uninstall' type='checkbox' class='formfld' id='uninstall' />&nbsp;
								   <?php endif;?>
								  <input name="submit1" type="submit" class="formbtn" id='submit1' value="Save" align="center" />
								 
							 </td></tr>
						  </table>
						  <?php include("formend.inc");?>
</form>
					</td>
				</tr>
				<?php if ( false == is_file("/tmp/bhyve.install") ): ?>
				<tr><td colspan='2' class='list' height='6'></td></tr>
				<form action="exec.php" method="post" name="iform" id="iform" >
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<?php 
			html_titleline(gettext("Update Availability")); 
			html_text($confconv, gettext("Current Status"),"The latest version on GitHub is: " . $git_ver . "<br /><br />Your version is: " . bhyve_VERSION ); 
			?> 
			<tr>
			
			<td width="22%" valign="top" class="vncell">Update your installation&nbsp;</td>
			<td width="78%" class="vtable">
			<?="Click below to download and install the latest version.";?><br />
				<div id="submit_x">
					<input id="bhyve_update" name="bhyve_update" type="submit" class="formbtn" value="Update" onClick="return confirm('<?="Bhyve will stop, and the latest version will  download. Are you sure you want to continue?";?>');" /><br />
				</div>
				<input name="txtCommand" type="hidden" value="<?="sh /tmp/install.sh {$config['bhyve']['homefolder']} &";?>" />
			</td>
			</tr>

	</table><?php include("formend.inc");?>
</form>
<?php endif;?>
	</table>
	


<?php include("fend.inc");?>