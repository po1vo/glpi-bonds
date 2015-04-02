<?php

include ("../../../inc/includes.php");

if (!empty($_GET["id"])) {
   $PluginBondsGraph = new PluginBondsGraph();

   $PluginBondsGraph->displayGraph($_GET["id"]);
} else {
   echo ":->";
}

?>
