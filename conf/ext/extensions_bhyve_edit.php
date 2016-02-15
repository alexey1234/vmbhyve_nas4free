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
					<?php html_textinfo("remark", "Attension","<b><font color ='red'>THIS IS TEST ONLY now</font></b>");?>
					<?php html_inputbox("vmname", gettext("Name"), $pconfig['vmname'], "Define name of virtual machine", true, 16,false);?>
					<?php html_combobox("guest", "VM guest OS", $pconfig['vmguest'], array("freebsd","netbsd","openbsd","centos","centos-grub","alpine","ubuntu","debian","windows","generic"), "The type of operating system this virtual machine will use.  Different systems often require specific steps to load the virtual machine,  or specific bhyve options. The generic option does no specific loading and can be used for standard uefi guests.", true, false);?>
					<?php html_inputbox("vmuefi", gettext("UEFI"), $pconfig['vmuefi'], "Tells bhyve that it should load the UEFI firmware. To use UEFIthis can be any value other than <b>[empty]/off/false/no/0</b>. If it contains the string 'csm', the UEFI BIOS compatability (CSM), firmware will be used", false, 16,false);?>
					<?php $cpuinfo = system_get_cpu_info(); $cpucount = array();
					for ($i = 1; $i <= $cpuinfo['number']; $i++) {$cpucount[] = $i; }
					 html_combobox("cpucount", "CPU", $pconfig['cpucount'], $cpucount, "Specify the number of cpu cores to give to the guest", true, false);?>
					<?php html_inputbox("vmmemory", gettext("Memory"), $pconfig['vmmemory'], "specify the amount of ram to give to the guest. This can be followed by M or G.", true, 4,false);?>
					<?php html_combobox("vmhostbridge", "Hostbridge", $pconfig['vmhostbridge'], array("standard","amd","none"), "Allows you to specify the type of hostbridge to use for the guest hardware. This can usually be left as default. The additional options are 'amd', for a hostbridge that advertises itself as AMD hardware and 'none' for no hostbridge. 
					<br /><b>Note</b>: there is no requirement to use the 'amd' hostbridge if you host has an AMD processor", false, false);?>
					<?php html_combobox("vmcomports", "Com ports for VM", $pconfig['vmcomports'], array("com1","com2","com1 com2","com2 com1"), "This allows you to define the com ports which should be available.
By default only com1 is connected, and can be accessed using the 'vm console' command. If more than one com port is specified, you can choose the port 
to connect to by running <i>vm console guest com1|com2</i>. When using the <i>vm console</i> command, if no com port is specified, you are connected to the first port listed in this string.", false, false);?>
					

					<?php html_timezonecombobox("utctime", gettext("utc time"), $pconfig['utctime'], gettext("Set to any value if you want the guest clock to use UTC time."), false, 16,false);?>
					<?php html_checkbox("vmdebug", "Debug", $pconfig['vmdebug'], "Set  to run vm-bhyve in debug mode.", "In this mode, all output from the bhyve process is written to
vm_dir/{guest}/bhyve.log. This is useful if the guest is crashing or exiting abnormally as the log will contain any output from bhyve.", false, "");?>
					<?php html_combobox("vmdisk0_type", "Disk0 type", $pconfig['vmdisk0_type'], array("virtio-blk","ahci-hd"), 
					"This specifies the emulation type for disk0. Please note that each disk requires at least a type and name.", true, false);?>
					<?php html_combobox("vmdisk0_dev", "Disk0 device", $pconfig['vmdisk0_dev'], array("file","zvol","sparse-zvol","custom"), 
					"The type of device used as the backing store for this disk. The default is <i>file</i>, which means a sparse file is used. This file is stored in the guest's directory.
					For the zvol options, the zvol must be directly under the guest dataset.
					There is also a <i>custom</i> option, in which case the disk name should be the full path to the file or device you want to use.", true, false);?>
					<?php html_filechooser('vmdisk0_name', "Disk0 name", $pconfig['vmdisk0_name'], 	"The name of the file or zvol for this disk. If the device type is 'custom', it should be the full path to whichever device or file you want to use. This value is translated to a path as follows, based on disk0_dev<br />
					<table border='3'><tr><td><b>DEVICE TYPE</b></td><td><b> DISK NAME </b></td><td><b> BHYVE PATH USED </b></td></tr>
					<tr><td>file</td><td>'disk0.img'</td> <td>'vm_dir/name/disk0.img'</td></tr>
					<tr><td>zvol|sparse-zvol</td><td>'disk0'</td><td>'/dev/zvol/pool/dataset/path/guest/disk0'</td></tr>
					<tr><td>custom</td><td>'/dev/da10'</td><td>'/dev/da10'</td></tr></table>", "/", false, "67", false,""); ?>
					<?php //html_optionsbox("disk0_opts", "Disk0 options", $pconfig['disk0_opts'], array("direct","nocache","ro","sectorsize=logical","sectorsize=physical"), false, false) ; ?>
					<?php html_listbox("disk0_opts", "Disk0 options", $pconfig['disk0_opts'], array("direct","nocache","ro","sectorsize=logical","sectorsize=physical"), "List of additional options for the specified disk.
The available options are listed below. See the <a href='https://www.freebsd.org/cgi/man.cgi?query=bhyve&sektion=8'>bhyve(8) man page</a> for more details", false, false, "");?>
					<?php html_inputbox("network0_switch", gettext("network0 switch"), empty($pconfig['network0_switch']) ? "public" : $pconfig['network0_switch'], "The name of the virtual switch to connect this interface to. When starting the guest, if this switch cannot be found, or no switch is specified, the interface
is still created but will not be connected to anything. All default templates use a switch called 'public', although it's perfectly reasonable to use other switch names that make sense in your environment", false, 16,false);?>
					<?php html_bhyveinterfacecombobox("network0_device", "network0 device", $pconfig['network0_device'], "If you do not want vm-bhyve to create a new interface, but use an existing
one, enter the interface name here. This allows you to preconfigure the network
device in a custom configuration, then instruct vm-bhyve to use that rather
than create all interfaces dynamically at run time.", false, false); ?>
					<?php html_inputbox("macaddr", "MAC address", $pconfig['macaddr'], "This allows you to specify a fixed mac address for this interface inside the guest.
Without this option, bhyve will automatically assign a mac address to the interface.", false, 26, false);?>
					<?php $devices = bhyve_pci_devices();
					html_combobox("passthru0", "Add a pass-through PCI device", $pconfig['passthru0'], $devices,"This allows the guest to access a hardware device no differently than if it was running on bare
metal. The value of this option is the Bus/Slot/Function of the appropriate device. Please note that in order to stop the bhyve host from attaching to the device,
there are some steps required to reserve the device in /boot/loader.conf.  More details can be found in the <a href='https://wiki.freebsd.org/bhyve/pci_passthru'>FreeBSD bhyve wiki</a> pages", false, false); ?>
					<?php html_checkbox("virtio_rnd", "Add virtio random", $pconfig['virtio_rnd'], "Check to create a virtio-rnd device for the guest", false, "");?>
					<?php html_textarea("grub_commands", "Grub bootloader commands", $pconfig['grub_commands'], "If you need to use custom grub loader commands, you can use here.
Usually the last entry in the file should be 'boot', followed by a newline", false, 60,5, false, true);?>
					<?php html_inputbox("linux_kernel", "Linux kernel", $pconfig['linux_kernel'], "This option is only used for CentOS guests, when booted using the standard
 grub-bhyve loader. It should contain a value similar to '3.10.0-229.el7.x86_64' which allows vm-bhyve to pass the correct kernel & initrd commands to the CentOS loader.", false, 26, false);?>
					<?php html_inputbox("guest_version", "Guest version", $pconfig['guest_version'], "The version of the guest operating system. This is only really used for
 OpenBSD guests. For OpenBSD the path to the kernel includes the release version, so we need to know the version in advance to be able to send the correct commands to grub-bhyve.", false, 26, false);?>
					<?php html_combobox("arch", "Architecture", $pconfig['arch'], array("amd64" => "amd64","i386" =>"i386"), " This is only used for OpenBSD guests. As well as containing the OS
release version, the loader path also includes the CPU architecture. In order to allow both i386 and amd64 versions of OpenBSD to be booted, this allows you to specify the architecture of the guest operating system
that you are installing", false, false);?>
					

		
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
<?php 
include("fend.inc"); ?>