﻿<?php
class rsv {
    static function integrar()
    {
        // Eliminamos las cuentas que no tengan mas pedidos.
        // Esto se produce sobre todo al fusionar mesas
        // pero lo haremos siempre por las veces que muevan articulo por articulo hasta
        // vaciar una cuenta, dejandola moribunda
        $c = 'DELETE FROM cuentas WHERE ID_cuenta NOT IN (SELECT ID_cuenta FROM pedidos)';
        db_consultar($c);

        // Eliminamos los pedidos que no tienen cuenta, pues estos nunca seran visibles
        // y tienen que ser un error definitivamente, mejor que "desaparezcan" y no creen
        // problemas mayores

        $c = 'DELETE FROM pedidos WHERE ID_cuenta  NOT IN (SELECT ID_cuenta FROM cuentas)';
        db_consultar($c);

        // Tenemos que...
        CacheDestruir();
    }

    static function cuenta_de_mesa($MESA) {
        $c = 'SELECT `ID_cuenta` FROM `cuentas` WHERE flag_pagado=0 AND flag_anulado=0 AND ID_mesa="'.db_codex($MESA).'" ORDER BY ID_cuenta DESC LIMIT 1';
        $r = db_consultar($c);

        if (db_num_resultados($r) > 0)
        {
            $cuenta = db_fetch($r);
            return $cuenta['ID_cuenta'];
        } else {
            return false;
        }
    }

    static function cuenta_de_mesa_a_datos($MESA) {
        $c = 'SELECT * FROM `cuentas` WHERE flag_pagado=0 AND flag_anulado=0 AND ID_mesa="'.db_codex($MESA).'" ORDER BY ID_cuenta DESC LIMIT 1';
        $r = db_consultar($c);

        if (db_num_resultados($r) > 0)
        {
            return db_fetch($r);
        } else {
            return false;
        }
    }

    static function cuenta_nueva($MESA, $MESERO, $COMANDA) {
        $CUENTA['ID_mesa'] = $MESA;
        $CUENTA['ID_mesero'] = $MESERO;
        $CUENTA['ID_usuario'] = 0;
        $CUENTA['ID_comanda'] = $COMANDA;
        return db_agregar_datos('cuentas', $CUENTA);
    }

    static function cuenta_actualizar( $CUENTA, $COMANDA, $MESERO) {
        $CUENTAD['ID_comanda'] = $COMANDA;
        $CUENTAD['ID_mesero'] = $MESERO;
        return db_actualizar_datos('cuentas', $CUENTAD," id_cuenta='" . $CUENTA . "'");
    }
    
    static function cuenta_duplicar_mesa($MESA) {
        $CUENTA_BASE = rsv::cuenta_de_mesa_a_datos($MESA);
        $CUENTA['ID_mesa'] = $MESA;
        $CUENTA['ID_mesero'] = $CUENTA_BASE['ID_mesero'];
        $CUENTA['ID_usuario'] = $CUENTA_BASE['ID_usuario'];;
        CacheDestruir();

        return db_agregar_datos('cuentas', $CUENTA);
    }

    static function cuenta_anexar_domicilio($CUENTA, $DOMICILIO)
    {
        $c = 'UPDATE cuentas SET ID_domicilio="'.$DOMICILIO.'" WHERE ID_cuenta="'.$CUENTA.'"';
        db_consultar($c);
        CacheDestruir();
    }
    
    static function cuenta_propina($CUENTA,$VALOR)
    {
        $c = 'UPDATE cuentas SET `flag_nopropina`= "'.$VALOR.'" WHERE ID_cuenta = "'.$CUENTA.'"';
        db_consultar($c);
        CacheDestruir();
    }

    static function validar_comanda($comanda) {
        $c = 'SELECT ID_cuentas FROM `cuentas` WHERE ID_comanda="'.db_codex($comanda).'" ';
        $r = db_consultar($c);

        if (db_num_resultados($r) > 0)
        {
            return true;
        } else {
            return false;
        }
    }
    
    // Retorna el uniqid() del grupo creado.
    static function ingresar_pedidos($CUENTA, $PRODUCTOS, $OPCIONES = array()){

        $DATOS_COMUNES['ID_cuenta'] = $CUENTA;
        $DATOS_COMUNES['grupo'] = uniqid();
        $DATOS_COMUNES['fechahora_pedido'] = mysql_datetime();

        foreach($PRODUCTOS as $tmpID => $PRODUCTO)
        {        

            // busquemos el producto.
            $PRODUCTO_BASE = db_obtener_fila('productos', 'ID_producto = '. db_codex($PRODUCTO['ID']));

            // Si no se encontró abortar ESTE producto
            if ($PRODUCTO_BASE === FALSE)
            {
                continue;
            }

            unset($BUFFER_PEDIDO);

            $BUFFER_PEDIDO['tmpID'] = $tmpID;
            $BUFFER_PEDIDO['ID_producto'] = $PRODUCTO['ID'];
            $BUFFER_PEDIDO['precio_original'] = $PRODUCTO['precio'];
            $BUFFER_PEDIDO['precio_grabado'] = $PRODUCTO['precio'];
            $BUFFER_PEDIDO['nodo'] = $PRODUCTO_BASE['nodo_sugerido'];
            $BUFFER_PEDIDO['prioridad'] = $PRODUCTO_BASE['prioridad'];

            if ( $PRODUCTO_BASE['autodespacho'] === '1' )
            {
                $BUFFER_PEDIDO['flag_elaborado'] = '1';
                $BUFFER_PEDIDO['flag_despachado'] = '1';
            }
            
            if ( isset($OPCIONES['pausar']) )
            {
                $BUFFER_PEDIDO['flag_pausa'] = '1';
                $BUFFER_PEDIDO['fechahora_activacion'] = $OPCIONES['pausar'];
            }

            $ID_pedido = db_agregar_datos('pedidos', array_merge($DATOS_COMUNES, $BUFFER_PEDIDO));

            if ($ID_pedido > 0)
            {

                if (isset($PRODUCTO['adicionales']) && is_array($PRODUCTO['adicionales']) && count($PRODUCTO['adicionales']) > 0 )
                {
                    foreach ($PRODUCTO['adicionales'] as $adicional)
                    {
                        $precio_grabado = db_obtener('adicionables','precio','ID_adicional = '.$adicional);
                        db_agregar_datos ('pedidos_adicionales',array('ID_pedido' => $ID_pedido, 'ID_adicional' => $adicional, 'precio_grabado' => $precio_grabado, 'precio_original' => $precio_grabado, 'tipo' => 'poner'));
                    }
                }

                if ( isset($PRODUCTO['ingredientes']) && is_array($PRODUCTO['ingredientes']) && count($PRODUCTO['ingredientes']) > 0 )
                {
                    foreach ($PRODUCTO['ingredientes'] as $adicional)
                    {
                        $precio_grabado = db_obtener('adicionables','precio','ID_adicional = '.$adicional);
                        db_agregar_datos ('pedidos_adicionales',array('ID_pedido' => $ID_pedido, 'ID_adicional' => $adicional, 'precio_grabado' => $precio_grabado, 'precio_original' => $precio_grabado, 'tipo' => 'quitar'));
                    }
                }
             }
        } // foreach($PEDIDOS as $tmpID => $pedido)

        // Vaciamos cache porque hemos cambiado la vista
        CacheDestruir();
        
        return $DATOS_COMUNES['grupo'];
    }
    
    static function domicilio_crear_registro($DOMICILIO)
    {
        $DOMICILIO['telefono'] = db_codex(preg_replace('/[^\d]/','',trim($DOMICILIO['telefono'])));
        
        $DATOS['telefono'] = $DOMICILIO['telefono'];
        $DATOS['nombre'] = $DOMICILIO['nombre'];
        $DATOS['direccion'] = $DOMICILIO['direccion'];
        $DATOS['tarjeta'] = $DOMICILIO['tarjeta'];
        $DATOS['expiracion'] = $DOMICILIO['expiracion'];
        $DATOS['vuelto'] = $DOMICILIO['vuelto'];
        $DATOS['notas'] = $DOMICILIO['notas'];
        $DATOS['metodo_pago'] = $DOMICILIO['metodo_pago'];
        $DATOS['documento_fiscal'] = $DOMICILIO['documento_fiscal'];
        $DATOS['detalle_facturacion'] = $DOMICILIO['detalle_facturacion'];
        $DATOS['facturacion_nombre'] = $DOMICILIO['facturacion_nombre'];
        $DATOS['facturacion__dui'] = $DOMICILIO['facturacion__dui'];
        $DATOS['facturacion_nit'] = $DOMICILIO['facturacion_nit'];
        $DATOS['facturacion_nrc'] = $DOMICILIO['facturacion_nrc'];
        $DATOS['facturacion_giro'] = $DOMICILIO['facturacion_giro'];
        $DATOS['facturacion_direccion'] = $DOMICILIO['facturacion_direccion'];
        
        $ID_domicilio = db_agregar_datos('domicilio', $DATOS);
        
        return $ID_domicilio;
    }

    static function obtener_informacion_completa($CUENTA, $OPCIONES = array()) {
        
        $buffer_total = 0;
        
        $CUENTA = db_codex($CUENTA);
        $c = 'SELECT `ID_cuenta`, `ID_domicilio`, `ID_mesa`, `flag_pagado`, '
                . '`flag_nopropina`, `flag_exento`, `flag_tiquetado`, `flag_anulado`, '
                . '`metodo_pago`, `ID_mesero`, `ID_usuario`, `fechahora_pagado`, '
                . '`fechahora_anulado`, `ID_comanda` FROM `cuentas` WHERE ID_cuenta="'.$CUENTA.'"';
        $rCuenta = db_consultar($c);
        
        if (db_num_resultados($rCuenta) === 0)
        {
            // No se encontró la cuenta
            return false;
        }
        
        $fCuenta = db_fetch($rCuenta);
        
        if ($fCuenta['ID_domicilio'] != '0')
        {
            $c = 'SELECT `ID_domicilio`, `telefono`, `direccion`, `nombre`, `tarjeta`, `expiracion`, `vuelto`, `notas`, `metodo_pago`, `documento_fiscal`, `detalle_facturacion`, `facturacion_nombre`, `facturacion__dui`, `facturacion_nit`, `facturacion_nrc`, `facturacion_giro`, `facturacion_direccion` FROM `domicilio` WHERE ID_domicilio="'.$fCuenta['ID_domicilio'].'"';
            $rDomicilio = db_consultar($c);
            
            if (db_num_resultados($rDomicilio) > 0)
            {
                $fCuenta['domicilio'] = db_fetch($rDomicilio);
            }
        }
        
        mysqli_free_result($rCuenta);

        $FILTRO = '';
        
        if (!empty($OPCIONES['FILTRAR_GRUPO']))
        {
            $FILTRO = sprintf('AND grupo = "%s"',$OPCIONES['FILTRAR_GRUPO']);
        }
            
        $c = 'SELECT t1.`ID_pedido`, t1.`ID_producto`, t2.`nombre`, t1.`precio_grabado`, t1.`precio_original`, t1.`flag_cancelado`, t1.`tmpID`, t1.`fechahora_pedido`, t1.`fechahora_elaborado`, `fechahora_despachado`, `fechahora_activacion`, t1.`flag_elaborado`, t1.`flag_despachado`, t1.`flag_pausa`, t1.`prioridad`, t1.`nodo`, t1.`grupo`, t1.`ID_cuenta`, t2.`ID_grupo` FROM `pedidos` AS t1 LEFT JOIN `productos` AS t2 USING(ID_producto) WHERE ID_cuenta="'.$CUENTA.'" '.$FILTRO.' ORDER BY t1.`tmpID`';
        
        $rPedidos = db_consultar($c);
        
        $pedidos = array();
        
        while ( $fPedido = db_fetch($rPedidos) )
        {   

            if ($fPedido['flag_cancelado'] === '0')
                $buffer_total += $fPedido['precio_grabado'];
            
            $pedidos[$fPedido['ID_pedido']] = $fPedido;
             
            $c = 'SELECT t1.ID_pedido_adicional, t2.nombre, t1.precio_grabado, t1.tipo FROM `pedidos_adicionales` AS t1 LEFT JOIN `adicionables` AS t2 USING(ID_adicional) WHERE ID_pedido="'.$fPedido['ID_pedido'].'" AND tipo="poner"';
            $rAdicionales = db_consultar($c);

            while ($rAdicionales && $fAdicionales = db_fetch($rAdicionales))
            {
                $pedidos[$fPedido['ID_pedido']]['adicionales'][] = $fAdicionales;
                
                if ($fPedido['flag_cancelado'] === '0')
                    $buffer_total += $fAdicionales['precio_grabado'];
            }

            $c = 'SELECT t2.nombre, t1.precio_grabado, t1.tipo FROM `pedidos_adicionales` AS t1 LEFT JOIN `adicionables` AS t2 USING(ID_adicional) WHERE ID_pedido="'.$fPedido['ID_pedido']. '" AND tipo="quitar"';
            $rRemociones = db_consultar($c);

            while ($rRemociones && $fRemociones = db_fetch($rRemociones))
            {
                $pedidos[$fPedido['ID_pedido']]['remociones'][] = $fRemociones;
            }

        } // fetch de pedidos
        
        $totales['total_con_iva_y_propina'] = $buffer_total;
        $totales['subtotal'] = $buffer_total;
                
        if ($fCuenta['flag_exento'] == '1')
        {
            // Es exento de IVA: SI
            $totales['total_con_iva_y_propina'] /= 1.13;
            $totales['subtotal'] /= 1.13;
        }
        
        if ($fCuenta['flag_nopropina'] == '0')
        {
            // Tiene propina: SI
            $totales['total_con_iva_y_propina'] += numero(($buffer_total * 1.10) - $buffer_total);
        }
        
        
        $totales['subtotal'] = numero($totales['subtotal']);
        $totales['total_con_iva_y_propina'] = numero($totales['total_con_iva_y_propina']);
        $totales['iva'] = numero($buffer_total - ($buffer_total / 1.13));
        $totales['propina'] = numero(($buffer_total * 1.10) - $buffer_total);
        
        mysqli_free_result($rPedidos);
        
        
        // Mesero
        $fCuenta['mesero'] = db_obtener_fila('usuarios', sprintf('ID_usuarios="%s"',$fCuenta['ID_mesero']));
        
        
        // Grupos
        $c = 'SELECT `ID_grupo`, `descripcion` FROM `productos_grupos` WHERE 1';
        $rGrupos = db_consultar($c);
        
        while ($fGrupo = db_fetch($rGrupos))
        {
            $fCuenta['grupos'][$fGrupo['ID_grupo']] = $fGrupo['descripcion'];
        }
        
        mysqli_free_result($rGrupos);
        
        //Inclusion Descuentos en Tiquetes
        //Abril 2015
        //Agregando al Arreglo de Totales
        $c = 'SELECT SUM( ROUND(cantidad,2) ) AS total_descuento FROM `cuenta_descuento` AS t1 LEFT JOIN `cuentas` AS t2 USING (ID_cuenta) WHERE ID_cuenta = "'.$CUENTA.'"';
        $r = db_consultar($c);
        $f = db_fetch($r);
        $totales['total_descuento'] = ( empty($f['total_descuento']) ? '0.00' : $f['total_descuento'] );        
        
        
        return array($fCuenta, $pedidos, $totales);
    }
    
    static function generar_impresion_domicilio($CUENTA) {

        list($cuenta, $pedidos, $totales) = rsv::obtener_informacion_completa($CUENTA);
        
        if ($cuenta['ID_domicilio'] == '0') return;
        
        $impresion = '<h1 style="text-align:center;">'.NOMBRE_RESTAURANTE.'</h1>';
        $impresion .= '<p>Ref: '.str_pad($cuenta['ID_cuenta'], 6, '0', STR_PAD_LEFT).'</p>';
        $impresion .= '<h2>'.mysql_datetime().'<h2>';
        $impresion .= '<p>Teléfono: ' . $cuenta['domicilio']['telefono'] . '</p>';
        $impresion .= '<p>Dirección: ' . $cuenta['domicilio']['direccion'] . '</p>';
        $impresion .= '<p>Nombre: ' . $cuenta['domicilio']['nombre'] . '</p>';
        $impresion .= '<p>Método de pago: ' . $cuenta['domicilio']['metodo_pago'] . '</p>';
        $impresion .= '<p>Facturación: ' . $cuenta['domicilio']['documento_fiscal'] . '</p>';
        $impresion .= '<p>Facturar a: ' . $totales['facturacion_nombre'] . '</p>';
        $impresion .= '<p>DUI: ' . $totales['facturacion__dui'] . '</p>';
        $impresion .= '<p>NIT: ' . $totales['facturacion__nit'] . '</p>';
        $impresion .= '<p>NRC: ' . $totales['	facturacion_nrc'] . '</p>';
        $impresion .= '<p>NIT: ' . $totales['facturacion__nit'] . '</p>';
        $impresion .= '<p>Giro: ' . $totales['facturacion_giro'] . '</p>';
        $impresion .= '<p>Factura dirección: ' . $totales['facturacion_direccion'] . '</p>';
        
        
        $impresion .= '<p>Total: $' . $totales['total_con_iva_y_propina'] . '</p>';
        if ( $cuenta['domicilio']['metodo_pago'] == 'tarjeta' )
        {
            $impresion .= '<p>Tarjeta *' . substr($cuenta['domicilio']['tarjeta'], -4) . '</p>';
        } else {
            $impresion .= '<p>Cambio: $' . numero($cuenta['domicilio']['vuelto'] - $totales['total_con_iva_y_propina']). '</p>';
        }
        
        $impresion .= '<p>Notas: ' . ( empty($cuenta['domicilio']['notas']) ? 'Ninguna' : $cuenta['domicilio']['notas'] ). '</p>';
        
        $impresion .= '<br /><h2>PRODUCTOS ['.count($pedidos).']<h2>';
        
        $impresion .= '<table style="width:100%;">';
        
        foreach($pedidos as $pedido)
        {
            
            if ($pedido['flag_cancelado'] == '1')
                continue;
            
            $extras = '';
            if (isset($pedido['adicionales']))
            {
                foreach ($pedido['adicionales'] as $adicional)
                {
                    $extras .= '<tr><td style="font-size:10pt;"> + '.$adicional['nombre'] . '</td><td style="text-align:right;font-size:10pt;">$' . $adicional['precio_grabado'] .'</td></tr>';
                }
            }

            if (isset($pedido['remociones']))
            {
                foreach ($pedido['remociones'] as $adicional)
                {                    
                    $extras .= '<tr><td style="font-size:10pt;"> - '.$adicional['nombre'] . '</td><td style="text-align:right;font-size:10pt;">-</td></tr>';
                }
            }

            $impresion .= '<tr><td style="text-align:left;">' . $pedido['nombre'] . '</td><td style="text-align:right;vertical-align:top;">$'.$pedido['precio_grabado'].'</td></tr>';
            $impresion .= $extras;
        }
        $impresion .= '</table>';
        
        $impresion .= '<p>Total: $' . $totales['total_con_iva_y_propina'] . '</p>';        
        
        $buffer = '<div style="font-weight:normal;font-size:10pt;">'.$impresion.'</div>';
        
        db_agregar_datos('comandas', array('data' => $buffer, 'estacion' => 'domicilio'));

        
        if ( $cuenta['domicilio']['metodo_pago'] == 'tarjeta' )
        {
            $impresion .= '<br />_____________';
            $impresion .= '<br />Tarjeta cobrada: [__]';
            $impresion .= '<br /><br /><br /><br /><br />______________';
            $impresion .= '<p>Tarjeta: ' . $cuenta['domicilio']['tarjeta'] . '</p>';
            $impresion .= '<p>Expiración: ' . $cuenta['domicilio']['expiracion'] . '</p>';
        }
        
        $buffer = '<div style="font-weight:normal;font-size:10pt;">'.$impresion.'</div>';

        db_agregar_datos('comandas', array('data' => $buffer, 'estacion' => 'domicilio'));
    }
    
    static function domicilio_modificar($CUENTA, $CAMPO, $VALOR)
    {
        $c = 'UPDATE domicilio LEFT JOIN cuentas USING(ID_domicilio) SET fechahora_transito=NOW(), flag_en_transito=1 WHERE ID_cuenta = "'.db_codex($CUENTA).'"';
        db_consultar($c);
        CacheDestruir();
    }
    
    static function generar_impresionA_cortez()
    {
        $c = 'SELECT t1.ID_cuenta, t1.ID_mesa, t1.ID_mesero, t4.usuario, t1.fechahora_anulado,  '
            . '        t3.nombre, t2.precio_grabado, t2.precio_original, t2.fechahora_pedido '
            . 'FROM `cuentas` AS t1 LEFT JOIN `pedidos` AS t2 on t1.ID_cuenta=t2.ID_cuenta '
            . '        inner join productos as t3 on t2.id_producto=t3.id_producto '
            . '        inner join usuarios as t4 on t1.ID_mesero = t4.ID_usuarios '
            . 'WHERE fechahora_pedido >= COALESCE((SELECT fechatiempo FROM cortez ORDER BY fechatiempo DESC LIMIT 1),"0")  '
            . 'AND flag_anulado=1 '
            . 'ORDER BY t1.ID_cuenta DESC';
        $r = db_consultar($c);                        

        //if(!$r){
            $impresion = '<h1 style="text-align:center;"> Anulaciones del Corte Actual</h1>' ;
            $cta="";
            
            while ($f = db_fetch($r))
            {            
                if($cta!==$f['ID_cuenta']){
                    $cta=$f['ID_cuenta'];
                
                    $impresion .= '<br><p><b>Cuenta:</b> ' . $f['ID_cuenta'] . '</p>';
                    $impresion .= '<p><b>Mesa:</b> ' . $f['ID_mesa'] . '</p>';
                    $impresion .= '<p><b>Mesero:</b> ' . $f['ID_mesero'] . '-' . $f['usuario'] . '</p>';
                    $impresion .= '<p><b>Fecha Hora Anulacion:</b> ' . $f['fechahora_anulado'] . '</p>';
                }                   

                $impresion .= '<p><b>Producto:</b> ' . $f['nombre'] . ' ';
                $impresion .= '<b>Fecha Hora Pedido:</b> ' . $f['fechahora_pedido'] . '</p>';
                $impresion .= '<p><b>Precio Original:</b> ' . $f['precio_original'] . ' ';
                $impresion .=  '<b>Precio Gravado:</b> ' . $f['precio_grabado'] . '</p> <br>';
                
            }
            db_agregar_datos('comandas', array('data' => $impresion, 'estacion' => 'cortez anulaciones'));
        //}    
    }
    static function generar_impresion_cortez($ID_cortez)
    {
        $c = 'SELECT `ID_cortez`, `fechahora_recibido`, `fechahora_remesado`, `total_a_cuadrar`, '
                . '`total_diferencia`, `total_efectivo`, `total_pos`, `total_compras`, `total_comprasG`, `total_comprasO`, `total_caja`, '
                . '`inventario`, `ID_usuario`, `fechatiempo`, `estado`, `remesa` FROM `cortez` '
                . 'WHERE ID_cortez =  "'.$ID_cortez.'" LIMIT 1';
        $r = db_consultar($c);
        
        $f = mysqli_fetch_array($r);

        $impresion = '<h1 style="text-align:center;">'.NOMBRE_RESTAURANTE.' - Corte Z</h1>';
        $impresion .= '<p>Fecha: ' . $f['fechatiempo'] . '</p>';
        $impresion .= '<hr />';
        $impresion .= '<p>Total a cuadrar: $' . $f['total_a_cuadrar'] . '</p>';
        $impresion .= '<p>Total diferencia: ' . $f['total_diferencia'] . '</p>';
        $impresion .= '<p>Total efectivo: ' . $f['total_efectivo'] . '</p>';
        $impresion .= '<p>Total POS: ' . $f['total_pos'] . '</p>';
        $impresion .= '<p>Total compras: ' . $f['total_compras'] . '</p>';
        $impresion .= '<p>Total comprasG: ' . $f['total_comprasG'] . '</p>';
        $impresion .= '<p>Total comprasO: ' . $f['total_comprasO'] . '</p>';
        $impresion .= '<p>Total efectivo: ' . $f['total_efectivo'] . '</p>';
        $impresion .= '<hr />';
        $impresion .= '<p>F._______________</p>';

        db_agregar_datos('comandas', array('data' => $impresion, 'estacion' => 'cortez'));                    
    }
        
    static function generar_impresion_orden_trabajo($CUENTA, $GRUPO = '') {

        list($cuenta, $pedidos, $totales) = rsv::obtener_informacion_completa($CUENTA, array('FILTRAR_GRUPO' => $GRUPO));
        
        $impresion ="";
        $grupos = array();        
        
        foreach($pedidos as $pedido)
        {
            
            if ($pedido['flag_cancelado'] == '1')
                continue;
            
            $buffer_pedido = '';
            $extras = '';
            
            if (isset($pedido['adicionales']))
            {
                foreach ($pedido['adicionales'] as $adicional)
                {
                    $extras .= '&nbsp;+ '.$adicional['nombre'] . '<br />';
                }
            }

            if (isset($pedido['remociones']))
            {
                foreach ($pedido['remociones'] as $adicional)
                {                    
                    $extras .= '&nbsp;- '.$adicional['nombre'] . '<br />';
                }
            }
            
            $buffer_pedido =  $pedido['nombre'] . '<br />';
            
            $grupos[$pedido['ID_grupo']][] = $buffer_pedido.$extras;
            
        }
        
        $impresion = '<h3>ORDEN DE TRABAJO</h3>';
        $impresion .= '<h3 style="text-align:center;">'.NOMBRE_RESTAURANTE.'</h3>';
        $impresion .= '<br /><br />';

        $impresion .= 'Mesa: ' . $cuenta['ID_mesa'] . '  - Cta #'.$cuenta['ID_cuenta']. '  - Cmda #'.$cuenta['ID_comanda'].'<br />';
        $impresion .= 'Mesero: '.$cuenta['mesero']['usuario'] . '<br />';
        
//        $impresion .= '<br />';
//// JOMR ini        
         $i=1;
         if ( ( isset($grupos) && is_array($grupos))){
            $buffer = $impresion;
            
            foreach ($grupos as $i => $grupo)
            {                    
               //$buffer .= var_dump($grupos);
                       
               $buffer .= '<br /><div style="text-align:center;font-size:10pt;">'.$cuenta['grupos'][$i].'</div>';
                $unicos = array_count_values($grupo);
                foreach ($unicos as $nombre => $cantidad)
                {
                    $buffer .= $cantidad . ' x '.$nombre;
                    
                }     
                                     
                $i++;
            }
            $buffer = '<br /><div style="font-size:10pt;">'.$buffer.'</div>'; 
            db_agregar_datos('comandas', array('data' => $buffer, 'estacion' => 'comandas'));
         }
//// JOMR end        
        
        /*
        if ( ( isset($grupos[1]) && is_array($grupos[1]) ) || ( isset($grupos[2]) && is_array($grupos[2]) ) )
        {
            $buffer = $impresion;
            
            if ( isset($grupos[1]) && is_array($grupos[1]) )
            {
                $buffer .= '<br /><div style="text-align:center;font-size:10pt;">'.$cuenta['grupos'][1].'</div>';
                $unicos = array_count_values($grupos[1]);
                foreach ($unicos as $nombre => $cantidad)
                {
                    $buffer .= $cantidad . ' x '.$nombre;
                }
            }
            
            if ( isset($grupos[2]) && is_array($grupos[2]) )
            {
                $buffer .= '<br /><div style="text-align:center;font-size:10pt;">'.$cuenta['grupos'][2].'</div>';
                $unicos = array_count_values($grupos[2]);
                foreach ($unicos as $nombre => $cantidad)
                {
                    $buffer .= $cantidad . ' x '.$nombre;
                }
            }
            
            $buffer = '<br /><div style="font-size:10pt;">'.$buffer.'</div>';
            
            db_agregar_datos('comandas', array('data' => $buffer, 'estacion' => 'comandas'));
        }

        // Grupos 3 y 4
        if ( ( isset($grupos[3]) && is_array($grupos[3]) ) || ( isset($grupos[4]) && is_array($grupos[4]) ) )
        {
            $buffer = $impresion;
            
            if ( isset($grupos[3]) && is_array($grupos[3]) )
            {
                $buffer .= '<br /><div style="text-align:center;font-size:10pt;">'.$cuenta['grupos'][3].'</div>';
                $unicos = array_count_values($grupos[3]);
                foreach ($unicos as $nombre => $cantidad)
                {
                    $buffer .= $cantidad . ' x '.$nombre;
                }
            }
            
            if (isset($grupos[4]) && is_array($grupos[4]) )
            {
                $buffer .= '<br /><div style="text-align:center;font-size:10pt;">'.$cuenta['grupos'][4].'</div>';
                $unicos = array_count_values($grupos[4]);
                foreach ($unicos as $nombre => $cantidad)
                {
                    $buffer .= $cantidad . ' x '.$nombre;
                }
            }
            
            $buffer = '<br /><div style="font-size:10pt;">'.$buffer.'</div>';
            
            db_agregar_datos('comandas', array('data' => $buffer, 'estacion' => 'comandas'));
        }
        */
    } // generar_impresion_orden_trabajo
    
    static function generar_impresion_tiquete($CUENTA, $CONTEXTO, $ESTACION) {
        
        list($cuenta, $pedidos, $totales) = rsv::obtener_informacion_completa($CUENTA);
        
        // Actualizamos el estado de tiquetado
        $c = 'UPDATE `cuentas` SET `flag_tiquetado`=1 WHERE `ID_cuenta`="'.$CUENTA.'"';
        db_consultar($c);
        // ------------
        
        $grupos = array();

        $impresion = '<div class="orden">';

        $impresion .= '<p style="font-weight:bold;text-align:center;">'.SUCURSAL_EMPRESA.'</p>';
        $impresion .= '<p style="text-align:center;">'.NOMBRE_RESTAURANTE.'</p>';
        $impresion .= '<p style="text-align:center;">Tel.:<br />'.SUCURSAL_TELEFONO.'</p>';

        $impresion .= '<br /><div style="height:1.5em;text-align:center;">'
                . '<span class="grupo" style="height:1.5em;text-align:center;font-size: 5px; font-weight:bold;">'
                . 'Mesa #'.$cuenta['ID_mesa']
                . '  - Cta #'.$cuenta['ID_cuenta']
                . '  - Cmda #'.$cuenta['ID_comanda'].
                '</span></div>';

        foreach($pedidos as $pedido)
        {
            
            if ($pedido['flag_cancelado'] == '1')
                continue;
            
            $buffer_pedido = '';
            $extras = '';
            
            if (isset($pedido['adicionales']))
            {
                $extras .= '<div class="adicionales" ><ul style="padding:2px;">';
                
                foreach ($pedido['adicionales'] as $adicional)
                {
                    $extras .= '<li>+ '.substr($adicional['nombre'], 0, 13) . ' <div style="float:right;z-index:99;">$' . numero($adicional['precio_grabado']) . '</div>' . '</li>';
                }
                
                $extras .= '</ul></div>';
            }
         
            if (defined('TIQUETE_AGRUPADO') && TIQUETE_AGRUPADO)
            {
                $buffer_pedido = '<div class="pedido" style="padding:0px;margin:0px;">';
                    $buffer_pedido .= '<div class="producto" style="padding:0px;margin:0px;">';
                        $buffer_pedido .= '{{cantidad}} x ' . substr($pedido['nombre'], 0, 15);
                        $buffer_pedido .= ' <div style="z-index:99;float:right;">$' . numero($pedido['precio_grabado']) . '</div>';
                    $buffer_pedido .= '</div>'; // .producto
                    $buffer_pedido .= $extras;
                $buffer_pedido .= '</div>'; // .pedido

                $grupos[$pedido['ID_grupo']][] = $buffer_pedido;
            } else {
                $impresion .= '<div class="pedido" style="padding:0px;margin:0px;">';
                    $impresion .= '<div class="producto" style="padding:0px;margin:0px;">';
                        $impresion .= substr($pedido['nombre'], 0, 15);
                        $impresion .= ' <div style="z-index:99;float:right;">$' . numero($pedido['precio_grabado']) . '</div>';
                    $impresion .= '</div>'; // .producto
                    $impresion .= $extras;
                $impresion .= '</div>'; // .pedido
            }
            
        }
        
        $impresion .= '<br />';
        
        
        if (defined('TIQUETE_AGRUPADO') && TIQUETE_AGRUPADO) {
         
            ksort($grupos);

            foreach($grupos as $indice => $grupo)
            {
                $impresion .= '<br /><div style="text-align:center;font-size:12pt;">'.$cuenta['grupos'][$indice].'</div>';
                $unicos = array_count_values($grupo);
                foreach ($unicos as $producto => $cantidad)
                {
                    $impresion .= str_replace('{{cantidad}}', $cantidad, $producto);
                }
            }
        }
        
        $impresion .= '<br />';
        
        $impresion .= '<table style="width:100%;" class="totales">';
            $impresion .= '<tr><td>SubTotal:</td><td>' . '$' . str_pad($totales['subtotal'], 6, ' ', STR_PAD_LEFT) . '</td></tr>';
            if ( $cuenta['flag_exento'] == '1' )
                $impresion .= '<tr><td>IVA</td><td>EXENTO</td></tr>';

            if ( $cuenta['flag_nopropina'] == '0' )
                $impresion .= '<tr><td>Propina (10%):</td><td>' . '$' . str_pad($totales['propina'], 6, ' ', STR_PAD_LEFT) . '</td></tr>';
           
        //Inclusion Descuentos en Tiquetes
        //Abril 2015
        //Agregando a la impresion del Tiquete
            if ( $totales['total_descuento'] != '0' )
                $impresion .= '<tr><td>Descuento: :</td><td>' . '($' . str_pad($totales['total_descuento'], 6, ' ', STR_PAD_LEFT) . ') </td></tr>';
                
            $impresion .= '<tr><td>Total:</td><td>' . '$' . str_pad($totales['total_con_iva_y_propina'], 6, ' ', STR_PAD_LEFT) . '</td></tr>';
            
        $impresion .= '</table>'; // Fin tabla de totales
        
        $impresion .= '<br /><br /><br /><br /><p style="text-align:center;">'.SUCURSAL_DIRECCION.'<br />¡Gracias por su compra!<br /><br />' . date('Y/m/d H:i:s') . '</p>';
        
        db_agregar_datos('comandas', array('data' => $impresion, 'estacion' => $ESTACION));
        

        // HISTORIAL
        $DATOS['grupo'] = 'ORDENES';
        $DATOS['accion'] = 'TIQUETE';
        $DATOS['nota'] = $CONTEXTO;
        $DATOS['fechahora'] = mysql_datetime();
        $DATOS['ID_cuenta'] = $CUENTA;
        db_agregar_datos('historial',$DATOS);
        // ----------
        
        CacheDestruir();

    } // generar_impresion_tiquete
    
    static function Statsemail($date){
        $message="";
        
        if(isset($date)){
            $_POST['periodo_inicio']=db_codex($date) . ' 00:00:00';
            $_POST['periodo_final']=db_codex($date) . ' 23:59:59'; 
        }else{
            $_POST['periodo_inicio']=date('Y-m-d 00:00:00');
            $_POST['periodo_final']=date('Y-m-d 23:59:59');            
        }
                        
            require_once('TPL/estadisticas.tpl.php');
                        
            $message=  str_replace('accordionpanel','', $json['html'] );

            // "        <meta charset='utf-8'> " .
            
            $html="<!DOCTYPE html> " .
            "<html lang='en'> " .
            "    <head> " .
                    "<STYLE type='text/css'>" . file_get_contents("../STAT/CSS/estilo.css") . "</STYLE>" .        
            "        <meta http-equiv='X-UA-Compatible' content='IE=edge'> " .
            "        <meta name='viewport' content='width=device-width, initial-scale=1'> " . $message . "  </head> </html>";

            //$message=str_replace("html9999", $message , $html);
            file_put_contents("C:\\xampp\htdocs\\pos\\backups\\backupstest.html", $html );

            require 'class.phpmailer.php';
            require 'class.smtp.php';

            $mail             = new PHPMailer();
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->Host       = "ssl://smtp.gmail.com"; // SMTP server
            //$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)

            // 1 = errors and messages
            // 2 = messages only
            $mail->SMTPAuth   = true;                  // enable SMTP authentication
            //$mail->SMTPSecure = "tls";                 // sets the prefix to the servier
            $mail->Host       = "ssl://smtp.gmail.com";      // sets GMAIL as the SMTP server
            $mail->Port       = 465;                   // set the SMTP port for the GMAIL server
            $mail->Username   = "juan.menjivar@gmail.com";  // GMAIL username
            $mail->Password   = "monica1998";            // GMAIL password

            $mail->SetFrom('kairoisaac@gmail.com', 'Las Tablitas Steak House');

            $mail->AddReplyTo("kairoisaac@gmail.com","Las Tablitas Steak House");
            $mail->Subject    = "Las Tablitas Steak House - POS Stat Report";
            $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
            $mail->MsgHTML($html);
             

            $mail->AddAddress("juan.menjivar@gmail.com", "Las Tablitas Steak House");
            $mail->AddAddress("trazosdigitalestd@gmail.com", "Las Tablitas Steak House");

            //$mail->AddAttachment("images/phpmailer.gif");      // attachment
            //$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

            $mail->Send();
 
    }
   
}