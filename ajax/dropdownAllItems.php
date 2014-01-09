<?php
/*
 * @version $Id: dropdownAllItems.php 20129 2013-02-04 16:53:59Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
*/

include ('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkCentralAccess();

// Make a select box
if ($_POST["idtable"] && class_exists($_POST["idtable"])) {
   $table = getTableForItemType($_POST["idtable"]);

   // Link to user for search only > normal users
   $link = "dropdownValue.php";

   if ($_POST["idtable"] == 'User') {
      $link = "dropdownUsers.php";
   }

   if (isset($_POST['rand'])) {
      $rand = $_POST['rand'];
   } else {
      $rand = mt_rand();
   }

   $use_ajax = false;

   if ($CFG_GLPI["use_ajax"]
       && (countElementsInTable($table) > $CFG_GLPI["ajax_limit_count"])) {
      $use_ajax = true;
   }

   $paramsallitems = array(
      'searchText'          => '__VALUE__',
      'table'               => $table,
      'itemtype'            => $_POST["idtable"],
      'rand'                => $rand,
      'myname'              => $_POST["myname"],
      'displaywith'         => array('serial'),
      'display_emptychoice' => true,
   );

   if (isset($_POST['value'])) {
      $paramsallitems['value'] = $_POST['value'];
   }
   if (isset($_POST['entity_restrict'])) {
      $paramsallitems['entity_restrict'] = $_POST['entity_restrict'];
   }
   if (isset($_POST['condition'])) {
      $paramsallitems['condition'] = stripslashes($_POST['condition']);
   }

   $default = "<select id='dropdown_".$_POST["myname"].$rand."'><option value='0'>".Dropdown::EMPTY_VALUE.
              "</option></select>";
   Ajax::dropdown($use_ajax, "/ajax/$link", $paramsallitems, $default, $rand);

   echo "&nbsp;outlet&nbsp;#&nbsp;";
   echo "<span id='show_foreign_outlet_id$rand'>";
   echo "<select id='dropdown_foreign_outlet_id$rand' name='foreign_outlet_id'><option value='0'>".Dropdown::EMPTY_VALUE."</option></select>";
   echo "</span>";

   echo "<script type='text/javascript'>";
   echo "var el = Ext.fly('show_foreign_assets".$rand."');";
   echo "el.on('change', function() {";
   echo "   var updr = Ext.fly('dropdown_foreign_outlet_id".$rand."').getUpdater();";
   echo "   updr.update({";
   echo "      url: '" . $CFG_GLPI["root_doc"] . "/plugins/bonds/ajax/dropdownOutlets.php',";
   echo "      params: {";
   echo "         asset_id:    Ext.get('dropdown_foreign_asset_id$rand').getValue(),";
   echo "         asset_type: '" . $_POST['idtable'] . "',";
   echo "         rand:       '$rand',";
   echo "         myname:     'foreign_outlet_id',";
   echo "         outlet_type: Ext.get('dropdown_outlet_type$rand').getValue(),";
   echo "      }";
   echo "   });";
   echo "});";
   echo "</script>";

}
?>
