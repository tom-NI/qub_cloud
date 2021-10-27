// function to draw a line chart
function drawLineChart(dataArray, idOfElementToFill, chartTitle) {
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
    var data = google.visualization.arrayToDataTable(dataArray);

    var options = {
        title: chartTitle,
        hAxis: {title: 'Date and Time',  titleTextStyle: {color: '#ffffff'}, slantedTextAngle:90},
        vAxis: {minValue: 0},
        legend: { position: 'top'},
        chartArea: {width: '90%'},
        titleTextStyle: {
            fontSize: 18,
            bold: true,
        }
    };

    var chart = new google.visualization.LineChart(document.getElementById(idOfElementToFill));
    chart.draw(data, options);
    }
}