<?php

if (!defined('e107_INIT')) { exit; }

$plugin_folder_name = 'ecallcentre';

$lan_file = e_PLUGIN."".$plugin_folder_name."/languages/".e_LANGUAGE.".php";
include_once((file_exists($lan_file) ? $lan_file : e_PLUGIN."".$plugin_folder_name."/languages/English.php"));
$ticket_count = getTicketCount();
$mtext = '
<table class="cont">
	<tbody>
		<tr>
			<td class="menu_content non_default">
				<img src="'.e_THEME.'jayya/images/bullet2.gif" alt="bullet" style="vertical-align: middle;">
				<a class="login_menu_link" href="'.e_SELF.'?ClientList">'.eCC_L0020.'</a><br>
				<img src="'.e_THEME.'jayya/images/bullet2.gif" alt="bullet" style="vertical-align: middle;">
				<a class="login_menu_link" href="'.e_SELF.'?HardList">'.eCC_L0025.'</a><br>
				<img src="'.e_THEME.'jayya/images/bullet2.gif" alt="bullet" style="vertical-align: middle;">
				<a class="login_menu_link" href="'.e_SELF.'?TicketList">'.eCC_LM005.' ('.join('/', getTicketCount()).')</a><br>
				<img src="'.e_THEME.'jayya/images/bullet2.gif" alt="bullet" style="vertical-align: middle;">
				<a class="login_menu_link" href="'.e_SELF.'?TicketList///My">'.eCC_LM006.' ('.join('/', getMyTicketCount()).')</a><br>
				<img src="'.e_THEME.'jayya/images/bullet2.gif" alt="bullet" style="vertical-align: middle;">
				<a class="login_menu_link" href="'.e_SELF.'?History">'.eCC_LM007.'</a><br>
				<img src="'.e_THEME.'jayya/images/bullet2.gif" alt="bullet" style="vertical-align: middle;">
				<a class="login_menu_link" href="'.e_SELF.'?Archive">'.eCC_LM008.'</a><br>
				<img src="'.e_THEME.'jayya/images/bullet2.gif" alt="bullet" style="vertical-align: middle;">
				<a class="login_menu_link" href="http://code.google.com/p/ecallcentre/wiki/UserManual">'.eCC_LM002.'</a><br>
			</td>
		</tr>
	</tbody>
</table>
';

$ns -> tablerender(eCC_LM001, $mtext);

function getTicketCount(){
	global $sql;
	if(!is_object($sql)){ $sql = new db; }
	$sql->db_Select("ticket","SUM(IF(status_id = 3,0,1)) AS open_count, SUM(IF(status_id = 3,1,0)) AS close_count, COUNT(*) AS total_count", "1 AND client_id AND hard_id");
	if ($row = $sql->db_Fetch()) {
		return array($row['open_count'], $row['total_count']);
	} else {
		return array('-', '-');
	}
}

function getMyTicketCount(){
	global $sql;
	if(!is_object($sql)){ $sql = new db; }
	$sql->db_Select("ticket","SUM(IF(status_id = 3,0,1)) AS open_count, SUM(IF(status_id = 3,1,0)) AS close_count, COUNT(*) AS total_count", "1 AND client_id AND hard_id AND officer_id = ".USERID."", true);
	if ($row = $sql->db_Fetch()) {
		return array($row['open_count']?$row['open_count']:'0', $row['total_count']?$row['total_count']:'0');
	} else {
		return array('-', '-');
	}
}
?>

