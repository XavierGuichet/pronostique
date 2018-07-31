<div class="history-graph-wrapper">
<script src="/wp-content/plugins/pronostiques/Chart.min.js"></script>
<h5 class="graphtitle">Evolution du profit sur les 40 derniers paris</h5>
<div class="history-graph-container">
<canvas id="buyers" width="720" height="200"></canvas>
</div>
<script>
var dataset = [<?=$graphdata?>];
let minDataValue = Math.min.apply(null,dataset);
minDataValue = Math.floor((minDataValue) / 5) * 5;

var ctx = document.getElementById('buyers').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [<?=$labels?>],
        datasets: [{
            label: '',
            data : dataset,
            lineTension: 0.5,
            backgroundColor: 'rgba(95, 140, 163, 1)',
            borderColor: 'rgba(95, 140, 163, 1)',
            fillColor: 'rgba(95, 140, 163, 1)',
            pointBackgroundColor: 'rgba(95, 140, 163, 1)',
            pointBorderColor: '#fff',
            borderWidth: 1,
            fill: 'start'
        }]
    },
    options: {
      maintainAspectRatio: false,
      responsiveAnimationDuration: 0,
      title: {
        display: false,
      },
      legend: {
        display: false,
      },
      animation: {
          duration: 0,
      },
      tooltips: {
        displayColors: false,
      },
      scales: {
          gridLines: [{
            color: 'rgba(0,0,0,.1)'
          }],
          yAxes: [{
              ticks: {
                  min: minDataValue,
                  stepSize: 5,
                  showLabelBackdrop: false
              },
              stacked: true
          }]
      }
    }
});
</script>
</div>
