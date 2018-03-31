<?php
class PluginBondsGraph extends CommonDBTM {
   static $sql_bonds = "
      SELECT 
         `t1`.`outlet_id`,
         `t2`.`asset_id` AS `foreign_asset_id`,
         `t2`.`asset_type` AS `foreign_asset_type`,
         `t2`.`outlet_id` AS `foreign_outlet_id`
      FROM `%s` `t1`
      INNER JOIN `%s` `t2`
         ON `t1`.`connected_to`=`t2`.`id`
      WHERE `t1`.`outlet_type`='Power'
         AND `t1`.`asset_type`='%s'
         AND `t1`.`asset_id`=%d";

   const MAX_OUTLET_ID = 24;
   const MIN_OUTLET_ID = 2;
   const MAX_OUTLETS = 2;
   private $PluginRacksRack;
   private $rack;
   private $pdus;
   private $valid_outlets = array();
   private $pdu_bonds = array();
   private $device_bonds = array();
   private $max_outlets = 2;


   static function canCreate() {
      return true;
   }


   static function canView() {
      return true;
   }


   static function getTypeName($nb = 0) {
      return _n('Bonds', 'Bonds', $nb, 'bonds');
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      return self::getTypeName();
   }


   static function displayTabContentForItem (CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      echo "<input type=\"button\" onClick=\"var w=window.open('".$CFG_GLPI["root_doc"].
             "/plugins/bonds/front/graph.php?id=".$item->getID()."' ,'_blank');".
             "w.focus();\" value=\"Open in a new tab\" class=\"submit\">";
   }


   function displayGraph ($rack_id) {
      global $CFG_GLPI;

      $PluginRacksRack = new PluginRacksRack();
      $res = $PluginRacksRack->getFromDB($rack_id);

      if(empty($res))
         return;

      $this->PluginRacksRack = $PluginRacksRack;
      $this->getRack();
      $this->getAllPdus();

      // Look for all bonds made to a device or PDU outside the current rack:
      //  device_bonds should match pdu_bonds. Unmatched items go to $excludes,
      //  matched items go to $bonds
      $excludes = array_merge(
         array_diff_assoc($this->device_bonds, $this->pdu_bonds),
         array_diff_assoc($this->pdu_bonds, $this->device_bonds)
      );
      $excludes = array_merge($excludes, array_flip($excludes));
      $bonds = array_intersect_assoc($this->device_bonds, $this->pdu_bonds);

      include GLPI_ROOT . "/plugins/bonds/tpl/graph.tpl";
   }


   private function getRack () {
      global $DB;

      $PluginRacksRack_Item = new PluginRacksRack_Item();
      $PluginBondsBond = new PluginBondsBond();

      $query = "
         SELECT
            `t1`.*,
            `t2`.`size`
         FROM 
            `".$PluginRacksRack_Item->getTable()."` `t1`,
            `glpi_plugin_racks_itemspecifications` `t2`
         WHERE `t1`.`plugin_racks_itemspecifications_id` = `t2`.`id` 
            AND `t1`.`plugin_racks_racks_id` = ".$this->PluginRacksRack->getField('id')."
            AND (`t1`.`faces_id` = '".PluginRacksRack::FRONT_FACE."' 
            OR (`t1`.`faces_id` ='".PluginRacksRack::BACK_FACE."' 
               AND `t2`.`length` = 1 ))
         ORDER BY `t1`.`position` ASC" ;

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      while ($data = $DB->fetch_assoc($result)) {
         $pos = $data["position"];
         $class = substr($data["itemtype"], 0, -5);
         $item = new $class();
         $item->getFromDB($data["items_id"]);

         $this->rack[$pos] = array(
            "items_id" => $data["items_id"],
            "class"    => $class,
            "size"     => $data["size"],
            "name"     => $item->getField("name"),
            "serial"   => $item->getField("serial"),
         );

         $res = $DB->query(
            sprintf(
               self::$sql_bonds,
               $PluginBondsBond->getTable(),
               $PluginBondsBond->getTable(),
               $class,
               $data["items_id"]
            )
         );

         if ($DB->numrows($res) > $this->max_outlets)
            $this->max_outlets = $DB->numrows($res);

         while ($assoc = $DB->fetch_assoc($res)) {
            $this->device_bonds[$class.'_'.$data["items_id"].'_'.$assoc['outlet_id']] =
               $assoc['foreign_asset_type'].'_'.$assoc['foreign_asset_id'].'_'.$assoc['foreign_outlet_id'];

            $this->rack[$pos]['outlets'][] = $assoc['outlet_id'];
         }
      }
   }


   private function getAllPdus() {
      global $DB;
      $name = $this->PluginRacksRack->getField('name');
      $name = str_replace(":","-",$name);

      $query = "
         SELECT `id`,`name`
         FROM `glpi_networkequipments`
         WHERE `name` LIKE 'pdu-" . $name . "%'
         ORDER BY `name`";

      $result = $DB->query($query);
      while ($data = $DB->fetch_assoc($result)) {
         $this->getPdu($data);
      }
   }


   /* showing 24 outlets by default, except the 1st outlet
      if outlet_id > 24 found, show 36 outlets */
   private function getPdu($param) {
      global $DB;

      $PluginBondsBond = new PluginBondsBond();
      $max_outlet_id = self::MAX_OUTLET_ID;
      $min_outlet_id = self::MIN_OUTLET_ID;

      $result = $DB->query(
         sprintf(
            self::$sql_bonds,
            $PluginBondsBond->getTable(),
            $PluginBondsBond->getTable(),
            'NetworkEquipment',
            $param["id"]
         )
      );

      while ($data = $DB->fetch_assoc($result)) {

         if($data['outlet_id'] < $min_outlet_id) {
            $min_outlet_id = $data['outlet_id'];
         } elseif ($data['outlet_id'] >= $max_outlet_id) {
            $max_outlet_id = $data['outlet_id'];
         }

         $this->pdu_bonds[$data['foreign_asset_type'].'_'.$data['foreign_asset_id'].'_'.$data['foreign_outlet_id']] =
            'NetworkEquipment_'.$param['id'].'_'.$data['outlet_id'];
      }

      if ($max_outlet_id > 24)
         $max_outlet_id = 42;

      $this->pdus[$param["id"]] = array(
         'id'            => $param['id'],
         'name'          => $param['name'],
         'class'         => 'NetworkEquipment',
         'max_outlet_id' => $max_outlet_id,
         'min_outlet_id' => $min_outlet_id
      );
   }
}
?>
