<?php
$stat_html = '';
require './TPL/myLog.php';

function estadisticas_agregar_info($texto) {
    global $stat_html;
    $stat_html .= '<h3 style="color:#ff0000;" class="panel-title">' . $texto . '</h3>';
}

function estadisticas_agregar_panel($titulo, $contenido) {
    global $stat_html;

    $stat_html .= '<div class="panel panel-default">';
    $stat_html .='<div class="panel-heading">';
    $stat_html .='<h3 class="panel-title">' . $titulo . '</h3>';
    $stat_html .='</div>';

    $stat_html .='<div class="panel-body">';
    $stat_html .= $contenido;
    $stat_html .='</div>';
    $stat_html .='</div>';
}


function estadisticas_renderizar($datos) {
    $buffer = '<table class="blueTable"> ';
    $buffer1 = '';
    $buffer2 = '';
    $buffer3 = '';
    $label = '';
    
    // ************ Distribucion de ventas por dia
    if (!empty($datos['aux']['venta_por_dias'])) {
        $buffer1 = '';

        foreach ($datos['aux']['venta_por_dias'] as $dia => $valor) {
            $buffer1 .= "<thead><tr><th>Ventas por dia</th><th>Total</th></tr></thead>"
                    . "<tr>"
                    ."<th>" . $datos['aux']['venta_por_dias'][$dia]['dia'] . "</th> " 
                    ."<th> $" . $datos['aux']['venta_por_dias'][$dia]['total'] . "</th>"
                    ."</tr>";
        }        
    } else {
        $buffer1 ='<tr><th>Sin datos de rendimiento de ventas por dia</th></tr>';
    }
    
    // ************ Distribucion de ventas por mes
    if (!empty($datos['aux']['venta_por_mes'])) {
        $buffer2 = '';

        foreach ($datos['aux']['venta_por_mes'] as $indice => $valor) {
            $buffer2 .= "<thead><tr><th>Ventas por mes</th><th>Total</th></tr></thead>"
                    . "<tr>"
                    . "<th>" . $datos['aux']['venta_por_mes'][$indice]['mes'] . "</th>"
                    . "<th>$" . $datos['aux']['venta_por_mes'][$indice]['total'] . "</th>"
                    . "</tr>";
        }        
    } else {
        $buffer2='<tr><th>Sin datos de rendimiento de ventas por mes</th></tr>';
    }
    
    // ************ Distribucion de comida/platos por dia
    if (!empty($datos['aux']['pizzas_por_dia'])) {
        $buffer3 = '';

        foreach ($datos['aux']['pizzas_por_dia'] as $index => $valor) {
            $buffer3 .= "<thead><tr><th>Platos vendidos por dia</th><th>Total</th></tr></thead>"
                    . "<tr>"
                    . "<th>" . $datos['aux']['pizzas_por_dia'][$index]['dia'] . "</th>"
                    . "<th>" . $datos['aux']['pizzas_por_dia'][$index]['cantidad'] . "</th>"
                    . "</tr>";
        }        
    } else {
        $buffer2='<tr><th>Sin datos de rendimiento de venta de platos por dia</th></tr>';        
    }
    
    if($buffer1!="" or $buffer2!="" or $buffer3!=""){
        $buffer.= $buffer1 . $buffer2. $buffer3  . "</table>";
        estadisticas_agregar_panel('Ventas', $buffer);
    }
    
    // ************ Distribucion de carga de servicio entre meseros
    if (!empty($datos['aux']['dsn'])) {
        $buffer = '<div id="piechart"></div>';
        $buffer3=array(array('Empleado', '%Venta'));
        
        
        foreach ($datos['aux']['dsn'] as $usuario => $valor) {                                           
            $buffer .=  "<button class='accordion'><table class='blueTable'><thead><tr>" 
                    . "<th>Meser@:</th><th>" .$datos['aux']['dsn'][$usuario]['usuario']."</th> "                     
                    . "<th>Porcentaje:</th><th>" . $datos['aux']['dsn'][$usuario]['porcentaje'] . "% </th> " 
                    . "<th>Total:</th>   <th>$" . $datos['aux']['dsn'][$usuario]['subtotal'] . "</th> </thead><tfoot><td>Click para abrir</td></tfoot> </table>"
                    . "</button>" ;
             
            // array for graph
             //addLog (json_encode($datos['aux']['dsn'][$usuario]));
             array_push($buffer3, array(str_replace(' ','#@#', $datos['aux']['dsn'][$usuario]['usuario'] ),
                        $datos['aux']['dsn'][$usuario]['porcentaje']));
            
            /*$buffer3[]=$datos['aux']['dsn'][$usuario]['usuario'];
            $buffer3[]=$datos['aux']['dsn'][$usuario]['porcentaje'];
            $buffer3[]=$datos['aux']['dsn'][$usuario]['color'];*/
                                         
            $buffer2='';
            $buffer2='<div class="accordionpanel "> '
                    . '<table class="blueTable"> <tr>'
                    . '<thead><th>Tipo</th>'
                    . '<th>Producto</th>'
                    . '<th>Cantidad</th>'
                    . '<th>Total</th>'
                    . '</tr></thead><tbody>';
            $t1=0.0000;
            $t2=0.0000;
            foreach ($datos['aux']['dsn'][$usuario]['prods'] as $row) {
                $buffer2 .= '<tr>'
                        . '<th>' . $row['tipo'] . '</th>'
                        . '<th>' . $row['producto'] . '</th>'
                        . '<th>' . $row['cantidad'] . '</th>'
                        . '<th>' . $row['total'] .  '</th></tr>';
                if($row['tipo']='Cuenta con propina'){
                    $t1+=$row['total'];
                }elseif($row['tipo']='Cuenta sin propina'){
                    $t2+=$row['total'];
                }
            }
            
            $buffer2.='</tbody>'
                    . '<tfoot><tr>'
                    . '<td>Total con propina:</td>'
                    . '<td>'.round($t1,2).'</td>'
                    . '<td>Total sin propina:</td>'
                    . '<td>'.round($t2,2).'</td>'
                    . '</tr></tfoot>'
                    . '</table>'; 
            $buffer2.='</div>';
            
            $buffer.=$buffer2;
        }        
        $buffer .=  "<input id='chartArr' name='chartArr' type='hidden' value=" . json_encode( $buffer3) . ">";
        estadisticas_agregar_panel('Distribucion de carga de servicio entre meseros', $buffer);
        
    } else {
        estadisticas_agregar_panel('Distribucion de carga de servicio entre meseros', 'Sin datos de rendimiento de meseros');
    }

    // ************ Distribucion de VENTA por hora
    if (!empty($datos['aux']['venta_por_horas'])) {
        $buffer = '<table class="blueTable">'
                . '<thead><tr>'
                . '<th>Hora</th>'
                . '<th>Total $</th>'
                . '<th>%</th>'
                . '</tr></thead>';

        foreach ($datos['aux']['venta_por_horas'] as $hora => $valor) {
            $buffer .= "<tr><th>" . $datos['aux']['venta_por_horas'][$hora]['hora'] . ":00  "
                    . "<th>$" . $datos['aux']['venta_por_horas'][$hora]['total'] . " </th> " 
                    . "<th>" . $datos['aux']['venta_por_horas'][$hora]['porcentaje'] . "%</th></tr>";
        }
        $buffer .= '</table>';

        estadisticas_agregar_panel('Distribucion de ventas por hora', $buffer );
    } else {
        estadisticas_agregar_panel('Distribucion de ventas por hora', 'Sin datos de rendimiento de ventas por hora');
    }
    
    // ************ Distribucion de CUENTAS por hora
    if (!empty($datos['aux']['cuentas_por_horas'])) {
        $buffer = '<table class="blueTable">';
        $buffer .= "<thead><tr>"
                . "<th>Hora</th>"
                . "<th>Cantidad</th>"
                . "</tr></thead>";
                
        foreach ($datos['aux']['cuentas_por_horas'] as $hora => $valor) {
            $buffer .= "<tr><th>" . $datos['aux']['cuentas_por_horas'][$hora]['hora']  . "</th>"
                    . "<th>" . $datos['aux']['cuentas_por_horas'][$hora]['num_cuentas'] . "</th></tr>";
        }
        $buffer .= '</table>';
        estadisticas_agregar_panel('Numero de Cuentas abiertas por hora',  $buffer );
    } else {
        estadisticas_agregar_info('Sin datos de rendimiento de cuentas por hora');
    }

    // ************ CorteZ
    if (!empty($datos['aux']['cortez_sum'])) {
        $buffer = '';
        $buffer .= '<table class="vitamins" ">';
        $buffer .= '<thead><tr><th>Concepto</th><th>Total</th></tr></thead><tbody>';

        foreach ($datos['aux']['cortez_sum'] as $indice => $valor) {
            $buffer .= "<tr>"
                    . "<th>" . $indice . "</th>"
                    . "<th>$" . $datos['aux']['cortez_sum'][$indice] . "</th>"
                    . "</tr>";
        }

        $buffer .= '</tbody></table>';

        estadisticas_agregar_panel('Totales de corte Z para periodo', $buffer);
    } else {
        estadisticas_agregar_info('Sin datos de corte Z para el periodo especificado');
    }
    
    // ************ Productos vendidos
    $buffer1=0;
    if (!empty($datos['aux']['productos_por_categoria'])) {
        $buffer = '';
        $buffer .= '<table class="blueTable" ">';
        $buffer .= '<thead><tr><th>Grupo</th><th>Producto</th><th>Cantidad</th></tr></thead><tbody>';

        foreach ($datos['aux']['productos_por_categoria'] as $producto => $valor) {
            $buffer .= "<tr><td>" . $datos['aux']['productos_por_categoria'][$producto]['grupo'] . "</td>"
                    . "<td>" . $datos['aux']['productos_por_categoria'][$producto]['nombre'] . "</td>"
                    . "<td>" . $datos['aux']['productos_por_categoria'][$producto]['cantidad'] . "</td>"
                    . "</tr>";
            
            $buffer1+=$datos['aux']['productos_por_categoria'][$producto]['cantidad'];
        }
        $buffer .= '</tbody><tfoot>'
                . '<tr><td></td><td></td><td>' . $buffer1 . '</td></tr></tfoot>';
        $buffer .= '</table>';
        estadisticas_agregar_panel('Productos mas vendidos por categoria', $buffer);
    } else {
        estadisticas_agregar_info('Sin datos de rendimiento de productos vendidos');
    }

    // ************ Uso de Mesas
    if (!empty($datos['aux']['uso_mesas'])) {
        $buffer = '';
        $buffer .= '<table class="blueTable">';
        $buffer .= '<thead><tr><th>Mesa</th><th># Cuentas</th></tr></thead><tbody>';
        foreach ($datos['aux']['uso_mesas'] as $indice => $valor) {
            $buffer .= "<tr><td>Mesa #" . $datos['aux']['uso_mesas'][$indice]['ID_mesa'] . "</td>"
                    . "<td>" . $datos['aux']['uso_mesas'][$indice]['cantidad'] . "</td></tr>";
        }
        $buffer .= '</tbody></table>';
        estadisticas_agregar_panel('Mesas mas utilizadas', $buffer);
    } else {
        estadisticas_agregar_info('Sin datos de uso de mesa');
    }

    // ************ Tiempos de despacho
    $buffer='';
    $buffer.='<table class="blueTable">';
    $buffer.='<thead>'
            . '<tr><th>Tiempo promedio de despacho (Prom Std./Desviacion Std.)</th>'
            . '<th>Tiempo maximo de despacho [(fechahora_despachado- fechahora_pedido) * #Cuentas]</th></tr>'
            . '</thead><tbody>'
            . '<tr><td> ' . $datos['aux']['tps'] . ' mins </td>'
            . '<td>' . $datos['aux']['tms'] . ' mins </td></tr>'
            . '</tbody></table>';
        
    estadisticas_agregar_panel('Tiempos de despacho', $buffer);

    // ************ Anulaciones
    if (!empty($datos['aux']['anulaciones'])) {
        $buffer = '';        
        $cta = "";

        foreach ($datos['aux']['anulaciones'] as $idx => $valor) {
            if ($cta !== $datos['aux']['anulaciones'][$idx]['ID_cuenta']) {
                
                $buffer = '<table class="blueTable">';
                $cta = $datos['aux']['anulaciones'][$idx]['ID_cuenta'];
                                
                $buffer .= '<thead><tr><td>Cuenta</td><td>Mesa</td><td>Mesero</td><td>Fecha Hora Anulacion</td></tr></thead>' 
                        . '<tbody><tr><td>'.$datos['aux']['anulaciones'][$idx]['ID_cuenta'] . '</td>'
                        . '<td>' . $datos['aux']['anulaciones'][$idx]['ID_mesa'] . '</td>'
                        . '<td>' . $datos['aux']['anulaciones'][$idx]['ID_mesero'] . '-' 
                        . $datos['aux']['anulaciones'][$idx]['usuario'] . '</td>'
                        . '<td>' . $datos['aux']['anulaciones'][$idx]['fechahora_anulado'] . '</td></tr></tbody></table>';                
            
                $buffer .= '<table class="blueTable">'
                        . '<thead><tr><td>Productos anulados</td><td>Fecha Hora Pedido</td>'
                        . '<td>Precio Original $</td><td>Precio Gravado $</td></thead>' ;
            }
            
                $buffer .=  '<tfoot><tr><td>'. $datos['aux']['anulaciones'][$idx]['nombre'] . '</td>'
                . '<td> ' . $datos['aux']['anulaciones'][$idx]['fechahora_pedido'] . '</td>'
                . '<td> ' . $datos['aux']['anulaciones'][$idx]['precio_original'] . '</td>'
                . '<td> ' . $datos['aux']['anulaciones'][$idx]['precio_grabado'] . '</td></tr> </tfoot>';
        }
        $buffer .= '</table>';
        estadisticas_agregar_panel('Anulaciones', $buffer);
    } else {
        estadisticas_agregar_info('Sin datos de anulaciones');
    }

    // ************ Compras
    if (!empty($datos['aux']['listacompras'])) {
        $buffer = '';
        $buffer1=0;
        $buffer2=0;
        $buffer3=0;
        
        $buffer .= '<table class="blueTable"><thead>'
                . '<tr><td>Empresa</td><td>Fecha compra</td>'
                . '<td>Descripcion</td><td>Valor $</td>'
                . '<td>Via</td></tr></thead><tbody>';

        foreach ($datos['aux']['listacompras'] as $indice => $valor) {            
            $buffer .= '<tr><td> ' . $datos['aux']['listacompras'][$indice]['empresa'] . '</td> '
                    . '<td>' . $datos['aux']['listacompras'][$indice]['fechatiempo'] . '</td> '
                    . '<td> ' . $datos['aux']['listacompras'][$indice]['descripcion'] . '</td> '
                    . '<td> ' . $datos['aux']['listacompras'][$indice]['precio'] . '</td> '
                    . '<td>' . $datos['aux']['listacompras'][$indice]['via'] . '</td></tr> ';   
            
            if($datos['aux']['listacompras'][$indice]['via']=='caja'){
                $buffer1+=$datos['aux']['listacompras'][$indice]['precio'];
            }elseif($datos['aux']['listacompras'][$indice]['via']=='gerencia'){
                $buffer2+=$datos['aux']['listacompras'][$indice]['precio'];
            }else{
                $buffer3+=$datos['aux']['listacompras'][$indice]['precio'];
            }
        }
        $buffer .= '</tbody>'
                . '<tfoot><td>Total caja $'.$buffer1.'</td><td>Gerencia $'.$buffer2.'</td><td>Otro $'.$buffer3.'</td></tfoot>'
                . '</table>';
        estadisticas_agregar_panel('Lista Compras', $buffer);
    } else {
        estadisticas_agregar_info('Sin datos de Compras');
    }

    // ************ Productos eliminados
    if (!empty($datos['aux']['ctasproddel'])) {
        $buffer = '';
        $cta = "";

        foreach ($datos['aux']['ctasproddel'] as $idx => $valor) {
            if ($cta !== $datos['aux']['ctasproddel'][$idx]['ID_cuenta']) {
                $cta = $datos['aux']['ctasproddel'][$idx]['ID_cuenta'];
                
                $buffer = '<table class="blueTable">'
                . '<thead><tr><td>Cuenta</td><td>Mesa</td><td>Mesero</td><td>Fecha Hora</td></tr></thead>' 
                . '<tbody><tr><td>' . $datos['aux']['ctasproddel'][$idx]['ID_cuenta'] . '</td> '
                . '<td> ' . $datos['aux']['ctasproddel'][$idx]['ID_mesa'] . '</td> '
                . '<td> ' . $datos['aux']['ctasproddel'][$idx]['ID_mesero'] . '-' 
                        . $datos['aux']['ctasproddel'][$idx]['usuario'] . '</td> '
                . '<td> ' . $datos['aux']['ctasproddel'][$idx]['fechahora_pedido'] . '</td> </tbody></table>';
                
                $buffer .= '<table class="blueTable">'
                        . '<thead><tr><td>Productos</td><td>Fecha Hora Pedido</td><td>Fecha Hora Eliminado:</td>'
                        . '<td>Precio Original $</td><td>Precio Gravado $</td><td>Comentario:</td></thead>' ;
            }

            $buffer .= '<tfoot><tr><td> ' . $datos['aux']['ctasproddel'][$idx]['nombre'] . '</td> '
                    . '<td> ' . $datos['aux']['ctasproddel'][$idx]['fechahora_pedido'] . ' </td>'
                    . '<td>' . $datos['aux']['ctasproddel'][$idx]['fechahora'] . '</td>'
                    . '<td>' . $datos['aux']['ctasproddel'][$idx]['precio_original'] . '</td> '
                    . '<td>' . $datos['aux']['ctasproddel'][$idx]['precio_grabado'] . ''
                    . '<td>' . $datos['aux']['ctasproddel'][$idx]['nota'] . '</td>';
        }
        $buffer .= '</tbody></table>';
        estadisticas_agregar_panel('Cuentas con Productos eliminados', $buffer);
    } else {
        estadisticas_agregar_info('Sin datos de Cuentas con Productos eliminados');
    }

    // ************ Productos precio cambiado
    if (!empty($datos['aux']['ctasprodpreciosdiff'])) {
        $buffer = '';           
        $cta = "";

        foreach ($datos['aux']['ctasprodpreciosdiff'] as $idx => $valor) {
            if ($cta !== $datos['aux']['ctasprodpreciosdiff'][$idx]['ID_cuenta']) {
                $cta = $datos['aux']['ctasprodpreciosdiff'][$idx]['ID_cuenta'];
                
                $buffer = '<table class="blueTable">'
                        . '<thead><tr><td>Cuenta</td><td>Mesa</td><td>Mesero</td><td>Fecha Hora</td></tr></thead>' 
                        . '<tbody><tr><td>' . $datos['aux']['ctasprodpreciosdiff'][$idx]['ID_cuenta'] . '</td> '
                        . '<td> ' . $datos['aux']['ctasprodpreciosdiff'][$idx]['ID_mesa'] . '</td> '
                        . '<td> '  . $datos['aux']['ctasprodpreciosdiff'][$idx]['ID_mesero'] . '-' 
                                . $datos['aux']['ctasprodpreciosdiff'][$idx]['usuario'] . '</td> '
                        . '<td> ' .  $datos['aux']['ctasprodpreciosdiff'][$idx]['fechahora_pedido'] . '</p> </tbody></table> ';
                
                $buffer .= '<table class="blueTable">'
                        . '<thead><tr><td>Productos</td><td>Fecha Hora Pedido</td><td>Fecha Hora Eliminado:</td>'
                        . '<td>Precio Original $</td><td>Precio Gravado $</td><td>Comentario:</td></thead>' ;
            }
            
            $buffer1 = '';
            if ($datos['aux']['ctasprodpreciosdiff'][$idx]['precio_grabado'] < $datos['aux']['ctasprodpreciosdiff'][$idx]['precio_original']) {
                $buffer1 = ' style=" color:#ff0000;" ';                            
            }
            
            $buffer .= '<td>  ' . $datos['aux']['ctasprodpreciosdiff'][$idx]['nombre'] . '</td> '
                    . '<td>  ' . $datos['aux']['ctasprodpreciosdiff'][$idx]['fechahora_pedido'] . '</td>  '
                    . '<td>  ' . $datos['aux']['ctasprodpreciosdiff'][$idx]['fechahora'] . '</td>  '
                    . '<td> ' . $datos['aux']['ctasprodpreciosdiff'][$idx]['precio_original'] . '</td> '
                    . '<td'. $buffer1 . '>' . $datos['aux']['ctasprodpreciosdiff'][$idx]['precio_grabado'] . '</td> '
                    . '<td> ' . $datos['aux']['ctasprodpreciosdiff'][$idx]['nota'] . '</td>';
        }
        $buffer .= '</tbody></table>';
        estadisticas_agregar_panel('Cuentas con Productos precio cambiado', $buffer);
    } else {
        estadisticas_agregar_info('Sin datos de Cuentas con  Productos precio cambiado');
    }

    global $stat_html;
    return $stat_html;
}
