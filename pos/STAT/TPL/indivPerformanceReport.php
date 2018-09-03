<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once('../../configuracion.php');
require_once('../../SERV/PHP/db.php');

$periodo_inicio = mysql_date(). ' 00:00:00';
$periodo_final = mysql_date(). ' 23:59:59';


// las fechas deben ser DATETIME.
if (isset($_GET['a1']) && isset($_GET['a2']))
{
    $periodo_inicio = db_codex($_GET['a1']);
    $periodo_final = db_codex($_GET['a2']);
    $periodo_inicio= str_replace("\'","",$periodo_inicio);
    $periodo_final= str_replace("\'","",$periodo_final);
}

if (isset($_GET['s1'])){
    $indivPerformanceReportUsr= db_codex($_GET['s1']);
    $indivPerformanceReportUsr= str_replace("\'","",$indivPerformanceReportUsr);
}

/*
 * 
 *
SELECT IF(cue.flag_nopropina = 0, 'Cuentas con propina', 'Cuentas sin propina')  as t, 	
	prod.nombre AS Producto, COUNT(prod.nombre) AS cantidad, 
	ROUND(SUM( ((COALESCE(ped.precio_grabado,0) + ( 
				SELECT COALESCE(SUM(t3.precio_grabado),0 ) FROM pedidos_adicionales AS t3 
					WHERE t3.tipo="poner" AND t3.ID_pedido=ped.ID_pedido )) / IF(cue.flag_exento = 0, 1, 1.13)) 
				* IF(cue.flag_nopropina = 0, 1.10, 1) ),2) AS total
FROM pedidos ped LEFT JOIN cuentas cue USING(ID_cuenta) 
	LEFT JOIN usuarios usu ON cue.ID_mesero = usu.ID_usuarios 
	INNER JOIN productos as prod ON ped.ID_producto = prod.ID_producto
WHERE ped.fechahora_pedido BETWEEN '2018-08-1 00:00:00' AND '2018-08-1 23:59:00' AND 
cue.flag_pagado=1 AND cue.flag_anulado = 0 AND ped.flag_cancelado = 0 AND cue.flag_nopropina=0
GROUP BY Producto
UNION 
SELECT IF(cue1.flag_nopropina = 0, 'Cuentas con propina', 'Cuentas sin propina') as t,  
	prod.nombre AS Producto, COUNT(prod.nombre) AS cantidad, 
	ROUND(SUM( ((COALESCE(ped.precio_grabado,0) + ( 
				SELECT COALESCE(SUM(t3.precio_grabado),0 ) FROM pedidos_adicionales AS t3 
					WHERE t3.tipo="poner" AND t3.ID_pedido=ped.ID_pedido )) / IF(cue1.flag_exento = 0, 1, 1.13)) 
				* IF(cue1.flag_nopropina = 0, 1.10, 1) ),2) AS total
FROM pedidos ped LEFT JOIN cuentas cue1 USING(ID_cuenta) 
	LEFT JOIN usuarios usu ON cue1.ID_mesero = usu.ID_usuarios 
	INNER JOIN productos as prod ON ped.ID_producto = prod.ID_producto
WHERE ped.fechahora_pedido BETWEEN '2018-08-1 00:00:00' AND '2018-08-1 23:59:00' AND 
cue1.flag_pagado=1 AND cue1.flag_anulado = 0 AND ped.flag_cancelado = 0 AND cue1.flag_nopropina=1
GROUP BY Producto
ORDER BY  cantidad DESC, total DESC;


SELECT IF(cue.flag_nopropina = 0, 'Cuentas con propina', 'Cuentas sin propina') as t,  
	prod.nombre AS Producto, COUNT(prod.nombre) AS cantidad, 
	ROUND(SUM( ((COALESCE(ped.precio_grabado,0) + ( 
				SELECT COALESCE(SUM(t3.precio_grabado),0 ) FROM pedidos_adicionales AS t3 
					WHERE t3.tipo="poner" AND t3.ID_pedido=ped.ID_pedido )) / IF(cue.flag_exento = 0, 1, 1.13)) 
				* IF(cue.flag_nopropina = 0, 1.10, 1) ),2) AS total
FROM pedidos ped LEFT JOIN cuentas cue USING(ID_cuenta) 
	LEFT JOIN usuarios usu ON cue.ID_mesero = usu.ID_usuarios 
	INNER JOIN productos as prod ON ped.ID_producto = prod.ID_producto
WHERE ped.fechahora_pedido BETWEEN '2018-08-1 00:00:00' AND '2018-08-1 23:59:00' AND 
cue.flag_pagado=1 AND cue.flag_anulado = 0 AND ped.flag_cancelado = 0 
GROUP BY cue.flag_nopropina,prod.nombre
ORDER BY cantidad DESC, total DESC; 
 * 
 * 
 */

$c="SELECT cue.fechahora_pagado , cue.ID_mesero, IFNULL(usu.usuario, CONCAT('#',cue.ID_mesero) ) AS usuario, " .
    "prod.nombre AS Producto, COUNT(prod.nombre) AS cantidad, " .
    "	ROUND(SUM( ((COALESCE(ped.precio_grabado,0) + ( SELECT COALESCE(SUM(t3.precio_grabado),0 ) FROM pedidos_adicionales AS t3 " .
    "	WHERE t3.tipo='poner' AND t3.ID_pedido=ped.ID_pedido )) / IF(cue.flag_exento = 0, 1, 1.13)) * IF(cue.flag_nopropina = 0, 1.10, 1) ),2) AS subtotal " .
    "FROM pedidos ped LEFT JOIN cuentas cue USING(ID_cuenta) " .
    "LEFT JOIN usuarios usu ON cue.ID_mesero = usu.ID_usuarios " .
    "	INNER JOIN productos as prod ON ped.ID_producto = prod.ID_producto " .
    "	WHERE ped.fechahora_pedido BETWEEN '" .$periodo_inicio. "' AND '".$periodo_final."' AND " .
    "cue.flag_pagado=1 AND cue.flag_anulado = 0 AND ped.flag_cancelado = 0  AND cue.ID_mesero='".$indivPerformanceReportUsr."' " .
    "GROUP BY prod.nombre " .
    "ORDER BY cue.fechahora_pagado ASC;";
        
/*
$r = db_consultar($c);
while ($f = db_fetch($r))
{

}
*/

echo 'go it: ' . $periodo_inicio .'**' . $periodo_final . '***' . $indivPerformanceReportUsr . "***" . $c;
?>
