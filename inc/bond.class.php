<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginBondsBond extends CommonDBTM {

   static function getTypeName($nb = 0) {
      return _n('Bond', 'Bonds', $nb, 'bonds');
   }


   static function canCreate() {
      return true;
   }


   static function canView() {
      return true;
   }


   static function countForItem(CommonDBTM $item) {
      return countElementsInTable(
         self::getTable(),
         "`asset_type`='".$item->getType()."' AND `asset_id` = '".$item->getID()."'"
      );
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($_SESSION['glpishow_count_on_tabs'])
         return self::createTabEntry(self::getTypeName(2), self::countForItem($item));

      return self::getTypeName(2);
   }


   static function displayTabContentForItem (CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      global $CFG_GLPI, $DB;

      $self=new self();
      $rand = mt_rand();

      echo "<form id='add_bond_form' name='add_bond_form' method='POST' action='" . $self->getFormURL() ."'>";
      echo "<input type='hidden' name='asset_id' value='".$item->getID()."'>";
      echo "<input type='hidden' name='asset_type' value='".$item->getType()."'>";
      echo '<div>';
      echo  '<table class="tab_cadre_fixe">';
      echo   '<tr class="tab_bg_2">';
      echo    '<td>';
      _e('Connect');
      echo "&nbsp;";

      $outlet_types  = array( '0' => Dropdown::EMPTY_VALUE );
      $outlet_types += array_combine(self::listOutletTypes(), self::listOutletTypes());

      Dropdown::showFromArray(
         "outlet_type",
         $outlet_types,
         array( 'rand' => $rand )
      );

      $params = array(
         'outlet_type' => '__VALUE__',
         'asset_id'    => $item->getID(),
         'asset_type'  => $item->getType(),
         'rand'        => $rand,
         'myname'      => 'outlet_id'
      );

      Ajax::updateItemOnSelectEvent(
         "dropdown_outlet_type$rand", "show_outlet_id$rand",
         $CFG_GLPI["root_doc"]."/plugins/bonds/ajax/dropdownOutlets.php",
         $params
      );

      echo "&nbsp;";
      _e('outlet #');
      echo "&nbsp;";
      echo "<span id='show_outlet_id$rand'>";

      Dropdown::showFromArray(
         "outlet_id", 
         array( '0' => Dropdown::EMPTY_VALUE ),
         array( 'rand' => $rand )
      );

      echo "</span>";
      echo "&nbsp;";
      _e('to');
      echo "&nbsp;";

      $connected_types = array( '0' => Dropdown::EMPTY_VALUE );
      foreach(self::getAssetClasses() as $class_name) {
         $connected_types[$class_name] = $class_name;
      }

      Dropdown::showFromArray(
         "foreign_asset_type", 
         $connected_types,
         array( 'rand' => $rand )
      );

      $params = array(
         'idtable' => '__VALUE__',
         'rand'    => $rand,
         'myname'  => 'foreign_asset_id'
      );
      Ajax::updateItemOnSelectEvent(
         "dropdown_foreign_asset_type$rand", "show_foreign_asset_id$rand",
         $CFG_GLPI["root_doc"]."/plugins/bonds/ajax/dropdownAllItems.php",
         $params
      );

      echo "<span id='show_foreign_asset_id$rand'>&nbsp;</span>\n";
      echo    "</td>";
      echo    "<td><input type='submit' name='add' value=\""._sx('button','Add')."\" class='submit'></td>\n";
      echo   '</tr>';
      echo  '</table>';
      echo '</div>';
      Html::closeForm();


      echo "<form id='bonds$rand' name='bonds$rand' method='POST' action='" . $self->getFormURL() ."'>";
      echo '<div>';
      echo  '<table class="tab_cadre_fixe">';
      echo   '<tr class="tab_bg_2">';
      echo    '<th width="10"></th>';
      echo    '<th width="1%">' . __('Outlet', 'bonds') . '</th>';
      echo    '<th>' . __('Type', 'bonds') . '</th>';
      echo    '<th>' . __('Connected to', 'bonds') . '</th>';
      echo    '<th width="1%">' . __('Outlet', 'bonds') . '</th>';
      echo    '<th>' . __('Serial', 'bonds') . '</th>';
      echo    '<th>' . __('Type', 'bonds') . '</th>';
      echo    '<th>' . __('Model', 'bonds') . '</th>';
      echo   '<th>' . __('Location', 'bonds') . '</th>';
      echo  '</tr>';

      $n = 1;
      $data = $self->getBondsFromIdAndType( $item->getID(), $item->getType(), "ORDER BY `outlet_id`" );
      foreach($data as $key => $assoc) {
         echo '<tr class="tab_bg_'.(($n%2==0)?"2":"1").'">';
         echo '<td><input type="checkbox" name="item['.$key.']" value="1"></td>';
         echo '<td class="center">'.$assoc['outlet_id'].'</td>';
         echo '<td class="center">'.$assoc['outlet_type'].'</td>';

         $self->showLine($assoc['connected_to']);

         echo '</tr>';
         $n++;
      }
      echo '</table>';
      echo '</div>';

      Html::openArrowMassives("bonds$rand",true);
      Html::closeArrowMassives(array('delete' => _sx('button','Delete')));
      Html::closeForm();
   }


   function showLine($id) {
      $this->getFromDB($id);

      $asset_id = $this->fields['asset_id'];
      $asset_class = $this->fields['asset_type'];
      $asset_url = Toolbox::getItemTypeFormURL($asset_class);
      $asset_model_table = getTableForItemType($asset_class."Model");
      $asset_model_field = getForeignKeyFieldForTable($asset_model_table);

      $class = new $asset_class;
      $class->getFromDB($asset_id);

      echo '<td class="center"><a href="'.$asset_url.'?id='.$asset_id.'">'.
         Dropdown::getDropdownName( getTableForItemType($asset_class), $asset_id) .
       '</a></td>';
      echo '<td class="center">'.$this->getField('outlet_id').'</td>';
      echo '<td class="center">'.$class->getField('serial').'</td>';
      echo '<td class="center">'.$class::getTypeName(2).'</td>';
      echo '<td class="center">'.Dropdown::getDropdownName($asset_model_table,$class->getField($asset_model_field)).'</td>';
      echo '<td class="center">'.Dropdown::getDropdownName("glpi_locations",$class->getField("locations_id")).'</td>';
   }


   static function getAssetClasses() {
      static $types = array( 'Computer','NetworkEquipment','Peripheral' );
      return $types;
   }


   function getBondsFromIdAndType($asset_id, $asset_type, $option = '') {
      $data = $this->find("`asset_id`='$asset_id' AND `asset_type`='$asset_type'" . $option);

      return $data;
   }


   static function listOutletTypes() {
      global $DB;

      $result = $DB->query("SHOW COLUMNS FROM `".self::getTable()."` WHERE Field='outlet_type'");
      $data   = $DB->fetch_assoc($result);

      preg_match('/^enum\((.*)\)$/', $data['Type'], $matches);
      foreach( explode(',', $matches[1]) as $value ) {
         $enum[] = trim( $value, "'" );
      }
      return $enum;
   }


   function addBond(array $input) {
      static $fields = array(
         'asset_type',
         'asset_id',
         'outlet_id',
         'outlet_type',
         'foreign_asset_type',
         'foreign_asset_id',
         'foreign_outlet_id',
      );
      $this_input = array();
      $foreign_input = array();

      foreach($fields as $field) {
         if (!isset($input[$field]) || empty($input[$field]))
            return false;

         if (preg_match("/^foreign_/",$field)) {
            $f = str_replace("foreign_", '', $field);
            $foreign_input[$f] = $input[$field];
         } else {
            $this_input[$field] = $input[$field];
         }
      }

      $this_id = $this->add($this_input);

      if (empty($this_id))
         return false;

      $foreign_input['outlet_type']  = $this_input['outlet_type'];
      $foreign_input['connected_to'] = $this_id;
      $foreign_id = $this->add($foreign_input);

      if (empty($foreign_id))
         return false;

      $this->fields['id'] = $this_id;
      $this->fields['connected_to'] = $foreign_id;
      $this->updateInDB(array('connected_to'));
   }


   function deleteBond(array $input) {
      $this->getFromDB($input['id']);
      $connected_to = $this->getField('connected_to');

      $this->delete($input);
      $this->delete(array('id' => $connected_to));
   }
}

?>
