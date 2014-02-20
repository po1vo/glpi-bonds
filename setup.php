<?php

// Init the hooks of the plugins -Needed
function plugin_init_bonds() {
   global $PLUGIN_HOOKS,$CFG_GLPI;
   static $types = array( 'Computer','NetworkEquipment','Peripheral' );

   Plugin::registerClass('PluginBondsBond', 
       array( "addtabon" => $types )
   );

   foreach ($types as $type) {
      $PLUGIN_HOOKS['item_purge']['bonds'][$type] = 'plugin_item_purge_bonds';
   }

   // CSRF compliance : All actions must be done via POST and forms closed by Html::closeForm();
   $PLUGIN_HOOKS['csrf_compliant']['bonds'] = true;
}

// Get the name and the version of the plugin - Needed
function plugin_version_bonds() {

   return array('name'           => 'Bonds',
                'version'        => '0.0.3',
                'author'         => 'Vadim Pisarev',
                'homepage'       => 'http://github.com/po1vo/glpi-bonds/',
                'license'        => 'GPLv2+',
                'minGlpiVersion' => '0.84');// For compatibility / no install in version < 0.80
}


function plugin_bonds_check_prerequisites() {
   return true;
}

function plugin_bonds_check_config($verbose=false) {
   return true;
}

?>
