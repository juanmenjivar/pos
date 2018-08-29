<?php
setlocale                   (LC_ALL, 'es_SV.UTF-8');
date_default_timezone_set   ('America/El_Salvador');


define('NOMBRE_RESTAURANTE', 'Las Tablitas Steak House');
define('ID_SERVIDOR', 'Las Tablitas Steak House');
define('MODO_GLOBAL', 'NORMAL'); // MODOS: [ NORMAL | DOMICILIO ]
define('URI_SERVIDOR', '/pos/SERV'); // URI relativa o absoluta hacia el servidor
define('URI_AUT', '/pos/AUT'); // URI relativa o absoluta hacia el autorizador

define('SUCURSAL_EMPRESA','Plaza Leon'); // Nombre de empresa
define('SUCURSAL_DIRECCION', '10 Ave. Nte. Cc. Leon, #18. S.S.'); // direccion de la sucursal
define('SUCURSAL_TELEFONO', '(503) 2557-1258'); // Telefono de empresa


define('USAR_AUT', FALSE); // forzar autorización para CAJA y PEDIDOS

define('ID_CACHE', "RSV_SQL_" . crc32(ID_SERVIDOR . URI_SERVIDOR) );

define('db__host','localhost');
define('db__usuario','pos'); // Nombre de usuario de base de datos
define('db__clave','posadmin'); // Clave de base de datos
define('db__db','rsv'); // Base de datos

$__listado_nodos['todos'] = 'Todas las ordenes';
$__listado_nodos['comida'] = 'Comida';
$__listado_nodos['bebidas_ensaladas_postres_entradas'] = 'Bebidas, Ensaladas, Postres y Entradas';
/*$__listado_nodos['pizzas1'] = 'Pizzas 1 + entradas horneadas';
$__listado_nodos['pizzas2'] = 'Pizzas 2';
$__listado_nodos['pastas'] = 'Pastas';
$__listado_nodos['nada'] = 'Desactivar este nodo';*/

$__listado_nodos_sql['todos'] = 'AND 1';
$__listado_nodos_sql['comida'] = 'AND t1.nodo IN ("comida")';
$__listado_nodos_sql['bebidas_ensaladas_postres_entradas'] = 'AND t1.nodo IN ("bebidas","general")';

/*$__listado_nodos_sql['horno'] = 'AND t1.nodo IN ("pizzas",pizzas1","pizzas2","entradas_horno","pastas")';
$__listado_nodos_sql['pizzas1'] = 'AND t1.nodo IN ("pizzas","pizzas1","entradas_horno")';
$__listado_nodos_sql['pizzas2'] = 'AND t1.nodo IN ("pizzas2")';
$__listado_nodos_sql['pastas'] = 'AND t1.nodo IN ("pastas")';
$__listado_nodos_sql['pastas'] = 'AND t1.nodo IN ("pastas")';
$__listado_nodos_sql['nada'] = 'AND 0';
$__listado_nodos_sql['domicilio'] = 'domicilio';*/

// OPCIONES ESPECIALES
define('TIQUETE_AGRUPADO', true); // Agrupar los productos en tiquete
//define('CANCELAR_IMPRESION_DESPACHO', false); // No imprimir la orden al momento de despachar

define('GENERAR_IMPRESION_ORDEN_TRABAJO', true);

//$JSOPS[] = 'despacho_aun_sin_elaborar';
//$JSOPS[] = 'sin_clave';

// Servidores externos para pedidos pedientes
//$__servidor_externo_pp['LPDOMICILIO'] = 'http://serv.domicilio.lapizzeria.com.sv/';

?>