<?php
require_once('../configuracion.php');

?>
<script type="text/javascript" src="JS/despacho.js"></script>
<audio id="beep">
    <source src="<?php echo URI_SERVIDOR; ?>/SND/beep.wav">
    <source src="<?php echo URI_SERVIDOR; ?>/SND/beep.mp3">
</audio>
<div id="pedidos">
    
</div>
<div style="position:fixed;bottom: 5px; right: 5px;">
    <form action="" method="post" autocomplete="off" id="f_mesa">
        <input type="text" id="mesa" autocomplete="off" value="" />
        <input type="submit" value="Cuenta" />
    </form>
</div>