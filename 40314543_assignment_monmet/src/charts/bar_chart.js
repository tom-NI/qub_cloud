// draw a simple bar chart
function drawBarChart(dataArray, idOfElementToFill, chartTitle, chartSubTitle) {
    google.charts.load('current', {'packages':['bar']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
      var data = google.visualization.arrayToDataTable(dataArray);

      var options = {
        chart: {
          title: chartTitle,
          subtitle: chartSubTitle,
        },
        titleTextStyle: {
            fontSize: 18,
            bold: true,
        },
        legend: {position: 'none'},
        chartArea: {bottom: 100, 'width': '80%', 'height': '100%'},
        hAxis: {textPosition: 'none'}
      };

      var chart = new google.charts.Bar(document.getElementById(idOfElementToFill));
      chart.draw(data, google.charts.Bar.convertOptions(options));
    }

}