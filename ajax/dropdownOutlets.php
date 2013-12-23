<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Plugin::load('bonds',true);

$PluginBondsBond = new PluginBondsBond;

$prefix = (isset($_POST['prefix_'])) ? $_POST['prefix_'] : '';
$bonds = $PluginBondsBond->getBondsFromIdAndType($_POST[$prefix."asset_id"], $_POST[$prefix."asset_type"]);

$used_outlets = array();
foreach ($bonds as $key => $val) {
   if ($val['outlet_type'] != $_POST['outlet_type'])
      continue;

   $used_outlets[$val['outlet_id']] = $val['outlet_id'];
}

Dropdown::showFromArray(
   (empty($_POST['myname'])) ? 'outlet_id' : $_POST['myname'],
   array_combine( range(1,50), range(1,50) ),
   array(
      'rand' => $_POST["rand"],
      'used' => $used_outlets,
   )
); 


?>
