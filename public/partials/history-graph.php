<div>
<script src="/wp-content/plugins/pronostique/Chart.min.js"></script>
<h5 class="graphtitle">Evolution du profit sur les 50 derniers paris</h5>
<canvas id="buyers" width="720" height="180"></canvas>

<script>
var options = {
scaleOverlay : false,
scaleOverride : false,
scaleLineColor : "rgba(0,0,0,.1)",
scaleLineWidth : 1,
scaleShowLabels : true,
scaleLabel : "<%=value%>",
scaleFontFamily : "\'Arial\'",
scaleFontSize : 12,
scaleFontStyle : "normal",
scaleFontColor : "#666",
scaleShowGridLines : true,
scaleGridLineColor : "rgba(0,0,0,.08)",
scaleGridLineWidth : 1,
bezierCurve : true,
pointDot : true,
pointDotRadius : 3,
pointDotStrokeWidth : 0,
datasetStroke : false,
datasetStrokeWidth : 1,
datasetFill : true,
animation : false
};

var data = {
labels : [<?=$labels?>],
datasets : [
    {
        strokeColor : "rgba(95, 140, 163, 1)",
        pointColor : "rgba(95, 140, 163, 1)",
        fillColor: "rgba(95, 140, 163, 1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(95, 140, 163, 1)",
        pointStrokeColor : "#fff",
        data : [<?=$graphdata?>]
    }
]

};

var ctx = document.getElementById("buyers").getContext("2d");
new Chart(ctx).Line(data,options);
</script>
</div>
