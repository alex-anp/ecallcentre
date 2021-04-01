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
if (!getperms("P")) {
      header("location:".e_HTTP."index.php");
      exit;
    }

require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");

$plugin_folder_name = 'ecallcentre';

$lan_file = e_PLUGIN."".$plugin_folder_name."/languages/".e_LANGUAGE.".php";
include_once((file_exists($lan_file) ? $lan_file : e_PLUGIN."".$plugin_folder_name."/languages/English.php"));

//=========== Update settings script =================
if(IsSet($_POST['updatesettings'])) {
    $pref['manager_class'] = $_POST['manager_class'];
	$pref['support_class'] = $_POST['support_class'];
    $pref['client_class'] = $_POST['client_class'];
    $pref['client_info_css'] = $_POST['client_info_css'];
    $pref['client_officer_id'] = $_POST['client_officer_id'];
    $pref['back_door_pass'] = $_POST['back_door_pass'];
    save_prefs();
    $message .= eCC_A004;
}

//================ Options Form ===========================
$text = "
<form name='setings' action='".e_SELF."' method='post'>
	<table style='width:90%' class='fborder'>
		<tr>
		  <td class='forumheader4'>".eCC_A001."</td>
		  <td class='forumheader4'>
			".r_userclass("manager_class", $pref['manager_class'],"off","member,admin,classes")."
		  </td>
		</tr>
		<tr>
		  <td class='forumheader4'>".eCC_A002."</td>
		  <td class='forumheader4'>
			".r_userclass("support_class", $pref['support_class'],"off","member,admin,classes")."
		  </td>
		</tr>
		<tr>
		  <td class='forumheader4'>".eCC_A003."</td>
		  <td class='forumheader4'>
			".r_userclass("client_class", $pref['client_class'],"off","member,admin,classes")."
		  </td>
		</tr>
		<tr>
		  <td class='forumheader4'>".eCC_A007."</td>
		  <td class='forumheader4'>
			<input type='text' class='tbox' name='client_info_css' value='".$pref['client_info_css']."'>
		  </td>
		</tr>
		<tr>
		  <td class='forumheader4'>".eCC_A009."</td>
		  <td class='forumheader4'>
			".getUsersSelector('client_officer_id', $pref)."
		  </td>
		</tr>
		<tr>
		  <td class='forumheader4'>".eCC_A008."</td>
		  <td class='forumheader4'>
			<input type='text' class='tbox' name='back_door_pass' value='".$pref['back_door_pass']."'>
		  </td>
		</tr>
		<tr>
		  <td class='forumheader4' colspan='2'>
		    <div align='center'>
		      <input type='submit' class='button' name='updatesettings' value='".eCC_A006."'>
		    </div>
		  </td>
		</tr>
	</table>
</form>
";

if ($message != "") $ns->tablerender("", $message);
$captions = eCC_A005;
$ns -> tablerender($captions, $text);
require_once(e_ADMIN."footer.php");

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

?>