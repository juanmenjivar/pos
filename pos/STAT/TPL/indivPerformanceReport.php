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

// Si se definió un periodo, restringir las estadísticas a este periodo.
// la fecha debe ser DATE
if (isset($_POST['fecha'])) {
    $periodo_inicio = db_codex($_POST['fecha']) . ' 00:00:00';
    $periodo_final = db_codex($_POST['fecha']) . ' 23:59:00';    
}

// las fechas deben ser DATETIME.
if (isset($_POST['periodo_inicio']) && isset($_POST['periodo_final']))
{
    $periodo_inicio = db_codex($_POST['periodo_inicio']);
    $periodo_final = db_codex($_POST['periodo_final']);
}

if (isset($_POST['indivPerformanceReportUsr'])){
    $indivPerformanceReportUsr= db_codex($_POST['indivPerformanceReportUsr']);
}


$c = ' SELECT cue.ID_cuenta, cue.ID_mesero, IFNULL(usu.usuario, CONCAT("#",cue.ID_mesero) ) AS usuario, ' .
        'ped.precio_grabado, prod.nombre, COUNT(prod.nombre) as ProdSales,flag_nopropina ' .
    'FROM pedidos ped LEFT JOIN cuentas cue USING(ID_cuenta)  ' .
    '	LEFT JOIN usuarios usu ON cue.ID_mesero = usu.ID_usuarios  ' .
    '	INNER JOIN productos as prod ON ped.ID_producto = prod.ID_producto ' .
    'WHERE ped.fechahora_pedido BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'" AND ' .
    'cue.flag_anulado = 0 AND ped.flag_cancelado = 0 and cue.ID_mesero=' .$indivPerformanceReportUsr .
    ' GROUP BY prod.nombre ' .
    ' ORDER BY cue.ID_cuenta;';
        
/*
        $r = db_consultar($c);
while ($f = db_fetch($r))
{

}
*/

echo 'go it ' . $c;
?>
