<?php

function plugin_bonds_install() {
   global $DB;

   if (
      (!TableExists("glpi_plugin_bonds_bonds"))
   ) {
      $DB->runFile(dirname(__FILE__) . "/db/mysql.0.0.x.sql") or
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

   $data = $PluginBondsBond->getBondsFromIdAndType(
         $item->getID(),
         $item->getType()
   );

   if (empty($data))
      return false;

   foreach($data as $h) {
      $PluginBondsBond->deleteBond($h);
   }

   return true; 
}

function plugin_bonds_postinit() {
   global $PLUGIN_HOOKS;

   foreach (PluginBondsBond::getTypes(true) as $type) {
      CommonGLPI::registerStandardTab($type, 'PluginBondsBond');
      $PLUGIN_HOOKS['item_purge']['bonds'][$type] = 'plugin_item_purge_bonds';
   }

   if (class_exists('PluginRacksRack'))
      CommonGLPI::registerStandardTab('PluginRacksRack' ,'PluginBondsGraph');
}

?>
