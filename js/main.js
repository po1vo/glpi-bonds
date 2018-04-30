jsPlumb.ready(function() {
   jsPlumb.importDefaults({
      Anchor      : "Center",
      Anchors     : ["Center", "Center"],
      Connector   : "Straight",
      Container   : $("body"),
      DragOptions : { cursor: "pointer" },
      DropOptions : { tolerance: "touch", hoverClass: "dropHover" },
      Endpoint    : [ "Dot", {"radius": 10} ],
      PaintStyle  : { strokeWidth: 1, stroke: "black"},
   });

   var EndpointOptions = {
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
   };

   jsPlumb.draggable($(".pdu"));
   $(".plus_button").mousedown(function(e){
      e.stopPropagation();
   });

   var conn4Color;
   endpoints = {};

   jsPlumb.batch(function() {
      $(".psu, .outlet").each(function(index){
         if ( $(this).attr('id') in excludes )
            return;

         endpoints[$(this).attr('id')] = jsPlumb.addEndpoint(this,EndpointOptions);
      });

      $.each(connections, function( src, dst ) {
         if ( src in excludes || dst in excludes )
            return;

         conn4Color = '#' + ("000000" + Math.floor(Math.random() * 0xCCCCCC).toString(16)).substr(-6);

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

      jsPlumb.batch(function() {
         for (var i=len; i>1; i--)
            kids.eq(len-i).detach().insertAfter(kids.last());
      });

      jsPlumb.recalculateOffsets(this);
   });

   $("input").click(function(event) {
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

      location.reload();
   });

   // Adds "TOADD" number of outlets to a pdu
   $(".plus_button").click(function() {
      var TOADD = 5;
      var _parent = $(this).parent();
      var arr = _parent.find(".outlet").last().attr("id").split("_");
      var last_outlet = parseInt(arr[2]);
      last_outlet++;

      jsPlumb.batch(function() {
         for (i = last_outlet; i < last_outlet + TOADD; i++){
            var n = document.createElement("span");
            $(n).addClass("num");
            $(n).text(i);
            var c = document.createElement("div");
            $(c).addClass("outlet");
            $(c).attr("id", arr[0] + "_" + arr[1] + "_" + i);
            $(c).append(n);
            _parent.append(c);
            endpoints[$(c).attr("id")] = jsPlumb.addEndpoint($(c),EndpointOptions);
         }
      });
   });

   $(".device_name span, .block_title span").dblclick(function(e){
      e.preventDefault();
      e.stopPropagation();
      var url = $(this).attr('url');
      window.open(url, '_blank');
   });
});
