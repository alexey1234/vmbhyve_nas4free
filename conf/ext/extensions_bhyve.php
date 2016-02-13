<?php
/*
extensions_bhyve.php
*/
require("auth.inc");
require_once("guiconfig.inc");
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
	$uconfig = $_POST;
		if (isset($_POST['Submit']) && $_POST['Submit'] == "Save") {
		//check errors section here
		$config['bhyve']['enable']= isset($_POST['enable']) ? true : false;
		$config['bhyve']['delay'] = $_POST['delay'] ;
		$config['bhyve']['work_dir_type'] = $_POST['work_dir_type'] ;
		switch ($_POST['work_dir_type'] ) {
			case "0" : // work dir is extension homefolder
				$config['bhyve']['work_dir'] = $config['bhyve']['homefolder'];
				break;
			case "1" : // work dir is zfs dataset
				$config['bhyve']['work_dir'] = $_POST['work_dir_zfs'];
				break;
			case "2" : //work dir is any another folder
				$config['bhyve']['work_dir'] = $_POST['work_dir_diff'];
				break;
			}
		
		write_config();
		}
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

// Webpage settings
$pconfig['enable'] = isset($config['bhyve']['enable']);
$pconfig['delay'] = isset($config['bhyve']['delay']) ? $config['bhyve']['delay'] : "3";
$pconfig['work_dir_type'] = isset($config['bhyve']['work_dir_type']) ? $config['bhyve']['work_dir_type'] : "0";
if ($config['bhyve']['work_dir_type'] !=='0') {$pconfig['work_dir'] = $config['bhyve']['work_dir'] ;}
/*switch ($config['bhyve']['work_dir']) {
	case "2" :
		$pconfig['work_dir'] = "2";
		
}
*/
include("fbegin.inc");
?>
<script type="text/javascript">//<![CDATA[


$(document).ready(function(){
	var gui = new GUI;
	$('#work_dir_type').change(function(){
		switch ($('#work_dir_type').val()) {
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
		 $('#work_dir_diff').keyup(function () { alert('test'); });
	}).change();
 $('#submit').hide();
 });

 function clickfix() { 	 $('#submit').show(); }
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
			
				<?php html_titleline_checkbox("enable", "Bhyve virtual machines", $pconfig['enable'], gettext("Enable"), "clickfix()" );?>
				
				<tr id='machines_tr'><td colspan='2' valign='top' class='vtable'>
					<?php //if( isset( $config['thebrig']['rootfolder'])==false): ?>
						<!--	<a title=<?=gettext("Configure TheBrig please first");?> -->
					<?php // elseif( isset( $config['thebrig']['content'])==false): ?>
						<!--	<a title=<?=gettext("Configure at least one jail first");?>	-->			
					<?php // else: ?>
								<table id = 'onlinetable' width="100%" border="0" cellpadding="5" cellspacing="0">
						
									<tr><td width="15%"  class="listhdrlr" >Name</td>
										<td width="15%" class="listhdrc">Path</td>
										<td width="15%" class="listhdrc">System</td>
										<td width="35%" class="listhdrc">Actions</td>
										<td width="20%" class="listhdrc"></td>
									</tr>
									<tr>
									<td class="list" colspan="5"></td>
									<td class="list">
										<a href="extensions_bhyve_edit.php"><img src="plus.gif" title="<?=gettext("Add machine");?>" border="0" alt="<?=gettext("Add machine");?>" /></a>
										
									</td>
								</tr>
						<?php 
						//foreach( $config['thebrig']['content'] as $n_jail): 
						//for ($k=1; $k <= count($config['thebrig']['content']); $k++) : ?>					
						<!--			<tr name='myjail<?=$n_jail['jailno']; ?>' id='myjail<?=$n_jail['jailno']; ?>'>
										<td width="7%" valign="top" class="listlr" name="ajaxjailname<?=$k; ?>"  id="ajaxjailname<?=$k; ?>" >  </td>
									    <td width="15%" valign="top" class="listr" name="ajaxjailbuilt<?=$k; ?>" ><span><img id="ajaxjailbuiltimg<?=$k; ?>" src="status_disabled.png" border="0" alt="template?" /> </span><span id="ajaxjailbuiltports<?=$k; ?>"></span><span id="ajaxjailbuiltsrc<?=$k; ?>"></span> </td>
									    <td width="24%" valign="top" class="listrc" name="ajaxjailstatus<?=$k; ?>"  > <span><img id="ajaxjailstatusimg<?=$k; ?>" src="status_disabled.png" border="0" alt="Stopped" /> </span><span id="ajaxjailstatus<?=$k; ?>"></span></td>
									    <td width="5%" valign= "top" class="listrc" name="ajaxjailid<?=$k; ?>" id="ajaxjailid<?=$k; ?>"></td>
									    <td width="22%" valign="top" class="listrc" name="ajaxjailip<?=$k; ?>" id="ajaxjailip<?=$k; ?>">  <img id="ajaxjailipimg<?=$k; ?>" src="status_disabled.png" border="0" alt="Stopped" /></td>
									    <td width="12%" valign="top" class="listrc" name="ajaxjailhostname<?=$k; ?>" id="ajaxjailhostname<?=$k; ?>"> <img id="ajaxjailhostnameimg<?=$k; ?>" src="status_disabled.png" border="0" alt="Stopped" /></td>
									    <td width="22%" valign="top" class="listrc" name="ajaxjailpath<?=$k; ?>" id="ajaxjailpath<?=$k; ?>"><img id="ajaxjailpathimg<?=$k; ?>" src="status_disabled.png" border="0" alt="Stopped" /> </td>
										<td width="5%" valign="top" class="listrd" name="ajaxjailcmd<?=$k; ?>" id="ajaxjailcmd<?=$k; ?>"><span><img id="ajaxjailcmdimg<?=$k; ?>" class="jail_start" src="ext/thebrig/on_small.png" border="0" alt="Jail start" /> </span></td>	
										</td>
									 </tr>  -->
			<?php //endfor; ?>
								</table>
					<?php // endif;?>
				</td></tr>
			<?php html_separator();  ?>
				</tr>
				<?php html_titleline("Framework configuration");
					$work_dir_type = array('0' => 'Home', '2'=>'Different folder');
					if (true == bhyve_zfs_check()) { $work_dir_type[1] = 'Dataset'; }
					html_combobox("work_dir_type", "Working folder", $pconfig['work_dir_type'], $work_dir_type, "Virtual machines will store on dataset", false, false, "clickfix()"); 

				html_text("work_dir_simple", "", "Virtual machines will store at extension home folder");
				   if ( FALSE !== ($datasets_list = bhyve_datasets_list())) {
					    for ($i = 0; $i < count($datasets_list); ++$i) { 
					 $a_datasets_list[$datasets_list[$i][1]] = $datasets_list[$i][1];
					}
				html_combobox("work_dir_zfs", "", $pconfig['work_dir'], $a_datasets_list, "Virtual machines will store on dataset", false, false, "clickfix()"); } ?>
				<tr id='work_dir_diff_tr'>
					<td width='22%' valign='top' class='vncell'><label for='work_dir_diff'></label></td>
					<td width='78%' class='vtable'>
						<input name='work_dir_diff' type='text' class='formfld' id='work_dir_diff' size='67' value='<?=$pconfig['work_dir'];?>' onchange='clickfix()' />
						<input name='work_dir_diffbrowsebtn' type='button' class='formbtn' id='work_dir_diffbrowsebtn' onclick='work_dir_diffifield = form.work_dir_diff; filechooser = window.open("filechooser.php?p="+encodeURIComponent(work_dir_diffifield.value)+"&amp;sd=/mnt", "filechooser", "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300"); filechooser.ifield = work_dir_diffifield; window.ifield = work_dir_diffifield;' value='...' />
						<br /><span class='vexpl'>Virtual machines will store on any different folder</span>
					</td>
				</tr>
				<?php //html_filechooser('work_dir_diff', "", $pconfig['work_dir'], "Virtual machines will store on any different folder", "/mnt", false, "67", false,"clickfix()"); ?>
				<?php html_combobox("delay", "Delay on boot",$pconfig['delay'], array ("1","2","3","4","5","6","7","8"), "Choise delay time between start machines at boot stage", false, false, "clickfix()");?>
			</table>
			<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="Save"  />
			</div>
			<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<?php include("fend.inc"); ?>
