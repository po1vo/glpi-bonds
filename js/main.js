jsPlumb.ready(function() {
   jsPlumb.importDefaults({
      Anchor      : "Center",
      Anchors     : ["Center", "Center"],
      Connector   : "Straight",
      Container   : $("body"),
      DragOptions : { cursor: "pointer" },
      Endpoint    : "Rectangle",
   });

   jsPlumb.draggable($(".pdu"));

   var conn4Color;
   endpoints = {};

   jsPlumb.setSuspendDrawing(true);

   $(".psu, .outlet").each(function(index){
      if ( $(this).attr('id') in excludes )
         return;

      endpoints[$(this).attr('id')] = jsPlumb.addEndpoint( this, {
         isSource:true,
         isTarget:true,
         hoverPaintStyle: { fillStyle:"#449999" },
         connectorHoverStyle: {strokeStyle:"#449999"},
         dropOptions: { tolerance:"touch", hoverClass:"dropHover" },
      });
   });

   $.each(connections, function( src, dst ) {
      if ( src in excludes || dst in excludes )
         return;

      conn4Color = '#'+(Math.random()).toString(16).substr(2,6);

      endpoints[src].setPaintStyle({ fillStyle: conn4Color });
      endpoints[dst].setPaintStyle({ fillStyle: conn4Color });

      jsPlumb.connect({
         source: endpoints[src],
         target: endpoints[dst],
         deleteEndpointsOnDetach:false,
         paintStyle: { 
            lineWidth   : 4,
            strokeStyle : conn4Color,
         },
      });
   });

   jsPlumb.setSuspendDrawing(false, true);


   $(".pdu").dblclick(function() {
      kids = $(this).children();
      var len = kids.length;
      jsPlumb.setSuspendDrawing(true);

      for (var i=len; i>1; i--)
         kids.eq(len-i).detach().insertAfter(kids.last());

      jsPlumb.setSuspendDrawing(false, true);
      jsPlumb.recalculateOffsets(this);
   });

   $("input").click(function( event ) {
      var conn = {};

      $.each(jsPlumb.getConnections(), function (idx, connection) {
         conn[connection.sourceId] = connection.targetId;
      });

      jsPlumb.selectEndpoints().each(function(endpoint) {
         if (endpoint.connections.length > 0)
            return;

         conn[endpoint.elementId] = null;
      });

      $.ajax({
         url  : "../ajax/update.php",
         type : 'POST',
         data : conn
      });
   });
});
