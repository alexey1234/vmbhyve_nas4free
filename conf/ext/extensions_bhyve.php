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
include_once($config['bhyve']['homefolder']."/conf/ext/extensions_bhyve_functions.inc");
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
<script type="text/javascript">//<![CDATA[


$(document).ready(function(){
	var gui = new GUI;
	$('#work_dir').change(function(){
		switch ($('#work_dir').val()) {
		case "0":
			$('#work_dir_simple_tr').show();
			$('#work_dir_zfs_tr').hide();
			$('#work_dir_diff_tr').hide();
			break;
		
		case "1":
			$('#work_dir_simple_tr').hide();
			$('#work_dir_zfs_tr').show();
			$('#work_dir_diff_tr').hide();
			break;
		case "2":
			$('#work_dir_simple_tr').hide();
			$('#work_dir_zfs_tr').hide();
			$('#work_dir_diff_tr').show();
			break;
		default:
			$('#work_dir_simple_tr').show();
			$('#work_dir_zfs_tr').hide();
			$('#work_dir_diff_tr').hide();
			break;
		}
	}).change();
 });

 function clickfix() {
	if (! clickfix) $('#submit').hide(); else $('#submit').show();
}
//]]>
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
			
				<?php html_titleline_checkbox("enable", "Bhyve virtual machines", $pconfig['enable'], gettext("Enable"), "clickfix(true)" ); ?>
				<tr id='work_dir_tr'>
					<td width='22%' valign='top' class='vncell'><label for=''>Working folder</label></td>
					<td width='78%' class='vtable'>
					
					<select name='work_dir' class='formfld' id='work_dir' onchange="clickfix(true)">
														<option value='0' >Home</option>
														<?php if (true == bhyve_zfs_check()):?>
														<option value='1' >Dataset</option>
														<?php endif;?>
														<option value='2' >Different folder</option>
													</select>						
						</td>

					
				</tr>
				<?php html_text("work_dir_simple", "", "Virtual machines will store at extension home folder");?>
				<?php if ( FALSE !== ($datasets_list = bhyve_datasets_list())) {
					for ($i = 0; $i < count($datasets_list); ++$i) { 
					$a_datasets_list[] = $datasets_list[$i][1];
					}
				html_combobox("work_dir_zfs", "", $pconfig['work_dir_zfs'], $a_datasets_list, "Virtual machines will store on dataset", false, false, "clickfix(true)"); }?> 
				<?php html_filechooser('work_dir_diff', "", $pconfig['work_dir_diff'], "Virtual machines will store on any different folder", "/mnt", false, "67", false); ?> 
				<?php html_combobox("delay", "Delay on boot",$pconfig['delay'], array ("1","2","3","4","5","6","7","8"), "Choise delay time between start machines at boot stage", false, "3", false, "clickfix(true)");?>
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
//print_r ($datasets_list);
include("fend.inc"); ?>
