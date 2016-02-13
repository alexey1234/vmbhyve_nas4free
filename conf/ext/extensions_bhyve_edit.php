<?php
/*
extensions_bhyve_edit.php
this page may be switch in small window 
*/
require("auth.inc");
require_once("guiconfig.inc");
require_once($config['bhyve']['homefolder']."/conf/ext/extensions_bhyve_functions.inc");
$pgtitle = array("Extensions", "Virtual Machine BHYVE", "Edit");
include("fbegin.inc");
?>
<script type="text/javascript">//<![CDATA[
function redirect() { window.location = "extensions_bhyve.php" }
//]]>
</script>

<link href="gui.css" rel="stylesheet" type="text/css">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<form action="extensions_bhyve_edit.php" method="post" name="iform" id="iform">
		<td class="tabcont">
		<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
			<table width="100%" border="0" cellpadding="5" cellspacing="0">
				<tr>
					<?php html_titleline("Bhyve  - Virtual machine configuration");?>
					<?php html_text("remark", "Attension","<b><font color ='red'>THIS IS TEST ONLY now</font></b>");?>
					<?php html_inputbox("vmname", gettext("Name"), $pconfig['vmname'], "Define name of virtual machine", true, 16,false);?>
					<?php html_combobox("guest", "VM guest OS", $pconfig['vmguest'], array("freebsd","netbsd","openbsd","centos","centos-grub","alpine","ubuntu","debian","windows","generic"), "The type of operating system this virtual machine will use. <br /> Different systems often require specific steps to load the virtual machine, <br /> or specific bhyve options. <br />The generic option does no specific loading and can be used for standard uefi guests.", true, false);?>
					<?php html_inputbox("vmuefi", gettext("UEFI"), $pconfig['vmuefi'], "Tells bhyve that it should load the UEFI firmware. To use UEFIthis can be any <br />value other than <b>[empty]/off/false/no/0</b>. If it contains the string 'csm',<br /> the UEFI BIOS compatability (CSM), firmware will be used", false, 16,false);?>
					<?php $cpuinfo = system_get_cpu_info(); $cpucount = array();
					for ($i = 1; $i <= $cpuinfo['number']; $i++) {$cpucount[] = $i; }
					 html_combobox("cpucount", "CPU", $pconfig['cpucount'], $cpucount, "Specify the number of cpu cores to give to the guest", true, false);?>
					<?php html_inputbox("vmmemory", gettext("Memory"), $pconfig['vmmemory'], "specify the amount of ram to give to the guest. This can be followed by M or G.", true, 4,false);?>
					<?php html_combobox("vmhostbridge", "Hostbridge", $pconfig['vmhostbridge'], array("standard","amd","none"), "Allows you to specify the type of hostbridge to use for the guest hardware. This can <br />usually be left as default. The additional options are 'amd', for a hostbridge that<br /> advertises itself as AMD hardware and 'none' for no hostbridge. <br />
					<b>Note</b>: there is no requirement to use the 'amd' hostbridge if you host has <br />an AMD processor", false, false);?>
					<?php html_combobox("vmcomports", "Com ports for VM", $pconfig['vmcomports'], array("com1","com2","com1 com2","com2 com1"), "This allows you to define the com ports which should be available.<br />
By default only com1 is connected, and can be accessed using the 'vm console'<br />
command. If more than one com port is specified, you can choose the port <br />
to connect to by running <i>vm console guest com1|com2</i>. When using the<br />
<i>vm console</i> command, if no com port is specified, you are connected <br />to the first port listed in this string.", false, false);?>
					

					<?php html_timezonecombobox("utctime", gettext("utc time"), $pconfig['utctime'], gettext("Set to any value if you want the guest clock to use UTC time."), false, 16,false);?>
					<?php html_checkbox("vmdebug", "Debug", $pconfig['vmdebug'], "Set  to run vm-bhyve in debug mode.", "In this mode, all output from the bhyve process is written to<br />
vm_dir/{guest}/bhyve.log. This is useful if the guest is crashing or<br />exiting abnormally as the log will contain any output from bhyve.", false, "");?>
					<?php html_combobox("vmdisk0_type", "Disk0 type", $pconfig['vmdisk0_type'], array("virtio-blk","ahci-hd"), 
					"This specifies the emulation type for disk0. Please note that each disk <br />
					requires at least a type and name.", true, false);?>
					<?php html_combobox("vmdisk0_dev", "Disk0 device", $pconfig['vmdisk0_dev'], array("file","zvol","sparse-zvol","custom"), 
					"The type of device used as the backing store for this disk. The default is <i>file</i>,<br />
					which means a sparse file is used. This file is stored in the guest's directory.<br />
					For the zvol options, the zvol must be directly under the guest dataset.<br />
					There is also a <i>custom</i> option, in which case the disk name should be <br />
					the full path to the file or device you want to use.", true, false);?>
					<?php html_filechooser('vmdisk0_name', "Disk0 name", $pconfig['vmdisk0_name'], 	"The name of the file or zvol for this disk. If the device type is 'custom',<br />
					it should be the full path to whichever device or file you want to use<br />This value is translated to a path as follows, based on disk0_dev<br />
					<table><tr><td>DEVICE TYPE</td><td>DISK NAME</td><td>BHYVE PATH USED</td></tr>
					<tr><td>file</td><td>'disk0.img'</td> <td>'vm_dir/name/disk0.img'</td></tr>
					<tr><td>zvol|sparse-zvol</td><td>'disk0'</td><td>'/dev/zvol/pool/dataset/path/guest/disk0'</td></tr>
					<tr><td>custom</td><td>'/dev/da10'</td><td>'/dev/da10'</td></tr></table>", "/", false, "67", false,""); ?>
					<?php html_optionsbox("disk0_opts", "Disk0 options", $pconfig['disk0_opts'], array("direct","nocache","ro","sectorsize=logical","sectorsize=physical"), false, false) ; ?>
					


					
		
				</tr>	
				
				<tr>
				</tr>
				<tr><td>
					<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>" />
					
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
					<input type="button" style = "font-family:Tahoma,Verdana,Arial,Helvetica,sans-serif;font-size: 11px;font-weight:bold;" value="<?=gettext("Cancel");?>" onclick="redirect()" />
					</div>
				    </td>
				</tr>
			
			</table>
		</td>
		<?php include("formend.inc");
		
		?>
		</form>
	</tr>

	
</table>
<?php include("fend.inc"); ?>