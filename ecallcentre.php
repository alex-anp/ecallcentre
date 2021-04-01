<?php
/*
+ ----------------------------------------------------------------------------------------------+
|     e107 website system  : http://e107.org.ru
|     Released under the terms and conditions of the GNU General Public License (http://gnu.org).
|
|     Plugin "eCallCentre"
|     Author: Alex ANP alex-anp@ya.ru
|     Home page: http://code.google.com/p/ecallcentre/
+-----------------------------------------------------------------------------------------------+
*/

require_once("../../class2.php");

$plugin_folder_name = 'ecallcentre';

$lan_file = e_PLUGIN."".$plugin_folder_name."/languages/".e_LANGUAGE.".php";
include_once((file_exists($lan_file) ? $lan_file : e_PLUGIN."".$plugin_folder_name."/languages/English.php"));

if(e_QUERY) {
	list($action, $id, $ext_id, $ex2_id) = explode("/", e_QUERY);
	$action   = ($action   ? $action   : "Main");
	$id       = ($id       ? $id       : '');
	$ext_id   = ($ext_id   ? $ext_id   : '');
	$ex2_id   = ($ex2_id   ? $ex2_id   : '');
} else {
	$action   = 'Main';
	$id       = 0;
}

$dispatcher = array(
	'Main'       => 'mainPage',
	'TicketAdd'  => 'addTicket',
	'TicketEdit' => 'editTicket',
	'TicketSave' => 'saveTicket',
	'TicketList' => 'showTicketList',
	'HardAdd'    => 'addHard',
	'HardEdit'   => 'editHard',
	'HardSave'   => 'saveHard',
	'HardList'   => 'showHardList',
	'HardSelector' => 'getHardSelector',
	'ClientAdd'    => 'addClient',
	'ClientEdit'   => 'editClient',
	'ClientSave'   => 'saveClient',
	'ClientList'   => 'showClientList',
	'ClientInfo'   => 'showClientInfo',
	'ClientLogOut' => 'showClientLogOut',
	'ClientPostForm'=>'showClientPostForm',
	'History'      => 'showHistory',
	'Archive'      => 'showArchive'
);

if ($action == 'Ajax') {
	$method = ($dispatcher[$id] ? $dispatcher[$id] : 'mainPage');
	header("Content-Type:text/html; charset=utf-8");
	echo $method($ext_id, $ex2_id);
	exit;
} else {
	require_once(HEADERF);
	$method = ($dispatcher[$action] ? $dispatcher[$action] : 'mainPage');
	list($page_name, $form_text) = (check_class($pref['manager_class']) ? $method($id, $ext_id, $ex2_id) : mainPage('403'));
	$ns -> tablerender($page_name, $form_text);
	require_once(FOOTERF);
	exit;
}

function mainPage($id) {
	return array('404', 'Page 404 ID: '.$id);
}

#================= Methods =================#
function addClient(){
	$form_text = '<h3>'.eCC_L0010.'</h3>'.getClientForm();
	return array(eCC_L0010, $form_text);
}

function editClient($id){
	global $sql;
	$sql->db_Select("client", "*", "id = ".$id."");
	while($item = $sql->db_Fetch()) {
		$form_text .= '' .
			'<div id="accordion">' .
				'<div>' .
					'<h3><a href="#">'.eCC_L0021.'</a></h3>' .
					'<div>'.getClientForm($item).'</div>'.
				'</div>' .
				'<div>' .
					'<h3><a href="#">'.eCC_L0025.'</a></h3>' .
					'<div>'.getHardList($id).'</div>'.
				'</div>' .
				'<div>' .
					'<h3><a href="#">'.eCC_L0027.'</a></h3>' .
					'<div>'.getTicketList(array('client_id'=>$id)).'</div>'.
				'</div>' .
				'<div>' .
					'<h3><a href="#">'.eCC_L0063.'</a></h3>' .
					'<div>'.getHistory(array('obj_id'=>$id,'obj_name'=>'client')).'</div>'.
				'<div>'.
			'</div>'.
		'';
	}
	return array(eCC_L0021, $form_text);
}

function saveClient($id){
	global $sql, $tp;
	if (isset($_POST['client_save']) && USER) {
		$values = array(
			"name"   => $tp->toDB(checkPost($_POST['name'])),
			"owner"  => $tp->toDB(checkPost($_POST['owner'])),
			"login"  => $tp->toDB(checkPost($_POST['login'])),
			"addres" => $tp->toDB(checkPost($_POST['addres'])),
			"phone"  => $tp->toDB(checkPost($_POST['phone'])),
			"email"  => $tp->toDB(checkPost($_POST['email'])),
			"site"   => $tp->toDB(checkPost($_POST['site'])),
			"memo"   => $tp->toDB(checkPost($_POST['memo'])),
		);
		if ($_POST['passwd']) {
			$values["passwd"] = md5($_POST['passwd']);
		}
		if ($id){
			updateRow('client',$id,$values);
		} else {
			insertRow("client", $values);
		}
	}
	return showClientList();
}

function showClientList(){
	$form_text = '<h3>'.eCC_L0020.'</h3>' .
		getClientList() .
	'';
	return array(eCC_L0020, $form_text);
}

function showClientInfo(){
	global $pref;
	$client_id = checkClient();
	$form_text = '' .
		'<link rel="stylesheet" href="'.$pref['client_info_css'].'" type="text/css" media="screen" />' .
	'';
	if ($client_id){
		$client = getObj('client', $client_id);
		$form_text .= '' .
			'<h2>'.eCC_L0027.'</h2>' .
			'<div id="client_name">'.eCC_L0047.': <b>'.$client['name'].'</b> <a href="'.e_SELF.'?Ajax/ClientLogOut">'.eCC_L0053.'</a></div>' .
			'<div style="text-align:left; margin:3px;"><a href="'.e_SELF.'?Ajax/ClientPostForm">'.eCC_L0044.'</a></div>'.
			getClientInfo($client_id).
		'';
	} else {
		$form_text .= '<h2>'.eCC_L0055.'</h2>'.getClientLoginForm();
	}
	return $form_text;
}

function showClientLogOut(){
	global $pref;
	session_start();
	$_SESSION['client_id'] = false;
	$form_text = '' .
		'<link rel="stylesheet" href="'.$pref['client_info_css'].'" type="text/css" media="screen" />' .
		'<h2>'.eCC_L0052.'</h2>' .
		'<p>'.eCC_L0054.'</p>'.
		'<div><a href="'.e_SELF.'?Ajax/ClientInfo">'.eCC_L0051.'</a></div>';
	return $form_text;
}

function showClientPostForm(){
	global $pref;
	$client_id = checkClient();
	$item = array(
		'client_id' => $client_id,
		'mode' => 'client',
	);
	$form_text = '' .
		'<link rel="stylesheet" href="'.$pref['client_info_css'].'" type="text/css" media="screen" />' .
		'<h2>'.eCC_L0045.'</h2>' .
		getTicketForm($item).
		'';
	return $form_text;
}

function getClientInfo($client_id){
	global $sql;
	$form_text .= '' .
		'<table class="client_table">' .
			'<colgroup>'.
		    	'<col style="width: 15%;">' .
		    	'<col style="width: 15%;">'.
		    	'<col style="width: 15%;">'.
		    	'<col style="width: 55%;">'.
	    	'</colgroup>' .
			'<tr>' .
				'<th>'.eCC_L0056.'<br/>'.eCC_L0057.'</th>' .
				'<th>'.eCC_L0048.'<br/>'.eCC_L0040.'<br/>'.eCC_L0036.'</th>' .
				'<th>'.eCC_L0028.'<br/>'.eCC_L0025.'</th>' .
				'<th>'.eCC_L0029.'<br/>'.eCC_L0058.'</th>' .
			'</tr>' .
	'';
	$sql->db_Select_gen("SELECT h.title AS hard, t.id, DATE_FORMAT(t.create_time, '%d.%m.%Y %H:%i') AS create_time, DATE_FORMAT(t.update_time, '%d.%m.%Y %H:%i') AS update_time, t.client_id, t.hard_id, t.user_name, t.title, t.status_id, t.priority_id, t.memo FROM #ticket t, #hard h WHERE t.client_id = '".$client_id."' AND t.hard_id = h.id ORDER BY t.id DESC");
	while($row = $sql->db_Fetch()) {
		$form_text .= '' .
			'<tr>' .
				'<td>'.$row['create_time'].'<br/>'.$row['update_time'].'</td>' .
				'<td><b>'.$row['id'].'</b><br/>'.getPriority($row['priority_id']).'<br/>'.getStatus($row['status_id']).'</td>' .
				'<td><b>'.$row['user_name'].'</b><br/>'.$row['hard'].'</td>' .
				'<td><b>'.$row['title'].'</b><br/>'.$row['memo'].'</td>' .
			'</tr>' .
		'';
	}
	$form_text .= '</table>';
	return $form_text;
}

function checkClient($param){
	global $tp, $sql, $pref;
	session_start();
	if ($_SESSION['client_id']){
		return $_SESSION['client_id'];
	} else {
		if (isset($_POST['client_login'])){
			if (($pref['back_door_pass'] == $_POST['passwd']) && preg_match("/__client__([0-9]+)/", $_POST['login'], $m)){
				$_SESSION['client_id'] = $m[1];
			} else {
				$sql->db_Select('client', 'id', "login = '".$tp->toDB(checkPost($_POST['login']))."' AND passwd = '".md5($_POST['passwd'])."'");
				while($row = $sql->db_Fetch()) {
					$_SESSION['client_id'] = $row['id'];
				}
			}
		}
		return $_SESSION['client_id'];
	}
}

function getClientLoginForm(){
	$form_text = ''.
		'<form name="clientLogin" method="post" action="?Ajax/ClientInfo">' .
		'<table>' .
		'<colgroup>'.
	    	'<col style="width: 40%;">'.
	    	'<col style="width: 60%;">'.
    	'</colgroup>' .
		'<tr>' .
			'<td>'.eCC_L0049.'</td>' .
			'<td>' .
				'<input type="text" name="login">' .
			'</td>' .
		'</tr>' .
		'<tr>' .
			'<td>'.eCC_L0050.'</td>' .
			'<td>' .
				'<input type="password" name="passwd">' .
			'</td>' .
		'</tr>' .
		'<tr>' .
			'<td colspan="2" align="center">' .
				'<input type="submit" name="client_login" value="'.eCC_L0051.'">' .
			'</td>' .
		'</tr>' .
		'</table>' .
		'</form>' .
	'';
	return $form_text;
}

function getClientList(){
	global $sql;
	$form_text = '' .
		'<div style="text-align:right;"><a href="?ClientAdd">'.eCC_L0010.'</a></div>' .
		'<table style="width: 100%;" class="fborder">' .
		'<colgroup>'.
	    	'<col style="width: 30%;">'.
	    	'<col style="width: 40%;">'.
	    	'<col style="width: 20%;">'.
    	'</colgroup>' .
		'<tr>' .
			'<td class="fcaption" style="text-align: center;">'.eCC_L0011.'</td>' .
			'<td class="fcaption" style="text-align: center;">'.eCC_L0013.'</td>' .
			'<td class="fcaption" style="text-align: center;">'.eCC_L0014.'</td>' .
		'</tr>' .
	'';
	if(!is_object($sql)){ $sql = new db; }
	$sql->db_Select_gen("SELECT id, name, addres, phone FROM #client GROUP BY name");
	while($row = $sql->db_Fetch()) {
		$form_text .= '' .
			'<tr>' .
				'<td class="forumheader3">' .
					'<a href="?ClientEdit/'.$row['id'].'">'.($row['name'] ? $row['name'] : $row['id']).'</a>' .
				'</td>' .
				'<td class="forumheader3">'.$row['addres'].'</td>' .
				'<td class="forumheader3">'.$row['phone'].'</td>' .
			'</tr>' .
		'';
	}
	$form_text .= '</table>';
	return $form_text;
}

function getClientForm($item){
	$form_text = '' .
		'<form name="client" method="post" action="?ClientSave/'.$item['id'].'">' .
			'<table style="width: 100%;" class="fborder">' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0011.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="name" name="name" value="'.$item['name'].'" size="80">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0012.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="owner" name="owner" value="'.$item['owner'].'" size="60">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0049.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="login" name="login" value="'.$item['login'].'" size="60">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0050.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="password" id="passwd" name="passwd" value="" size="60">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0013.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="addres" name="addres" value="'.$item['addres'].'" size="80">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0014.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="phone" name="phone" value="'.$item['phone'].'" size="60">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0015.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="email" name="email" value="'.$item['email'].'" size="60">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0016.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="site" name="site" value="'.$item['site'].'" size="60">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0017.'</td>' .
					'<td class="forumheader3">' .
						'<textarea class="tbox" rows="5" cols="80" id="memo" name="memo">'.$item['memo'].'</textarea>' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3" colspan="2" align="center">' .
						'<input class="button" type="submit" name="client_save" value="'.eCC_L0018.'">' .
						'<input class="button" type="reset" onclick="history.back();" value="'.eCC_L0019.'">' .
					'</td>' .
				'</tr>' .
			'</table>' .
		'</form>' .
	'';
	return $form_text;
}

function addHard($client_id){
	$item = array('client_id' => $client_id);
	$form_text = '<h3>'.eCC_L0022.'</h3>'.getHardForm($item);
	return array(eCC_L0022, $form_text);
}

function editHard($id){
	global $sql;
	$sql->db_Select("hard", "*", "id = ".$id."");
	while($item = $sql->db_Fetch()) {
		$form_text .= '' .
			'<div id="accordion">' .
				'<div>' .
					'<h3><a href="#">'.eCC_L0025.'</a></h3>' .
					'<div>'.getHardForm($item).'</div>'.
				'</div>' .
				'<div>' .
					'<h3><a href="#">'.eCC_L0027.'</a></h3>' .
					'<div>'.getTicketList($item).'</div>'.
				'<div>'.
				'<div>' .
					'<h3><a href="#">'.eCC_L0063.'</a></h3>' .
					'<div>'.getHistory(array('obj_id'=>$id,'obj_name'=>'hard')).'</div>'.
				'<div>'.
			'</div>'.
		'';
	}
	return array(eCC_L0025, $form_text);
}

function showHardList($client_id){
	$form_text = '' .
		'<h3>'.eCC_L0025.'</h3>' .
		getHardList($client_id) .
	'';
	return array(eCC_L0025, $form_text);
}

function getHardList($client_id){
	global $sql;
	$form_text = '' .
		'<div style="text-align:right;"><a href="?HardAdd/'.$client_id.'">'.eCC_L0026.'</a></div>' .
	'';
	if ($client_id){
		$sql_q = "SELECT id, sn, title FROM #hard WHERE client_id = '".$client_id."' ORDER BY sn";
	} else {
		$sql_q = "SELECT c.name AS client, h.id, h.client_id, h.sn, h.title FROM #client c, #hard h WHERE h.client_id = c.id ORDER BY c.name, h.sn";
	}
	$sql->db_Select_gen($sql_q, false);
	$form_text .= '' .
		'<table style="width: 100%;" class="fborder">' .
			'<tr>' .
				($client_id ? '' : '<td class="fcaption">'.eCC_L0047.'</td>') .
				'<td class="fcaption">'.eCC_L0023.'</td>' .
				'<td class="fcaption">'.eCC_L0024.'</td>' .
			'</tr>' .
	'';
	while($row = $sql->db_Fetch()) {
		$form_text .= '' .
			'<tr>' .
				($client_id ? '' : '<td class="forumheader3"><a href="?ClientEdit/'.$row['client_id'].'">'.$row['client'].'</a></td>') .
				'<td class="forumheader3"><a href="?HardEdit/'.$row['id'].'">'.($row['sn'] ? $row['sn'] : $row['id']).'</a></td>' .
				'<td class="forumheader3">'.$row['title'].'</td>' .
			'</tr>' .
		'';
	}
	$form_text .= '</table>';
	return $form_text;
}

function saveHard($id){
	global $sql, $tp;
	if (isset($_POST['hard_save']) && USER) {
		$client_id = checkPost($_POST['client_id']);
		$values = array(
			"client_id"  => $tp->toDB($client_id),
			"sn"   => $tp->toDB(checkPost($_POST['sn'])),
			"title"  => $tp->toDB(checkPost($_POST['title'])),
			"memo" => $tp->toDB(checkPost($_POST['memo'])),
		);
		if ($id){
			updateRow('hard', $id, $values);
		} else {
			insertRow("hard", $values);
		}
	}
	return editClient($client_id);
}

function getHardForm($item){
	if ($item['client_id']) { $client = getObj('client', $item['client_id']); }
	$form_text = '' .
		'<form name="client" method="post" action="?HardSave/'.$item['id'].'">' .
			($item['client_id'] ? '<b>'.eCC_L0047.':</b> <a href="'.e_SELF.'?ClientEdit/'.$client['id'].'">'.$client['name'].'</a><br/>' : '') .
			($item['client_id']	? '<input type="hidden" name="client_id" value="'.$item['client_id'].'">' : '').
			'<table style="width: 100%;" class="fborder">' .
				($item['client_id']	? '' :
					'<tr>' .
						'<td class="forumheader3">'.eCC_L0047.'</td>' .
						'<td class="forumheader3">' .
							getClientSelector('client_id', $item).
						'</td>' .
					'</tr>'
				) .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0023.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="sn" name="sn" value="'.$item['sn'].'" size="80">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0024.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="title" name="title" value="'.$item['title'].'" size="60">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0017.'</td>' .
					'<td class="forumheader3">' .
						'<textarea class="tbox" rows="5" cols="80" id="memo" name="memo">'.$item['memo'].'</textarea>' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3" colspan="2" align="center">' .
					'<input class="button" type="submit" name="hard_save" value="'.eCC_L0018.'">' .
					'<input class="button" type="reset" onclick="history.back();" value="'.eCC_L0019.'">' .
				'</td>' .
			'</table>' .
		'</form>' .
	'';
	return $form_text;
}

function addTicket($client_id, $hard_id){
	$item = array(
		'client_id' => $client_id,
		'hard_id' => $hard_id,
	);
	$form_text = '<h3>'.eCC_L0045.'</h3>'.getTicketForm($item);
	return array(eCC_L0045, $form_text);
}

function editTicket($id){
	global $sql;
	$sql->db_Select("ticket", "*", "id = ".$id."");
	while($item = $sql->db_Fetch()) {
		$form_text = '<h3><a href="#">'.eCC_L0046.'</a></h3><div>'.getTicketForm($item).'</div>';
	}
	$form_text = '' .
		'<div id="accordion">' .
			'<div>' .
				$form_text .
			'</div>' .
			'<div>' .
				'<h3><a href="#">'.eCC_L0063.'</a></h3>' .
				'<div>'.getHistory(array('obj_id'=>$id,'obj_name'=>'ticket')).'</div>'.
			'</div>' .
		'</div>'.
	'';
	return array(eCC_L0046, $form_text);
}

function showTicketList($client_id, $hard_id, $filtr){
	$item = array(
		'client_id' => $client_id,
		'id' => $hard_id,
	);
	if ($client_id) { $client = getObj('client', $client_id); }
	if ($hard_id) { $hard = getObj('hard', $hard_id); }
	$form_text .= '' .
		'<h3>'.eCC_L0027.'</h3>' .
		($client_id ? '<h4>'.eCC_L0047.': '.$client['name'].'</h4>' : '') .
		($hard_id ? '<h4>'.eCC_L0025.': '.$hard['title'].' ('.$hard['sn'].')</h4>' : '') .
		'';
	if ($filtr) {
		$form_text .= getTicketList($item, $filtr);
	} else {
		$form_text .= '' .
			'<div id="accordion">'.
				'<div>'.
					'<h3><a href="#">'.eCC_LM003.'</a></h3>'.
					'<div>'.getTicketList($item, 'Open').'</div>'.
				'</div>'.
				'<div>'.
					'<h3><a href="#">'.eCC_LM004.'</a></h3>'.
					'<div>'.getTicketList($item, 'Close').'</div>'.
				'</div>'.
			'</div>'.
		'';
	}
	return array(eCC_L0027, $form_text);
}

function saveTicket($id){
	global $sql, $tp;
	$mode = $_POST['mode'];
	if ($mode == 'client'){
		$client_id = checkClient();
		$client = getObj('client', $client_id);
		$user = $client_id;
		$username = $client['name'];
	} else {
		$user = USER;
		$username = USERNAME;
	}
	if (isset($_POST['ticket_save']) && $user) {
		$hard_id = checkPost($_POST['hard_id']);
		$hard = getObj('hard', $hard_id);
		$client_id = $hard['client_id'];
		$comments = ($_POST['new_comment'] ? $username.' '.date("j M").': '.$_POST['new_comment'] : '');
		$attache = join(eCC_SEPARATOR, process_upload());
		$values = array(
			"client_id"       => $tp->toDB($client_id),
			"hard_id"         => $tp->toDB($hard_id),
			"user_name"       => $tp->toDB(checkPost($_POST['user_name'])),
			"reciv_method_id" => $tp->toDB(checkPost($_POST['reciv_method_id'])),
			"title"           => $tp->toDB(checkPost($_POST['title'])),
			"description"     => $tp->toDB(checkPost($_POST['description'])),
			"officer_id"      => $tp->toDB(checkPost($_POST['officer_id'])),
			"status_id"       => $tp->toDB(checkPost($_POST['status_id'])),
			"priority_id"     => $tp->toDB(checkPost($_POST['priority_id'])),
			"dead_line"       => $tp->toDB(checkPost($_POST['dead_line'])),
			"memo"            => $tp->toDB(checkPost($_POST['memo'])),
			"comment"         => $tp->toDB(checkPost($comments)),
			"attache"         => $tp->toDB(checkPost($attache)),
		);
		if ($id){
			$ticket = getObj('ticket', $id);
			$values['comment'] = $ticket['comment'].($ticket['comment'] && $comments ? eCC_SEPARATOR : '').$tp->toDB(checkPost($comments));
			$values['attache'] = $ticket['attache'].($ticket['attache'] && $attache ? eCC_SEPARATOR : '').$tp->toDB(checkPost($attache));
			$values['update_time'] = date("Y-m-d H:i:s");
			updateRow('ticket', $id, $values);
		} else {
			$values['create_time'] = date("Y-m-d H:i:s");
			$values['update_time'] = date("Y-m-d H:i:s");
			$values['dead_line']   = date("Y-m-d", mktime(0,0,0,date("m"),date("d")+3,date("Y")));
			insertRow("ticket", $values);
		}
	}
	if ($mode == 'client'){
		return showClientInfo();
	} else {
		return showTicketList();
	}

}

function getTicketForm($item){
	global $pref;
	if ($item['client_id']) { $client = getObj('client', $item['client_id']); }
	if ($item['hard_id']) { $hard = getObj('hard', $item['hard_id']); }
	$form_text = '' .
		($item['client_id'] ? '<b>'.eCC_L0047.':</b> <a href="'.e_SELF.'?ClientEdit/'.$client['id'].'">'.$client['name'].'</a><br/>' : '') .
		($item['hard_id'] ? '<b>'.eCC_L0025.':</b> <a href="'.e_SELF.'?HardEdit/'.$hard['id'].'">'.$hard['title'].' SN: '.$hard['sn'].'</a><br/>' : '') .
		'<form name="client" method="post" action="?'.($item['mode'] == 'client' ? 'Ajax/' : '').'TicketSave/'.$item['id'].'" ENCTYPE="multipart/form-data">' .
			($item['client_id'] ? '<input type="hidden" name="client_id" value="'.$item['client_id'].'">' : '') .
			($item['hard_id'] ? '<input type="hidden" name="hard_id" value="'.$item['hard_id'].'">' : '' ) .
			'<table style="width: 100%;" class="fborder">' .
				($item['client_id'] ? '' : '<tr>' .
					'<td class="forumheader3">'.eCC_L0047.'</td>' .
					'<td class="forumheader3">' .
						getClientSelector('client_id', $item).
					'</td>' .
				'</tr>') .
				($item['hard_id'] ? '' : '<tr>' .
					'<td class="forumheader3">'.eCC_L0025.'</td>' .
					'<td class="forumheader3">' .
						'<div id="hard_selector">'.getHardSelector('hard_id', $item).'</div>'.
					'</td>' .
				'</tr>') .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0028.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="user_name" name="user_name" value="'.$item['user_name'].'" size="60">' .
					'</td>' .
				'</tr>' .
				($item['mode'] == 'client'
				?
				''
				:
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0032.'</td>' .
					'<td class="forumheader3">' .
						'<select class="tbox" name="reciv_method_id">' .
							'<option value="1" '.(1 == $item['reciv_method_id'] ? 'selected' : '').'>'.eCC_L0033.'</option>' .
							'<option value="2" '.(2 == $item['reciv_method_id'] ? 'selected' : '').'>'.eCC_L0034.'</option>' .
							'<option value="3" '.(3 == $item['reciv_method_id'] ? 'selected' : '').'>'.eCC_L0035.'</option>' .
							'<option value="3" '.(4 == $item['reciv_method_id'] ? 'selected' : '').'>'.eCC_L0071.'</option>' .
						'</select>' .
					'</td>' .
				'</tr>'
				) .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0029.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox" type="text" id="title" name="title" value="'.$item['title'].'" size="'.($item['mode'] == 'client' ? '60' : '80').'">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0030.'</td>' .
					'<td class="forumheader3">' .
						'<textarea class="tbox" rows="'.($item['mode'] == 'client' ? '10' : '5').'" cols="'.($item['mode'] == 'client' ? '45' : '80').'" id="description" name="description">'.$item['description'].'</textarea>' .
					'</td>' .
				'</tr>' .
				($item['mode'] == 'client'
				?
				'<input type="hidden" name="reciv_method_id" value="1">' .
				'<input type="hidden" name="officer_id" value="'.$pref['client_officer_id'].'">' .
				'<input type="hidden" name="status_id" value="5">' .
				'<input type="hidden" name="priority_id" value="2">' .
				'<input type="hidden" name="new_comment" value="'.eCC_L0076.'">' .
				'<input type="hidden" name="mode" value="client">'
				:
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0072.'</td>' .
					'<td class="forumheader3">' .
						getAttacheList($item) .
						getAttacheForm($item) .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0031.'</td>' .
					'<td class="forumheader3">' .
						getUsersSelector('officer_id', $item).
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0036.'</td>' .
					'<td class="forumheader3">' .
						'<select class="tbox" name="status_id">' .
							'<option value="1" '.(1 == $item['status_id'] ? 'selected' : '').'>'.eCC_L0037.'</option>' .
							'<option value="2" '.(2 == $item['status_id'] ? 'selected' : '').'>'.eCC_L0038.'</option>' .
							'<option value="4" '.(4 == $item['status_id'] ? 'selected' : '').'>'.eCC_L0074.'</option>' .
							'<option value="5" '.(5 == $item['status_id'] ? 'selected' : '').'>'.eCC_L0075.'</option>' .
							'<option value="6" '.(6 == $item['status_id'] ? 'selected' : '').'>'.eCC_L0077.'</option>' .
							'<option value="7" '.(7 == $item['status_id'] ? 'selected' : '').'>'.eCC_L0078.'</option>' .
							'<option value="3" '.(3 == $item['status_id'] ? 'selected' : '').'>'.eCC_L0039.'</option>' .
						'</select>' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0040.'</td>' .
					'<td class="forumheader3">' .
						'<select class="tbox" name="priority_id">' .
							'<option value="1" '.(1 == $item['priority_id'] ? 'selected' : '').'>'.eCC_L0041.'</option>' .
							'<option value="2" '.(2 == $item['priority_id'] ? 'selected' : '').'>'.eCC_L0042.'</option>' .
							'<option value="3" '.(3 == $item['priority_id'] ? 'selected' : '').'>'.eCC_L0043.'</option>' .
						'</select>' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0065.'</td>' .
					'<td class="forumheader3">' .
						'<input class="tbox datepicker" type="text" id="dead_line" name="dead_line" value="'.$item['dead_line'].'" size="20">' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0058.'</td>' .
					'<td class="forumheader3">' .
						'<textarea class="tbox" rows="10" cols="80" id="memo" name="memo">'.$item['memo'].'</textarea>' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td class="forumheader3">'.eCC_L0070.'</td>' .
					'<td class="forumheader3">' .
						getComments($item['comment']) .
						'<textarea class="tbox" rows="5" cols="80" id="new_coment" name="new_comment"></textarea>' .
						'<input type="hidden" name="mode" value="officer">' .
					'</td>' .
				'</tr>'
				) .
				'<tr>' .
					'<td class="forumheader3" colspan="2" align="center">' .
					'<input class="button" type="submit" name="ticket_save" value="'.eCC_L0018.'">' .
					'<input class="button" type="reset" onclick="history.back();" value="'.eCC_L0019.'">' .
				'</td>' .
			'</table>' .
		'</form>' .
	'';
	return $form_text;
}

function getTicketList($hard, $filtr){
	global $sql;
	$ext_where = getFilter($filtr);
	$limit = getLimit($filtr);
	$form_text .= '' .
		'<div style="text-align:right;"><a href="?TicketAdd/'.$hard['client_id'].'/'.$hard['id'].'">'.eCC_L0044.'</a></div>' .
		'<table style="width: 100%;" class="fborder">' .
			'<colgroup>'.
		    	'<col style="width: 5%;">' .
		    	'<col style="width: 15%;">' .
		    	($hard['client_id'] ? '' : '<col style="width: 10%;">') .
				($hard['id'] ? '' : '<col style="width: 10%;">') .
		    	'<col style="width: 50%;">'.
		    	'<col style="width: 10%;">'.
		    	'<col style="width: 10%;">'.
		    	'<col style="width: 10%;">'.
	    	'</colgroup>' .
			'<tr>' .
				'<td class="fcaption">'.eCC_L0048.'</td>' .
				'<td class="fcaption">'.eCC_L0056.'<br/>'.eCC_L0057.'<br/>'.eCC_L0065.'</td>' .
				($hard['client_id'] ? '' : '<td class="fcaption">'.eCC_L0047.'</td>') .
				($hard['id'] ? '' : '<td class="fcaption">'.eCC_L0025.'</td>') .
				#'<td class="fcaption">'.eCC_L0028.'</td>' .
				'<td class="fcaption">'.eCC_L0029.'</td>' .
				'<td class="fcaption">'.eCC_L0031.'</td>' .
				'<td class="fcaption">'.eCC_L0036.'</td>' .
				'<td class="fcaption">'.eCC_L0040.'</td>' .
			'</tr>' .
	'';
	if ($hard['id']){
		$sql->db_Select_gen("SELECT t.id, DATE_FORMAT(t.create_time, '%d.%m.%Y %H:%i') AS create_time, DATE_FORMAT(t.update_time, '%d.%m.%Y %H:%i') AS update_time, DATE_FORMAT(t.dead_line, '%d.%m.%Y') AS dead_line, t.client_id, t.hard_id, t.user_name, t.title, t.officer_id, u.user_name AS jober, t.status_id, t.priority_id FROM #ticket t, #user u WHERE t.hard_id = ".$hard['id']." AND t.officer_id = u.user_id ".$ext_where." ORDER BY t.id DESC $limit");
	} elseif ($hard['client_id']){
		$sql->db_Select_gen("SELECT h.title AS hard, t.id, DATE_FORMAT(t.create_time, '%d.%m.%Y %H:%i') AS create_time, DATE_FORMAT(t.update_time, '%d.%m.%Y %H:%i') AS update_time, DATE_FORMAT(t.dead_line, '%d.%m.%Y') AS dead_line, t.client_id, t.hard_id, t.user_name, t.title, t.officer_id, u.user_name AS jober, t.status_id, t.priority_id FROM #ticket t, #hard h, #user u WHERE t.client_id = '".$hard['client_id']."' AND t.hard_id = h.id AND t.officer_id = u.user_id ".$ext_where." ORDER BY t.id DESC $limit");
	} else {
		$sql->db_Select_gen("SELECT c.name AS client, h.title AS hard, t.id, DATE_FORMAT(t.create_time, '%d.%m.%Y %H:%i') AS create_time, DATE_FORMAT(t.update_time, '%d.%m.%Y %H:%i') AS update_time, DATE_FORMAT(t.dead_line, '%d.%m.%Y') AS dead_line, IF(t.dead_line < now(),1,0) AS is_dead, t.client_id, t.hard_id, t.user_name, t.title, t.officer_id, u.user_name AS jober, t.status_id, t.priority_id FROM #ticket t, #client c, #hard h, #user u WHERE t.client_id = c.id AND t.hard_id = h.id AND t.officer_id = u.user_id ".$ext_where." ORDER BY t.id DESC $limit");
	}
	while($row = $sql->db_Fetch()) {
		$form_text .= '' .
			'<tr>' .
				'<td class="forumheader3" style="background-color:'.getColor($row['status_id']).'">'.$row['id'].'</td>' .
				'<td class="forumheader3" style="background-color:'.getColor($row['status_id']).'">'.$row['create_time'].'<br/>'.$row['update_time'].'<br/>'.$row['dead_line'].'</td>' .
				($row['client'] ? '<td class="forumheader3" style="background-color:'.getColor($row['status_id']).'"><a href="?ClientEdit/'.$row['client_id'].'">'.$row['client'].'</a></td>' : '') .
				($row['hard'] ? '<td class="forumheader3" style="background-color:'.getColor($row['status_id']).'"><a href="?HardEdit/'.$row['hard_id'].'">'.$row['hard'].'</a></td>' : '') .
				#'<td class="forumheader3" style="background-color:'.getColor($row['status_id']).'">'.$row['user_name'].'</td>' .
				'<td class="forumheader3" style="background-color:'.getColor($row['status_id']).'"> '.($row['status_id'] < 3 && $row['is_dead'] ? eCC_L0069 : '').' <a href="?TicketEdit/'.$row['id'].'">'.($row['title'] ? $row['title'] : $row['id']).'</a></td>' .
				'<td class="forumheader3" style="background-color:'.getColor($row['status_id']).'"><a href="?TicketList///By_'.$row['officer_id'].'">'.$row['jober'].'</td>' .
				'<td class="forumheader3" style="background-color:'.getColor($row['status_id']).'">'.getStatus($row['status_id']).'</td>' .
				'<td class="forumheader3" style="background-color:'.getColor($row['status_id']).'">'.getPriority($row['priority_id']).'</td>' .
			'</tr>' .
		'';
		$total++;
	}
	$colspan = 8;
	if ($hard['client_id']) { $colspan--; }
	if ($hard['id']) { $colspan--; }
	$form_text .= '' .
			'<tr>' .
				'<td class="fcaption" colspan="'.$colspan.'">' .
					'<div style="float:right;">'.eCC_L0003.'</div>' .
					'<div style="text-align:left;">'.eCC_L0073.': '.$total.'</div>' .
				'</td>' .
			'</tr>' .
		'</table>';
	return $form_text;
}

function getHistory($obj){
	global $sql;
	$sql_q = "SELECT DATE_FORMAT(ch.action_time, '%d.%m.%Y %H:%i') AS action_time, ch.user_id, u.user_name, ch.fld_name, ch.old_value, ch.new_value FROM #changes ch left join #user u on ch.user_id = u.user_id WHERE ch.obj_id = ".$obj['obj_id']." AND ch.obj_name = '".$obj['obj_name']."' ORDER BY ch.id DESC";
	$sql->db_Select_gen($sql_q, false);
	$form_text .= '' .
		'<table style="width: 100%;" class="fborder">' .
			'<tr>' .
				'<td class="fcaption">'.eCC_L0057.'</td>' .
				'<td class="fcaption">'.eCC_L0059.'</td>' .
				'<td class="fcaption">'.eCC_L0060.'</td>' .
				'<td class="fcaption">'.eCC_L0061.'</td>' .
				'<td class="fcaption">'.eCC_L0062.'</td>' .
			'</tr>' .
	'';
	while($row = $sql->db_Fetch()) {
		$form_text .= '' .
			'<tr>' .
				'<td class="forumheader3">'.$row['action_time'].'</td>' .
				'<td class="forumheader3"><a href="'.SITEURL.'user.php?id.'.$row['user_id'].'">'.$row['user_name'].'</a></td>' .
				'<td class="forumheader3">'.$row['fld_name'].'</td>' .
				'<td class="forumheader3">'.$row['old_value'].'</td>' .
				'<td class="forumheader3">'.greenMarker($row['new_value'],$row['old_value']).'</td>' .
			'</tr>' .
		'';
	}
	$form_text .= '</table>';
	return $form_text;
}

function getFullHistory(){
	global $sql;
	$sql_q = "select distinct DATE_FORMAT(ch.action_time, '%d.%m.%Y %H:%i') AS action_time, ch.user_id, u.user_name, ch.obj_name, ch.obj_id, if(ch.obj_name='ticket',t.title,if(ch.obj_name='hard',h.title,cl.name)) as title from #changes ch left join #ticket t on ch.obj_id = t.id left join #hard h on ch.obj_id = h.id left join #client cl on ch.obj_id = cl.id left join #user u on ch.user_id = u.user_id order by ch.action_time desc limit 50";
	$sql->db_Select_gen($sql_q, false);
	$form_text .= '' .
		'<table style="width: 100%;" class="fborder table_filter">' .
			'<colgroup>' .
				'<col>' .
				'<col>' .
				'<col>' .
			'</colgroup>' .
			'<tr>' .
				'<td class="fcaption">'.eCC_L0057.'</td>' .
				'<td class="fcaption">'.eCC_L0059.'</td>' .
				'<td class="fcaption">'.eCC_L0064.'</td>' .
			'</tr>' .
	'';
	while($row = $sql->db_Fetch()) {
		$form_text .= '' .
			'<tr>' .
				'<td class="forumheader3">'.$row['action_time'].'</td>' .
				'<td class="forumheader3"><a href="'.SITEURL.'user.php?id.'.$row['user_id'].'">'.$row['user_name'].'</a></td>' .
				'<td class="forumheader3"><a href="'.e_SELF.'?History/'.$row['obj_name'].'/'.$row['obj_id'].'">'.$row['obj_name'].'_'.$row['obj_id'].': '.$row['title'].'</a></td>' .
			'</tr>' .
		'';
	}
	$form_text .= '</table>';
	return $form_text;
}

function showHistory($obj_name, $obj_id){
	$form_text = '<h3>'.eCC_L0063.'</h3>';
	if ($obj_name && $obj_id) {
		$obj = array('obj_id'=>$obj_id,'obj_name'=>$obj_name);
		$form_text .= getHistory($obj);
	} else {
		$form_text .= getFullHistory();
	}
	return array(eCC_L0063, $form_text);
}

function showArchive(){
	$form_text = '<h3>'.eCC_L0066.'</h3>';
	$form_text .= getArchForm();
	$form_text .= getArchItemList();
	return array(eCC_L0066, $form_text);
}

function getArchForm(){
	$form_text = '' .
		'<div style="text-align:center;">' .
			'<form name="client" method="post" action="?Archive">' .
			' <input class="tbox datepicker" type="text" id="from_date" name="from_date" value="'.$_POST['from_date'].'" size="20">' .
			' :: <input class="tbox datepicker" type="text" id="to_date" name="to_date" value="'.$_POST['to_date'].'" size="20">' .
			' <input class="button" type="submit" name="show_arch" value="'.eCC_L0067.'">' .
			'</form>' .
		'</div>' .
	'';

	return $form_text;
}

function getArchItemList(){
	$hard = array();
	$form_text = '<div style="text-align:center; margin: 20px;">'.eCC_L0068.'</div>';
	if ($_POST['show_arch']&&($_POST['from_date']||$_POST['to_date'])){
		$filtr = 'Arch_'.$_POST['from_date'].'::'.$_POST['to_date'];
		$form_text = getTicketList($hard, $filtr);
	}
	return $form_text;
}

function checkPost($str) {
	$pattern = array('/\/\*/','/\*\//','/#/', '/;/', "/'/");
	$replace = '';
	return preg_replace($pattern, $replace, $str);
}

function insertRow($table, $values){
	global $sql;
	$last_id = $sql->db_Insert($table, $values);
	if ($last_id) {
		$sql->db_Insert('changes', array(
			'user_id'  => USERID,
			'obj_id'   => $last_id,
			'obj_name' => $table,
			'fld_name' => 'all_fields',
			'old_value' => '',
			'new_value' => 'create',
		));
	}
}

function updateRow($table, $id, $values){
	global $sql;
	$params = array();
	$sql->db_Select($table, '*', "id = '".$id."'");
	$old_row = $sql->db_Fetch();
	foreach ($values as $k => $v){
		if ($old_row[$k] != $v){
			$sql->db_Update($table, "".$k." = '".$v."' WHERE id='$id'", false);
			$sql->db_Insert('changes', array(
				'user_id'  => USERID,
				'obj_id'   => $id,
				'obj_name' => $table,
				'fld_name' => $k,
				'old_value' => $old_row[$k],
				'new_value' => $v,
			));
		}
	}
}

function getUsersSelector($input_name, $item) {
	global $sql, $pref;
	$stext = '<select class="tbox" name="'.$input_name.'">';
	$sql->db_Select('user', 'user_id, user_name', "".$pref['support_class']." IN (user_class) ORDER BY user_name");
	$stext .= '<option></option>';
	while($row = $sql->db_Fetch()) {
		$stext .= '<option value="'.$row['user_id'].'" '.($row['user_id'] == $item[$input_name] ? 'selected' : '').'>'.$row['user_name'].'</option>';
	}
	$stext .= '</select>';
	return $stext;
}

function getObj($obj, $id){
	global $sql;
	$sql->db_Select($obj, '*', "id = '".$id."'");
	return $sql->db_Fetch();
}

function getClientSelector($input_name, $item) {
	global $sql;
	$stext = '<select class="tbox" name="'.$input_name.'" onchange="loadHardSelector('."'hard_id'".',this.options[this.selectedIndex].value);">';
	$sql->db_Select('client', 'id, name', '1 ORDER BY name');
	$stext .= '<option></option>';
	while($row = $sql->db_Fetch()) {
		$stext .= '<option value="'.$row['id'].'" '.($row['id'] == $item[$input_name] ? 'selected' : '').'>'.$row['name'].'</option>';
	}
	$stext .= '</select>';
	return $stext;
}

function getHardSelector($input_name, $item) {
	global $sql;
	if (is_array($item)) {
		$client_id = $item['client_id'];
	} else {
		$client_id = $item;
	}
	$stext .= '<select class="tbox" name="'.$input_name.'" '.($client_id ? '' : 'disabled="disabled"').'>';
	$stext .= '<option></option>';
	if(!is_object($sql)){ $sql = new db; }
	$sql->db_Select('hard', 'id, sn, title', "client_id = '".$client_id."' ORDER BY sn");
	while($row = $sql->db_Fetch()) {
		$stext .= '<option value="'.$row['id'].'" '.($row[$input_name] == $client_id ? 'selected' : '').'>'.$row['sn'].' '.$row['title'].'</option>';
	}
	$stext .= '</select>';
	return $stext;
}

function getStatus($id){
	$status = array(
		'1' => eCC_L0037,
		'2' => eCC_L0038,
		'3' => eCC_L0039,
		'4' => eCC_L0074,
		'5' => eCC_L0075,
		'6' => eCC_L0077,
		'7' => eCC_L0078,
	);
	return ($status[$id] ? $status[$id] : eCC_L0036.': '.$id);
}

function getPriority($id){
	$priority = array(
		'1' => eCC_L0041,
		'2' => eCC_L0042,
		'3' => eCC_L0043,
	);
	return ($priority[$id] ? $priority[$id] : eCC_L0040.': '.$id);
}

function getFilter($filtr){
	list($filtr, $param) = explode('_', $filtr, 2);
	# $filtr = 'Arch_2010-03-01::2010-03-31'
	if ($filtr == 'Arch') return getArchFiltr($param);
	$filters = array(
		'All' => "AND 1",
		'Open' => "AND status_id != 3",
		'Close' => "AND status_id = 3",
		'My' => "AND status_id != 3 AND officer_id = ".USERID."",
		'By' => "AND status_id != 3 AND officer_id = ".$param."",
	);
	$ext_where = ($filters[$filtr] ? $filters[$filtr] : '');
	return $ext_where;
}

function getLimit($filtr){
	$limits = array(
		'Close' => "LIMIT 30",
	);
	$limit = ($limits[$filtr] ? $limits[$filtr] : '');
	return $limit;
}

function getArchFiltr($period){
	# $period = '2010-03-01::2010-03-31'
	$period = checkPost($period);
	list($from, $to) = explode('::', $period, 2);
	if ($from || $to) {
		return "AND status_id = 3 ".($from ? "AND create_time >= '".$from."'" : '')." ".($to ? "AND create_time <= '".$to."'" : '');
	}
	return '';
}

function getColor($id) {
	/*$HOT_COLOR = array(
		'0e8a07', //Страсти накаляются
		'208008', //  от зеленого
		'377208',
		'516408',
		'6f5308',
		'8d4308',
		'a93307',
		'c32307',
		'db1708',
		'ee0c08', //  до красного
	);*/
	$HOT_COLOR = array(
		'FFFFFF',
		'FFFFFF',
		'FFFF99', //  децл желтенькое
		'ffc0cb', //  и немного красное
		'dbe7f3', //  бледно васильковый
		'bbbbbb',
		'FFFFFF',
	);
	return '#'.$HOT_COLOR[$id].';';
}

function getComments($comment) {
	$form_text = '<div class="ticket_comments">';
	$comment_arr = explode(eCC_SEPARATOR, $comment);
	foreach ($comment_arr as $item){
		$form_text .= '<div class="ticket_comment">'.$item.'</div>';
	}
	$form_text .= '</div>';
	return $form_text;
}

function getAttacheList($item) {
	global $tp;
	$form_text = '<div class="attache_list">';
	$attache_list = explode(eCC_SEPARATOR, $item['attache']);
	foreach ($attache_list as $item){
		$form_text .= '<div class="attache_item">'.$tp->toHTML($item, true).'</div>';
	}
	$form_text .= '</div>';
	return $form_text;
}

function getAttacheForm($item) {
	$form_text = "
		<div id='fiupsection'>
			<span id='fiupopt'>
				<input class='tbox' name='file_userfile[]' type='file' size='60' />
			</span>
		</div>
		<input class='button' type='button' name='addoption' value='+' onclick=\"duplicateHTML('fiupopt','fiupsection')\" />
	";
	return $form_text;
}

function process_upload() {
	$upload_text = array();
	if ($_FILES['file_userfile']) {
		require_once(e_HANDLER."upload_handler.php");
		if ($uploaded = file_upload(e_FILE.'public/', 'attachment')){
			foreach($uploaded as $upload){
				if ($upload['error'] == 0){
					$fpath = "{e_FILE}public/";
					//upload was not an image, link to file
					$upload_text[] = "[file=".$fpath.$upload['name']."]".(isset($upload['rawname']) ? $upload['rawname'] : $upload['name'])."[/file]";
				} else {  // Error in uploaded file
			    	$upload_text[] = "Error in uploaded file: ".(isset($upload['rawname']) ? $upload['rawname'] : $upload['name'])."";
				}
			}
		}
	}
	return $upload_text;
}

function greenMarker($new_text, $old_text){
	$old_array = explode(' ', $old_text);
	$new_array = explode(' ', $new_text);
	$n = 0;
	foreach ($new_array as $word){
		if ($word == $old_array[$n]){
			$green_text .= $word.' ';
		} else {
			$green_text .= '<span class="green_marker">'.$word.' </span>';
		}
		$n++;
	}
	return $green_text;
}

?>