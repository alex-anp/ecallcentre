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

$PLUGIN_FLD = 'ecallcentre';

$lan_file = e_PLUGIN."".$PLUGIN_FLD."/languages/".e_LANGUAGE.".php";
include_once((file_exists($lan_file) ? $lan_file : e_PLUGIN."".$PLUGIN_FLD."/languages/English.php"));

$eplug_name = "eCallCentre";
$eplug_version = "1.0";
$eplug_author = "Alex ANP";
$eplug_logo = "button.png";
$eplug_url = "http://code.google.com/p/ecallcentre/";
$eplug_email = "alex-anp@ya.ru";
$eplug_description = eCC_L0002."";
$eplug_compatible = "e107 v7.8+";
$eplug_readme = "readme.txt";
$eplug_folder = $PLUGIN_FLD;
$eplug_menu_name = $PLUGIN_FLD."_menu";
$eplug_conffile = "admin_config.php";
$eplug_icon = $eplug_folder."/images/icon.png";
$eplug_icon_small = $eplug_folder."/images/icon_16.png";
$eplug_caption =  eCC_L0001;

$eplug_prefs = array(
    #"cbase_title" => "ClientBase",
    );


$eplug_table_names = array(
        "client",
        "hard",
        "ticket",
        "changes"
        );

$eplug_tables = array(
"
	CREATE TABLE ".MPREFIX."client (                                                                 
	    id INT(9) NOT NULL AUTO_INCREMENT,                                                  
		name VARCHAR(255) DEFAULT NULL,                                                     
		owner VARCHAR(255) DEFAULT NULL,                                                    
		login VARCHAR(255) DEFAULT NULL,                                                    
		passwd CHAR(50) DEFAULT NULL,                                                    
		addres VARCHAR(255) DEFAULT NULL,                                                   
		phone VARCHAR(255) DEFAULT NULL,                                                    
		email VARCHAR(255) DEFAULT NULL,                                                    
		site VARCHAR(255) DEFAULT NULL,                                                     
		memo TEXT,                                                                          
		PRIMARY KEY (id)                                                                    
	) ENGINE=MYISAM
",
"
	CREATE TABLE ".MPREFIX."hard (                                                                   
          id INT(9) NOT NULL AUTO_INCREMENT,                                                  
          client_id INT(9) DEFAULT NULL,
          sn VARCHAR(255) DEFAULT NULL,                                                       
          title VARCHAR(255) DEFAULT NULL,                                                    
          memo TEXT,                                                                          
          PRIMARY KEY (id)                                                                    
        ) ENGINE=MYISAM
",
"
	CREATE TABLE ".MPREFIX."ticket (                                                                             
          id INT(9) NOT NULL AUTO_INCREMENT,                                                              
          create_time DATETIME DEFAULT NULL,                                                              
          update_time DATETIME DEFAULT NULL,
          dead_line DATE DEFAULT NULL,
          client_id INT(9) NOT NULL,
          hard_id INT(9) NOT NULL,
          user_name VARCHAR(255) DEFAULT NULL,                                                            
          reciv_method_id SMALLINT(3) DEFAULT '1',                                                        
          title VARCHAR(255) NOT NULL,                                                                    
          description TEXT NOT NULL,                                                                      
          officer_id INT(9) NOT NULL,                                                                     
          status_id SMALLINT(3) DEFAULT '1',                                                              
          priority_id SMALLINT(3) DEFAULT '2',                                                            
          memo TEXT,
          comment TEXT,
	  attache TEXT,
          PRIMARY KEY (id)                                                                                
        ) ENGINE=MYISAM
",
"
	CREATE TABLE ".MPREFIX."changes (                                                                             
           id INT(9) NOT NULL AUTO_INCREMENT,                                                     
           action_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  
           user_id INT(9) DEFAULT NULL,                                                           
           obj_id INT(9) DEFAULT NULL,
           obj_name VARCHAR(255) DEFAULT NULL,                                                    
           fld_name VARCHAR(255) DEFAULT NULL,                                                    
           old_value TEXT,                                                                        
           new_value TEXT,                                                                        
           PRIMARY KEY (id)
        ) ENGINE=MYISAM
"
);

$eplug_link = TRUE;
$eplug_link_name = eCC_L0001;
$eplug_link_url = e_PLUGIN."$PLUGIN_FLD/$PLUGIN_FLD.php";

$eplug_done = "Installation Successful...";

$upgrade_add_prefs = "";

$upgrade_remove_prefs = "";

$upgrade_alter_tables = array();

$eplug_upgrade_done = "Upgrade Successful...";

?>
