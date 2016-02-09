<?php
// extensions_bhyve_functions.inc

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