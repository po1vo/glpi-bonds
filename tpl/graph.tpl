<!DOCTYPE html>
<html>
   <head>
      <link href="<?php echo $CFG_GLPI['root_doc']; ?>/plugins/bonds/css/main.css" type="text/css" rel="stylesheet">
      <script type='text/javascript' src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
      <script type='text/javascript' src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
      <script type='text/javascript' src="<?php echo $CFG_GLPI['root_doc']; ?>/plugins/bonds/js/jsPlumb-1.5.5-min.js"></script> 
      <script type='text/javascript'>
         var connections = JSON.parse('<?php echo json_encode($bonds); ?>');
         var excludes = JSON.parse('<?php echo json_encode($excludes); ?>');
      </script>
      <script type='text/javascript' src="<?php echo $CFG_GLPI['root_doc']; ?>/plugins/bonds/js/main.js"></script>
   </head>
<body>
<ul class="legend">
<li>You can move PDUs as you like</li>
<li>Double click a PDU to change the order of outlets (buggy)</li>
<li>Inactive outlets mean the outlet is connected to a device beyond this scope</li>
</ul>
<table class="rack">
   <tr>
      <th colspan="<?php echo $this->max_outlets + 2; ?>">
         <span><?php echo $this->PluginRacksRack->fields['name']; ?></span>
         <input type="submit" value="Save Changes" />
      </th>
   </tr>
<?php
   $j=0;
   for( $i = $this->PluginRacksRack->fields['rack_size']; $i>=1; $i-- ) {
      if (array_key_exists($i, $this->rack)) {
         extract($this->rack[$i],EXTR_OVERWRITE);
?>
   <tr class="unit">
      <td class="unit_number"><?php echo $i; ?></td>
      <td class="device_name"><?php echo $name; ?></td>
<?php
         if (isset($outlets) && count($outlets) > 0) {
            $outlets = array_unique( array_merge(range(1, self::MAX_OUTLETS), $outlets));
         } else {
            $outlets = range(1, self::MAX_OUTLETS);
         }

         for ($k = 0; $k < ($this->max_outlets - count($outlets)); $k++) {
?>
      <td></td>
<?php
         }

         foreach($outlets as $x) {
?>
      <td class="psu" id="<?php echo $class.'_'.$items_id.'_'.$x ?>"><span class="num"><?php echo $x; ?></span></td>
<?php
         }
?>
      </td>
   </tr>
<?php
         if ($size > 1)
            $j = $size - 1;

      } else {
?>
   <tr class="unit <?php if($j>0){$j--;}else{echo "empty";} ?>">
      <td class="unit_number"><?php echo $i; ?></td>
<?php
      for ($m=0; $m < $this->max_outlets + 1; $m++) {
?>
      <td></td>
<?php
      }
?>
   </tr>
<?php
      }
   }
?>
   <tr><th colspan="<?php echo $this->max_outlets + 2; ?>"><input type="submit" value="Save Changes" /></th></tr>
</table>
<?php
   $j = 1;
   foreach ($this->pdus as $key => $pdu) {
?>
<div class="pdu" style="top: 10em; left:<?php echo $j*4; ?>em;">
   <div class="block_title"><?php echo $pdu['name']; ?></div>
<?php
      for ($i = $pdu['min_outlet_id']; $i <= $pdu['max_outlet_id']; $i++) {
?>
   <div class="outlet" id="NetworkEquipment_<?php echo $pdu['id']; ?>_<?php echo $i; ?>"><span class="num"><?php echo $i; ?></span></div>
<?php
      }
?>
</div>
<?php
      $j++;
   }
?>
</body>
</html>