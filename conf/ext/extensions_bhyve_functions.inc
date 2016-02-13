<?php
// extensions_bhyve_functions.inc

class HTMLOptionsBox extends HTMLBaseControl {
	var $j_option = "";
	
	function __construct($ctrlname, $title, $value ) {
		parent::__construct($ctrlname, $title, $value );
	}
	function Getj_option() {
		return $this->j_option;
	}

	function Setj_option($j_option) {
		$this->j_option = $j_option;
	}
	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$options = $this->Getj_option(); 

		echo "    <script type='text/javascript'>\n";
		echo "    //<![CDATA[\n";
		echo "   function SelectMoveRows(SS1,SS2) {\n";
		echo "      var SelID='';\n";
		echo "      var SelText='';\n";
		echo "      for (i=SS1.options.length - 1; i>=0; i--) {\n";
		echo "       	if (SS1.options[i].selected == true) {\n";
		echo "      		SelID=SS1.options[i].value;\n";
		echo "       		SelText=SS1.options[i].text;\n";
		echo "      		var newRow = new Option(SelText,SelID);\n";
		echo "      		SS2.options[SS2.length]=newRow;\n";
		echo "       		SS1.options[i]=null;\n";
		echo "         		}\n";
		echo "        	}\n";
		echo "      	SelectSort(SS2);\n";
		echo "      }\n";
		echo "      function SelectSort(SelList) {\n";
		echo "      	var ID='';\n";
		echo "   	var Text='';\n";
		echo "    	for (x=0; x < SelList.length - 1; x++) {\n";
		echo "      		for (y=x + 1; y < SelList.length; y++) {\n";
		echo "      			if (SelList[x].text > SelList[y].text) {\n";
	//	echo "      				if (valuetype != '') {\n";
		echo "      ID=SelList[x].value;\n";
		echo "        Text=SelList[x].text;\n";
		echo "      SelList[x].value=SelList[y].value;\n";
		echo "       SelList[x].text=SelList[y].text;\n";
		echo "        SelList[y].value=ID;\n";
		echo "        SelList[y].text=Text;\n";
		echo "        } } } }\n";
		echo "    function onsubmit_{$ctrlname}() {\n";
		echo "      var element = document.getElementById('{$ctrlname}');\n";
		echo "      for (var i = 0; i < element.length; i++) {\n";
		echo "        if (element.options[i].value != '')\n";
		echo "          element.options[i].selected = true;\n";
		echo "      }\n";
		echo "    }\n";
		echo "    //]]>\n";
		echo "    </script>\n";
		echo "    <table><tr><td width='40%' color='red'><center><b>Disk options</b></center>\n";
		echo "    <select name='{$ctrlname}[]' class='formfld' id='{$ctrlname}' multiple='multiple' size='8' style='width: 350px' >\n";
		if (isset($value)) { foreach ($value as $valuek => $valuev) {
			echo "      <option value='{$valuev}' >{$valuev}</option>\n";
				      }
			}
		echo "    </select>\n";
		echo "    </td>\n";
		echo "    <td width='20%'>\n";
		echo "    <center><input name='{$ctrlname}addbtn' type='button' class='formbtn' id='{$ctrlname}addbtn' value='".htmlspecialchars(gettext(" << "), ENT_QUOTES)."' onClick='SelectMoveRows(document.iform.{$ctrlname}optiontype,document.iform.{$ctrlname})' /></center><br />\n";
		
		echo "    <center><input name='{$ctrlname}deletebtn' type='button' class='formbtn' id='{$ctrlname}deletebtn' value='".htmlspecialchars(gettext(" >> "), ENT_QUOTES)."' onClick='SelectMoveRows(document.iform.{$ctrlname},document.iform.{$ctrlname}optiontype)' /></center>\n";
		echo "    </td>\n";
		echo "    <td width='40%'><center>Options </center>\n";
		echo "    <select name='{$ctrlname}optiontype' class='formfld' id='{$ctrlname}optiontype' multiple='multiple' size='8' style='width: 350px'> ";

	if (is_array($value)) {
		$options1 = array_diff($options, $value);
		foreach ($options1 as $optionsv) {
		echo "      <option value='{$optionsv}' {$optparam}>{$optionsv}</option>\n";
			}
		echo "    </td></tr></table>\n";
		} else {
		foreach ($options as $optionsv) {
		echo "      <option value='{$optionsv}' {$optparam}>{$optionsv}</option>\n";
			}
		echo "    </td></tr></table>\n";
		}
	}
}
function html_optionsbox($ctrlname, $title, $value, $j_opt, $required = false, $readonly = false) {
	$ctrl = new HTMLOptionsBox($ctrlname, $title, $value);
	//$ctrl->GetOptions($options);
	$ctrl->Setj_option($j_opt);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

// cmd_exec execute process and have output and error
//$cmd - proccess 
//$stdout - result
//$stderr - messages about error
//Exported from TheBrig


function cmd_exec($cmd, &$stdout, &$stderr)
{
    $outfile = tempnam("/tmp", "cmd");
    $errfile = tempnam("/tmp", "cmd");
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("file", $outfile, "w"),
        2 => array("file", $errfile, "w")
    );
    $proc = proc_open($cmd, $descriptorspec, $pipes);
    
    if (!is_resource($proc)) return 255;

    fclose($pipes[0]);    //Don't really want to give any input

    $exit = proc_close($proc);
    $stdout = file($outfile);
    $stderr = file($errfile);

    unlink($outfile);
    unlink($errfile);
    return $exit;
}
function bhyve_zfs_check() {	
	$pool_names = array(); $dataset_names = array(); $errors = array(); 
	cmd_exec("zpool list -Ho name",$pool_names, $errors);
	cmd_exec("zfs list -Ho jailed,name,mountpoint",$dataset_names, $errors);
	if ( 0 < ( (count($dataset_names)) - (count($pool_names)) ) ){ return true; } else { return false;}
}
function bhyve_datasets_list() {
	global $config;
	$pool_names = array(); $dataset_names = array(); $errors = array(); 
	// Using the -H option removes the headers, and uses a single \t between
	// column entries
	cmd_exec("zpool list -Ho name",$pool_names, $errors);
	cmd_exec("zfs list -t filesystem -Ho jailed,name,mountpoint",$dataset_names, $errors);
	$pool_count = count($pool_names);
	$dataset_count = count($dataset_names);
	$unique_count = $dataset_count - $pool_count ;
	if (0 < $unique_count ){
		for ($i=0; $i < $dataset_count;$i++) { 
			$cell[$i] = explode ( "\t" , $dataset_names[$i]);
			for ($k=0; $k<$pool_count;$k++){
				// Remove the pools from the list of datasets
				if ( strcmp( $cell[$i][1] , rtrim($pool_names[$k]) ) === 0 ) {
					unset ( $cell[$i]);
				}
			}
		}
		foreach ($cell as $cells ) { $cell1[] = array_filter($cells); }
		for ($i=0; $i< count($cell1);) { 
			$pattern1=$cell1[$i][2];
			$pattern1 = preg_quote($pattern1, '/' );
			$pattern = "/".$pattern1."/"; 
			$match = preg_match($pattern, $config['thebrig']['rootfolder']);
			if (1 === $match) { unset ($cell1[$i]); } 
			$i++;
			}
		$cell1 =  array_filter($cell1);
		foreach ($cell1 as $cells ) { $datasets_list[] = array_filter($cells); }
	return $datasets_list;
	} else { return false; }
}
?>