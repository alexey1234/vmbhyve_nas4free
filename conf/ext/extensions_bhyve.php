<?php
/*
extensions_bhyve.php
*/
require("auth.inc");
require("guiconfig.inc");
$savemsg = "I begin prepare extension for bhyve virtual machines. <br />Now ready commandline interface only, please wait, I work for webgui<br />I add manual page for commands<br />Extension may be upgraded ober webgui page config";
$pgtitle = array("Extensions", "Virtual Machine BHYVE");
if ( !isset( $config['bhyve']['homefolder']) ) {
	if (is_file("/tmp/bhyve.install")) {
		header("Location: extensions_bhyve_config.php"); 
		exit;
	} else { $input_errors[] = "Bhive not installed"; }
}
if ($_POST) {
		if (isset($_POST['Submit']) && $_POST['Submit'] == "Save") {
		//check errors section here
		$config['bhyve']['enable']= isset($_POST['enable']) ? true : false;
		write_config();
		$retval = 0;
		if ( isset($config['bhyve']['enable']) ) { 
			$retval |= exec ("rconf service enable vm");
			$retval |= exec ("rconf attribute set vm_dir " . $config['bhyve']['homefolder']);
			$retval |= exec ("service vm start");
			} 
		else { 
			$retval |= exec ("service vm stop");
			$retval |= exec ("rconf service disable vm");
			$retval |= exec ("rconf attribute remove vm_dir " . $config['bhyve']['homefolder']);
		}
		if ($retval == 0) { 
			//updatenotify_set("vm", UPDATENOTIFY_MODE_MODIFIED, $vmstate); 
			
		} else {
			$input_errors[] = "Somesing wrong" ;
		}
	}
}
$pconfig['enable'] = isset($config['bhyve']['enable']);
include("fbegin.inc");
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
    $('#enable').change(function(){
      $('#submit').toggle(this.changed);
    }).change();

});
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0" >
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabact"><a href="extensions_bhyve.php"><span><?="VM Bhyve";?></span></a></li>
			
			<li class="tabinact"><a href="extensions_bhyve_config.php"><span><?="Extension config";?></span></a></li>
			<li class="tabinact"><a href="extensions_bhyve_manual.php"><span><?="VM Manual";?></span></a></li>
					</span> </a>
				</li>
		</ul>
	</td></tr>
	<tr>
		<td class="tabcont">
		<form action="extensions_bhyve.php" method="post" name="iform" id="iform">
			<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
			<?php if (!empty($savemsg)) print_info_box($savemsg); ?>
			<?php if (updatenotify_exists("vm")) print_config_change_box();?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			
				<?php html_titleline_checkbox("enable", "Bhyve virtual machines", $pconfig['enable'], gettext("Enable"), "" ); ?>
			</table>
			<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="Save"  />
			</div>
			<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<?php 
print $statusvmprint;
include("fend.inc"); ?>
