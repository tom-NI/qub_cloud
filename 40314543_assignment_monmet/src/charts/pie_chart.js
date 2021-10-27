// function to draw a pie chart using google charts
function drawMetricPieChart(dataArray, idOfElementToFill, providedTitle) {
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawPieChart);

    function drawPieChart() {
    var data = google.visualization.arrayToDataTable(dataArray);

    var options = {
        reverseCategories : true,
        colors: ['#FF6347', '#48c774', '#FFD700', '#00BFFF'],
        fontSize: 16,
        width: 260,
        height: 260,
        is3D: false,
        legend: 'none',
        title: providedTitle,
        titleTextStyle: { 
            color: '#000',
            fontName: "Arial",
            fontSize: 16,
            bold: true,
            italic: false
        },
        chartArea: {left: 0, top: 50, 'width': '100%', 'height': '100%'},
        annotations: {
            textStyle: {
                fontName: 'Arial',
                fontSize: 16,
                color: '#ffffff',
                bold: false,
                italic: false,
                auraColor: '#ffffff',
                opacity: 1
            }
        },
    };

    var chart = new google.visualization.PieChart(document.getElementById(idOfElementToFill));
    chart.draw(data, options);
    }
}