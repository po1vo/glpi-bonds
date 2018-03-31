<?php

// Init the hooks of the plugins -Needed
function plugin_init_bonds() {
   global $PLUGIN_HOOKS,$CFG_GLPI;

   Plugin::registerClass('PluginBondsBond');

   if (class_exists('PluginRacksRack'))
      Plugin::registerClass('PluginBondsGraph');

   $PLUGIN_HOOKS['post_init']['bonds'] = 'plugin_bonds_postinit';

   // CSRF compliance : All actions must be done via POST and forms closed by Html::closeForm();
   $PLUGIN_HOOKS['csrf_compliant']['bonds'] = true;

}

// Get the name and the version of the plugin - Needed
function plugin_version_bonds() {

   return array('name'           => 'Bonds',
                'version'        => '0.0.4',
                'author'         => 'Vadim Pisarev',
                'homepage'       => 'http://github.com/po1vo/glpi-bonds/',
                'license'        => 'GPLv2+',
                'minGlpiVersion' => '0.84');// For compatibility / no install in version < 0.80
}


function plugin_bonds_check_prerequisites() {
   return true;
}

function plugin_bonds_check_config() {
   return true;
}

?>
