<script type="text/javascript" src="JS/caja.js"></script>
<script type="text/javascript" src="JS/master.chef.js"></script>

<div id="dialog-password" title="Ingrese clave" style="display:none;">
    <table>
        <tr>
        
    <td>
    User: <input id="dialog-user-input" type ="text" /> 
    </td>
        </tr>
        <tr>
        <td>
    Pass: <input id="dialog-password-input" type ="password" />
        </td>
        </tr>
    </table>
</div>

<audio id="beep">
    <source src="<?php echo URI_SERVIDOR; ?>/SND/beep.wav">
    <source src="<?php echo URI_SERVIDOR; ?>/SND/beep.mp3">
</audio>
<?php $_html['titulo'] = 'Caja'; ?>
<div id="menu" style="z-index:555;padding-left:40px;height:32px;line-height:32px;background-color: grey; position:fixed;left:0px;right:0px;border:2px solid white;display:none;">
    <div>
        <!--readonly="readonly"-->
    <input type="date" style="width:150px;"  value="<?php echo date('Y-m-d'); ?>" id="fecha_caja" />&nbsp;
    <button class="btn" id="ver_historial">Historial</button>&nbsp;
    <button class="btn" id="ver_total">Corte Z</button>&nbsp;
    <button class="btn" id="compras">Compras</button>&nbsp;
    
    <button class="btn" id="email">Email</button>&nbsp;
    
    <button class="btn" id="historial_cortez" style="display:none;">H. Cortes</button>&nbsp;
    <button class="btn" id="cortes" style="display:none;">Cortes</button>&nbsp;
    </div>
</div>
<img src="IMG/gear.png" id="mostrar_opciones" style="background-color: grey;border:2px solid white;position:fixed;top:0px;left:0px;z-index:999;" />
<table style="width:98%;border-collapse:collapse;margin:auto;table-layout:fixed;">
<tr>
    <td id="pestana_pedido" style="vertical-align: top; border-right: 1px solid whitesmoke;padding-right: 5px;width:75%;">
        <h1>
            CUENTAS ABIERTAS<br />
            [
            &nbsp;Mesa: <input autocomplete="off" id="id_mesa" type="text" value="" style="width:3.5em;" />&nbsp;
            <button id="btn_rapido_cuenta_tiquete" class="btn">Tiquete</button>&nbsp;
            <button id="btn_rapido_cuenta_cerrar" class="btn">Cerrar</button>&nbsp;
            
            ]

        </h1><hr />
        <div id="pedidos"></div>
    </td>
    <td id="pestana_cocina" style="vertical-align: top; border-left: 1px solid whitesmoke;padding-left: 5px;width:25%;">
        <h1>ORDENES EN COCINA</h1><hr />
        <div id="cocina"></div>
    </td>
</tr>
</table>
<div id="menu2" style="background: grey;border-top: 2px solid whitesmoke;color: black;z-index: 99;position: fixed;bottom: 0;line-height: 20px;left: 0px;right: 0px;font-family: monospace;font-size:12px;display:none;">
    <b>[</b> <input type="checkbox" style="vertical-align: middle;" class="vaciar_cache_caja auto_guardar" id="ocultar_fechas" value="1" /><label for="ocultar_fechas">Ocultar horas</label> <b>]</b>&nbsp;
    <b>[</b> <input type="checkbox" style="vertical-align: middle;" class="vaciar_cache_caja auto_guardar" id="cuentas_compactas" value="1" /><label for="cuentas_compactas">Cuentas compactas</label> <b>]</b>&nbsp;
    <b>[</b> <input type="checkbox" style="vertical-align: middle;" class="auto_guardar" id="habilitar_facturin" value="1" /><label for="habilitar_facturin">Facturin Plus</label> <b>]</b>
    &nbsp;|&nbsp;C<span id="t_cuentas"></span>
    &nbsp;|&nbsp;P<span id="t_pendientes"></span>
    <br />
    <span title="Distrubición de Servicio Normalizado" style="font-weight:bold;">Distrubición de Servicio Normalizado(DSN)</span>: <span id="dsn"></span>
    <br />
    <span title="Tiempo Promedio de Servicio" style="font-weight:bold;">Tiempo Promedio de Servicio(TPS)</span>: <span id="tps"></span>
    &nbsp;|&nbsp;
    <span title="Tiempo Máximo de Servicio" style="font-weight:bold;">Tiempo Máximo de Servicio(TMS)</span>: <span id="tms"></span>
</div>