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
   $link = "getDropdownValue.php";

   if ($_POST["idtable"] == 'User') {
      $link = "getDropdownUsers.php";
   }

   if (isset($_POST['rand'])) {
      $rand = $_POST['rand'];
   } else {
      $rand = mt_rand();
   }

   $field_id = Html::cleanId('dropdown_'.$_POST["name"].$rand);

   $p        = array('value'               => 0,
                     'valuename'           => Dropdown::EMPTY_VALUE,
                     'itemtype'            => $_POST["idtable"],
                     'display_emptychoice' => true,
                     'width'               => '20em',
                     'displaywith'         => array('serial'));

   if (isset($_POST['value'])) {
      $paramsallitems['value'] = $_POST['value'];
   }
   if (isset($_POST['entity_restrict'])) {
      $paramsallitems['entity_restrict'] = $_POST['entity_restrict'];
   }
   if (isset($_POST['condition'])) {
      $paramsallitems['condition'] = stripslashes($_POST['condition']);
   }

   echo Html::jsAjaxDropdown($_POST["name"], $field_id, $CFG_GLPI['root_doc']."/ajax/".$link, $p);

   echo "&nbsp;outlet&nbsp;#&nbsp;";
   echo "<span id=\"show_foreign_outlet_id$rand\">";

   Dropdown::showFromArray(
      "foreign_outlet_id",
      array( '0' => Dropdown::EMPTY_VALUE ),
      array( 'rand' => $rand, 'width' => 'auto' )
   );

echo <<< EOT
   </span>
   <script type='text/javascript'>
   $("#dropdown_foreign_asset_id$rand").change(function() {
      $("#show_foreign_outlet_id$rand").load(
         "{$CFG_GLPI["root_doc"]}/plugins/bonds/ajax/dropdownOutlets.php",
         {
            asset_id:    $("#dropdown_foreign_asset_id$rand").val(),
            asset_type:  "{$_POST['idtable']}",
            rand:        $rand,
            name:        "foreign_outlet_id",
            outlet_type: $("#dropdown_outlet_type$rand").val(),
         }
      )
   });
   </script>
EOT;

}
?>
