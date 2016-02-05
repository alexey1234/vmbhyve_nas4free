<?php
/*
extensions_bhyve_manual.php
*/
require("auth.inc");
require("guiconfig.inc");
$pgtitle = array("Extensions", "Virtual Machine BHYVE", "Manual");

include("fbegin.inc");
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" >
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="extensions_bhyve.php"><span><?="VM Bhyve";?></span></a></li>
			
			<li class="tabinact"><a href="extensions_bhyve_config.php"><span><?="Extension config";?></span></a></li>
			<li class="tabact"><a href="extensions_bhyve_manual.php"><span><?="VM Manual";?></span></a></li>
					</span> </a>
				</li>
		</ul>
	</td></tr>
	<tr>
		<td class="tabcont">
			
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<?php
			html_text("my", "Notes for extemsion", file_get_contents($config['bhyve']['homefolder']."/conf/ext/my.txt"));
			
			html_text('text5', "Manual", file_get_contents($config['bhyve']['homefolder']."/conf/ext/vm.html"));
			
			?>
			</table>
			
		</td>
	</tr>
</table>
<?php include("fend.inc"); ?>
?>
