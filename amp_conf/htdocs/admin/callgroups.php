<?php
//Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.


//script to write extensions_additional.conf file from mysql
$wScript1 = rtrim($_SERVER['SCRIPT_FILENAME'],$currentFile).'retrieve_extensions_from_mysql.pl';
	
$action = $_REQUEST['action'];
$extdisplay=$_REQUEST['extdisplay'];
$dispnum = 4; //used for switch on config.php

//add group
if ($action == 'addGRP') {
	
	$account = $_REQUEST['account'];
	$grplist = $_REQUEST['grplist'];
	$grptime = $_REQUEST['grptime'];
	$grppre = $_REQUEST['grppre'];
	
	$addarray = array('ext-group',$account,'1','Setvar','GROUP='.$grplist,'','0');
	addextensions($addarray);
	$addarray = array('ext-group',$account,'2','Setvar','RINGTIMER='.$grptime,'','0');
	addextensions($addarray);
	$addarray = array('ext-group',$account,'3','Setvar','PRE='.$grppre,'','0');
	addextensions($addarray);
	$addarray = array('ext-group',$account,'4','Macro','rg-group','','0');
	addextensions($addarray);
	
	
			$goto = $_REQUEST['goto0'];
			if ($goto == 'extension') {
				$args = 'ext-local,'.$_REQUEST['extension'].',1';
				$addarray = array('ext-group',$account,'5','Goto',$args,'','0'); 
			}
			elseif ($goto == 'voicemail') {
				$args = 'vm,'.$_REQUEST['voicemail'];
				$addarray = array('ext-group',$account,'5','Macro',$args,'','0');
			}
			elseif ($goto == 'ivr') {
				$args = 'aa_'.$_REQUEST['ivr'].',s,1';
				$addarray = array('ext-group',$account,'5','Goto',$args,'','0');
			}
			elseif ($goto == 'group') {
				$args = 'ext-group,'.$_REQUEST['group'].',1';
				$addarray = array('ext-group',$account,'5','Goto',$args,'','0');
			}
			elseif ($goto == 'custom') {
			        $args = $_REQUEST['custom_args'];
			        $addarray = array('ext-group',$account,'5','Goto',$args,'','0');
            }
	
	addextensions($addarray);
	
	
	//write out extensions_additional.conf
	exec($wScript1);
	
	//indicate 'need reload' link in header.php 
	needreload();
}

//del group
if ($action == 'delGRP') {
	delextensions('ext-group',ltrim($extdisplay,'GRP-'));
	
	//write out extensions_additional.conf
	exec($wScript1);
	
	//indicate 'need reload' link in header.php 
	needreload();
}

//edit group - just delete and then re-add the extension
if ($action == 'edtGRP') {
	
	$account = $_REQUEST['account'];
	$grplist = $_REQUEST['grplist'];
	$grptime = $_REQUEST['grptime'];
	$grppre = $_REQUEST['grppre'];

		delextensions('ext-group',$account);
		
		$addarray = array('ext-group',$account,'1','Setvar','GROUP='.$grplist,'','0');
		addextensions($addarray);
		$addarray = array('ext-group',$account,'2','Setvar','RINGTIMER='.$grptime,'','0');
		addextensions($addarray);
		$addarray = array('ext-group',$account,'3','Setvar','PRE='.$grppre,'','0');
		addextensions($addarray);
		$addarray = array('ext-group',$account,'4','Macro','rg-group','','0');
		addextensions($addarray);
		
		
				$goto = $_REQUEST['goto0'];
				if ($goto == 'extension') {
					$args = 'ext-local,'.$_REQUEST['extension'].',1';
					$addarray = array('ext-group',$account,'5','Goto',$args,'','0'); 
				}
				elseif ($goto == 'voicemail') {
					$args = 'vm,'.$_REQUEST['voicemail'];
					$addarray = array('ext-group',$account,'5','Macro',$args,'','0');
				}
				elseif ($goto == 'ivr') {
					$args = 'aa_'.$_REQUEST['ivr'].',s,1';
					$addarray = array('ext-group',$account,'5','Goto',$args,'','0');
				}
				elseif ($goto == 'group') {
					$args = 'ext-group,'.$_REQUEST['group'].',1';
					$addarray = array('ext-group',$account,'5','Goto',$args,'','0');
				}
				elseif ($goto == 'custom') {
                	$args = $_REQUEST['custom_args'];
                    $addarray = array('ext-group',$account,'5','Goto',$args,'','0');
                }
		
		addextensions($addarray);
		
		//write out extensions_additional.conf
		exec($wScript1);
		
		//indicate 'need reload' link in header.php 
		needreload();

}

?>
</div>

<div class="rnav">
    <li><a id="<? echo ($extdisplay=='' ? 'current':'') ?>" href="config.php?display=<?echo $dispnum?>">Add Ring Group</a><br></li>
<?
//get unique ring groups
$gresults = getgroups();

foreach ($gresults as $gresult) {
    echo "<li><a id=\"".($extdisplay=='GRP-'.$gresult[0] ? 'current':'')."\" href=\"config.php?display=".$dispnum."&extdisplay=GRP-{$gresult[0]}\">Ring Group {$gresult[0]}</a></li>";
}
?>
</div>

<div class="content">
<?

		
		if ($action == 'delGRP') {
			echo '<br><h3>Group '.ltrim($extdisplay,'GRP-').' deleted!</h3><br><br><br><br><br><br><br><br>';
		} else {
			
			//query for exisiting aa_N contexts
			$unique_aas = getaas();
			//get unique extensions
			$extens = getextens();
			//get unique ring groups
			$gresults = getgroups();
	
			//get extensions in this group
			$thisGRP = getgroupextens(ltrim($extdisplay,'GRP-'));
			//get ringtime for this group
			$thisGRPtime = getgrouptime(ltrim($extdisplay,'GRP-'));
			//get goto for this group
			$thisGRPgoto = getgroupgoto(ltrim($extdisplay,'GRP-'));
			//get prefix for this group
			$thisGRPprefix = getgroupprefix(ltrim($extdisplay,'GRP-'));

			$delURL = $_REQUEST['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delGRP';
	?>
			<h2>Ring Group: <? echo ltrim($extdisplay,'GRP-'); ?></h2>
<?		if ($extdisplay){ ?>
			<p><a href="<? echo $delURL ?>">Delete Group <? echo ltrim($extdisplay,'GRP-'); ?></a></p>
<?		} ?>
			<form name="editGRP" action="<? $_REQUEST['PHP_SELF'] ?>" method="post">
			<input type="hidden" name="display" value="<?echo $dispnum?>">
			<input type="hidden" name="action" value="<? echo ($extdisplay ? 'edtGRP' : 'addGRP') ?>">
			<table>
			<tr><td colspan="2"><h5><? echo ($extdisplay ? 'Edit Ring Group' : 'Add Ring Group') ?><hr></h5></td></tr>
			<tr>
				<td><a href="#" class="info">group number:<span>The number users will dial to ring extensions in this ring group</span></a></td>
				<td><input size="5" <? echo ($extdisplay ? 'disabled="true"' : '') ?> type="text" name="account" value="<? echo ($extdisplay ? ltrim($extdisplay,'GRP-') : $gresult[0] + 1 ); ?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">extension list:<span>Separate extensions with a | (pipe) character. Ex: 201|202|203<br><br>You can include an extension on a remote system, or an external number by including an asterisk (*) after the dial prefix for the appropriate trunk.  ex:  9*2448089 would dial 2448089 on the trunk accessible with dial prefix 9.<br><br></span></a></td>
				<td><input type="text" name="grplist" value="<? echo substr($thisGRP[0][0],6) ?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">CID name prefix:<span>You can optionally prefix the Caller ID name when ringing extensions in this group. ie: If you prefix with "Sales:", a call from John Doe would display as "Sales:John Doe" on the extensions that ring.</span></a></td>
				<td><input size="4" type="text" name="grppre" value="<? echo substr($thisGRPprefix[0][0],4) ?>"></td>
			</tr><tr>
				<td>ring time (max 60 sec):</td>
				<td><input size="4" type="text" name="grptime" value="<? echo substr($thisGRPtime[0][0],10) ?>"></td>
			</tr>
			<tr><td colspan="2"><br><h5>Destination if no answer:<hr></h5></td></tr>
			<tr>
				<td colspan=2>
				<input type="hidden" name="goto0" value="">				
				<input type="radio" name="goto_indicate" value="ivr" disabled="true" <? echo strpos($thisGRPgoto[0][0],'aa_') === false ? '' : 'CHECKED=CHECKED';?> /> Digital Receptionist: 
				
				<select name="ivr" onclick="javascript:document.editGRP.goto_indicate[0].checked=true;"/>
			<?
				foreach ($unique_aas as $unique_aa) {
					$menu_num = substr($unique_aa[0],3);
					$menu_name = $unique_aa[1];
					echo '<option value="'.$menu_num.'" '.(strpos($thisGRPgoto[0][0],'aa_'.$menu_num) === false ? '' : 'SELECTED').'>'.($menu_name ? $menu_name : 'Menu #'.$menu_num);
				}
			?>
				</select><br>
				<input type="radio" name="goto_indicate" value="extension" disabled="true" <? echo strpos($thisGRPgoto[0][0],'ext-local') === false ? '' : 'CHECKED=CHECKED';?>/> Extension: 
				<select name="extension" onclick="javascript:document.editGRP.goto_indicate[1].checked=true;"/>
			<?
				foreach ($extens as $exten) {
					echo '<option value="'.$exten[0].'" '.(strpos($thisGRPgoto[0][0],$exten[0]) === false ? '' : 'SELECTED').'>#'.$exten[0];
				}
			?>		
				</select><br>
				<input type="radio" name="goto_indicate" value="voicemail" disabled="true" <? echo strpos($thisGRPgoto[0][0],'vm') === false ? '' : 'CHECKED=CHECKED';?> /> Voicemail: 
				<select name="voicemail" onclick="javascript:document.editGRP.goto_indicate[2].checked=true;"/>
			<?
				foreach ($extens as $exten) {
					echo '<option value="'.$exten[0].'" '.(strpos($thisGRPgoto[0][0],$exten[0]) === false ? '' : 'SELECTED').'>#'.$exten[0];
				}
			?>		
				</select><br>
				<input type="radio" name="goto_indicate" value="group" disabled="true" <? echo strpos($thisGRPgoto[0][0],'ext-group') === false ? '' : 'CHECKED=CHECKED';?> /> Ring Group: 
				<select name="group<? echo $i ?>" onclick="javascript:document.editGRP.goto_indicate[3].checked=true;"/>
			<?
				foreach ($gresults as $gresult) {
					echo '<option value="'.$gresult[0].'" '.(strpos($thisGRPgoto[0][0],$gresult[0]) === false ? '' : 'SELECTED').'>#'.$gresult[0];
				}
			?>			
				</select><br>
				<input type="radio" name="goto_indicate" value="custom" disabled="true" <? echo strpos($thisGRPgoto[0][0],'custom') === false ? '' : 'CHECKED=CHECKED';?> /><a href="#" class="info"> Custom App<span><br>ADVANCED USERS ONLY<br><br>Uses Goto() to send caller to a custom context.<br><br>The context name <b>MUST</b> contain the word "custom" and should be in the format custom-context , extension , priority. Example entry:<br><br><b>custom-myapp,s,1</b><br><br>The <b>[custom-myapp]</b> context would need to be created and included in extensions_custom.conf<b><b></span></a>:
                <input type="text" size="15" name="custom_args" onclick="javascript:document.editGRP.goto_indicate[4].checked=true;" value="<? echo strpos($thisGRPgoto[0][0],'custom') === false ? '' : $thisGRPgoto[0][0]; ?>" />
                <br>
				
				</td>
				
			</tr><tr>
			<td colspan="2"><br><h6><input name="Submit" type="button" value="Submit Changes" onclick="checkGRP(editGRP);"></h6></td>		
			
			</tr>
			</table>
			</form>
<?		
		} //end if action == delGRP
		

?>

<? //Make sure the bottom border is low enuf
foreach ($gresults as $gresult) {
    echo "<br>";
}
?>




