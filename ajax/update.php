<?php

include ('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkCentralAccess();

PluginBondsBond::updatePowerBonds($_POST);

?>
