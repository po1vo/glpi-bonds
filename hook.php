<?php

function plugin_bonds_install() {
   global $DB;

   if (
      (!TableExists("glpi_plugin_bonds_bonds"))
   ) {
      $DB->runFile(dirname(__FILE__) . "/db/mysql.0.0.1.sql") or
      Html::displayErrorAndDie(
         "Error installing Bonds plugin ". $DB->error()
      );
   }

   return true;

}

function plugin_bonds_uninstall() {
   global $DB;

    foreach (array("bonds") as $table) {
        if (TableExists("glpi_plugin_bonds_" . $table)) {
            $DB->query("DROP TABLE glpi_plugin_bonds_" . $table) or
                print "Cannot remove database table glpi_plugin_bonds_" .
                    $table;
        }
    }

   return true;
}

function plugin_item_purge_bonds($item) {
   $PluginBondsBond = new PluginBondsBond;

   if (!$PluginBondsBond->getBondsFromIdAndType(
         $item->getID(),
         $item->getType() )
   )
      return false;

   $connected_to = $this->getField('connected_to');
   $PluginBondsBond->deleteFromDB();
   $PluginBondsBond->getFromDB($connected_to);
   $PluginBondsBond->deleteFromDB();

   return true; 
}

?>
