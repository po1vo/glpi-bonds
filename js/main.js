jsPlumb.ready(function() {
   jsPlumb.importDefaults({
      Anchor      : "Center",
      Anchors     : ["Center", "Center"],
      Connector   : "Straight",
      Container   : $("body"),
      DragOptions : { cursor: "pointer" },
      Endpoint    : [ "Dot", {"radius": 11} ],
      PaintStyle  : { strokeWidth: 1, stroke: "black"},
   });

   jsPlumb.draggable($(".pdu"));

   var conn4Color;
   endpoints = {};

   jsPlumb.batch(function() {
      $(".psu, .outlet").each(function(index){
         if ( $(this).attr('id') in excludes )
            return;

         endpoints[$(this).attr('id')] = jsPlumb.addEndpoint( this, {
            isSource: true,
            isTarget: true,
            hoverPaintStyle: {
               fill: "red",
               outlineStroke: "white",
               outlineWidth: 2
            },
            connectorHoverStyle: {
               stroke: "red",
               strokeWidth: 4
            },
         });
      });

      $.each(connections, function( src, dst ) {
         if ( src in excludes || dst in excludes )
            return;

         conn4Color = '#'+(Math.random()).toString(16).substr(2,6);

         endpoints[src].setPaintStyle({ fill: conn4Color });
         endpoints[dst].setPaintStyle({ fill: conn4Color });

         jsPlumb.connect({
            source: endpoints[src],
            target: endpoints[dst],
            deleteEndpointsOnDetach: false,
            paintStyle: {
               stroke:      conn4Color,
               strokeWidth: 3
            },
         });
      });
   });


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
