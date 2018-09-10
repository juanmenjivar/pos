
function agregar_info(texto) {
    $("#estadisticas").append('<div style="padding-top:5px;">' + texto + '</div>');
}

function agregar_panel(titulo, contenido) {
	var html = '';
	
	html += '<div class="panel panel-default">';
		html += '<div class="panel-heading">';
			html += '<h3 class="panel-title">' + titulo + '</h3>';
		html += '</div>';

		html += '<div class="panel-body">';
			html += contenido;
		html += '</div>';
	html += '</div>';
	$("#estadisticas").append(html);
        
}

function estadisticas() {
    var periodo_inicio = $('#periodo_inicio').val() + ' 00:00:00';
    var periodo_final = $('#periodo_final').val() + ' 23:59:59';

    $("#estadisticas").html('<b>{cargando estadísticas}</b>');
    rsv_solicitar('estadisticas',{periodo_inicio: periodo_inicio, periodo_final: periodo_final},function(datos){
        $("#estadisticas").empty();
        $("#estadisticas").html(datos.html); 
        accordion();
        graficar();
    });
    
}

$(function(){
    $("#actualizar").click(function(){
        estadisticas();        
    });
});

function accordion(){
    var acc = document.getElementsByClassName("accordion");
        var i;

        for (i = 0; i < acc.length; i++) {
          acc[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            
            if (panel.style.maxHeight){
              panel.style.maxHeight = null;
            } else {
              panel.style.maxHeight = panel.scrollHeight + "px";
            } 
          });
        }
}

function graficar(){
    // Load google charts
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);                        

            // Draw the chart and set the chart values
            function drawChart() {
                var obj= document.getElementById('chartArr');
                var s=obj.value;
                s=s.replace(/#@#/gi,' ');
                var arr = JSON.parse(s);
            
                // JSON.parse(document.getElementById('chartArr').value);

              var data = google.visualization.arrayToDataTable(arr);

              // Optional; add a title and set the width and height of the chart
              var options = {'title':'Ventas Empleados', 'width':550, 'height':400};

              // Display the chart inside the <div> element with id="piechart"
              var chart = new google.visualization.PieChart(document.getElementById('piechart'));
              chart.draw(data, options);
            }
}