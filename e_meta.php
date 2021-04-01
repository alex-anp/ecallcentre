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

if (!defined('e107_INIT')) { exit; }

$PLUGIN_FLD = 'ecallcentre';

echo '<link rel="stylesheet" href="'.e_PLUGIN.''.$PLUGIN_FLD.'/css/ecallcentre.css" type="text/css" />';

echo '<link rel="stylesheet" href="'.e_PLUGIN.''.$PLUGIN_FLD.'/js/ui/theme/ui.all.css" type="text/css" />';
echo '<script src="'.e_PLUGIN.''.$PLUGIN_FLD.'/js/jquery.js" type="text/javascript"></script>';
echo '<script src="'.e_PLUGIN.''.$PLUGIN_FLD.'/js/ui/jquery-ui.js" type="text/javascript"></script>';

?>
<script type="text/javascript">
	function loadHardSelector(input_name, client_id){
		$("#hard_selector").load("?Ajax/HardSelector/"+input_name+"/"+client_id);
	}
	$(function(){
		// Accordion
		$("#accordion").accordion({ header: "h3", autoHeight: false });
		
		// tableFilter
		//$('.table_filter').tableFilter({imagePath:"<?php echo ''.e_PLUGIN.''.$PLUGIN_FLD.'/js/tableFilter/images/icons'; ?>"});
		
		// Datepicker
		$('.datepicker').datepicker({
			firstDay: 1,
			dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
			monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
			dateFormat: 'yy-mm-dd'
		});
	});	
</script>