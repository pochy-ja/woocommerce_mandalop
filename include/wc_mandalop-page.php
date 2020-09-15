<div class="wrap">
    <main class="contenido-principal">
    
         <center>
            <h1>
                <span>Conexión al webservice de Mandalop</span>
            </h1>
            <span>
                <span>Comprueba si las credenciales de acceso proporcionadas son válidas para operar en el sistema. Además las guarda para que funcione el plugin. Mediante el correo se notifica cuando una orden no pudo ser grabada en el api de mandalop.</span> 
            </span>
        <?php $options_r = get_option('wcm_custom_option');?>
        <!-- formulario para requerir los parametros a consultar en el webservice -->
        <form action="<?php plugin_dir_path(__FILE__).'wc_mandalop-page.php' ?>" method="POST" class="agregar-tarea">
            <div class="campo" style="margin: 20px 0px;">
                <label for="tarea">Usuario:</label>
                <input type="text" placeholder="Usuario *" name="usuario" id="usuario" 
                value= "<?php if(isset($options_r)) {echo $options_r["usuario"];}?>" class="nombre-tarea" required> 
            </div>
            <div class="campo" style="margin-bottom: 20px;">
                <label for="tarea">Password:</label>
                <input type="text" placeholder="Password *" name="pass" class="nombre-tarea"
                value= "<?php if(isset($options_r)) {echo $options_r["pass"];}?>" required> 
            </div>
            <div class="campo" style="margin-bottom: 20px;">
                <label for="tarea">Enlace:</label>
                <input type="text" placeholder="URL del webservice *" name="url" style="width: 400px;" class="nombre-tarea"
                value= "<?php if(isset($options_r)) {echo $options_r["url"];}?>" required> 
            </div>
            <div class="campo" style="margin-bottom: 20px;">
                <label for="tarea">Correo:</label>
                <input type="email" placeholder="Correo a notificar *" name="correo" class="nombre-tarea"
                value= "<?php if(isset($options_r)) {echo $options_r["correo"];}?>" required> 
            </div>
            <div class="campo enviar">
                <input type="submit" name="submit" class="boton nueva-tarea" value="Guardar">
            </div>
        </form>
        </center>
        <?php
//requerimos la libreria nusoap para manejo de webservice con php
require_once(plugin_dir_path(__FILE__).'nusoap.php');
	if(isset($_POST["submit"]) && !empty($_POST["submit"])) {

        //capturar parametros
		$usuario = $_POST["usuario"];
        $pass = $_POST["pass"];
        $correo = $_POST["correo"];

    //url donde se invoca el webservice a consumir
    $wsdl=$_POST["url"];
    
    //guardar los datos en las opciones de wordpress

    $data_r = array('usuario' => $usuario, 'pass' => $pass, 'url' => $wsdl, 'correo' => $correo, 1, false );
    update_option('wcm_custom_option', $data_r);

    //instanciando un nuevo objeto cliente para consumir el webservice
    $client=new nusoap_client($wsdl,'wsdl');

    //pasando los parámetros a un array
    $param=array('_Usuario'=>$usuario, '_Clave' => $pass);

    //llamando al método y pasándole el array con los parámetros
    $resultado = $client->call('LoginTest', $param);
   
    //si ocurre algún error al consumir el Web Service
    if ($client->fault) { // si
        $error = $client->getError();
    if ($error) { // Hubo algun error
            echo 'Error:  ' . $client->faultstring;
        }
        
        die();
    }
    
	}
?>
        <?php
        if(isset($_POST["submit"]) && !empty($_POST["submit"])) {
        ?>   
        <center> 
        <h2>Resultado de la Solicitud:</h2>
            <div class="listado-pendientes">
            <ul>
                
                    <table border="1" style=" border-collapse: collapse;" >
                        <?php
	                        
                                $result= $resultado['LoginTestResult'];
                                //se imprime la respuesta consultada al metodo en Lista
                                echo "<tr ><td width='15%'>1.Ok: </td><td width='85%'>";
                                    echo $result['Ok'];
                                echo "</td></tr>";

                                echo "<tr border='1'><td width='15%'>2.Error: </td><td width='85%'>";
                                    echo $result['CodigoError'];
                                echo "</td></tr>";

                                echo "<tr border='1'><td width='15%'>3.Info: </td><td width='85%'>";
                                    echo $result['Info'];
                                echo "</td></tr></table></center> </br></br><pre> ";                              
                                //var_dump($resultado);

                               //se imprime la respuesta consultada al metodo
                               //print_r($resultado);
                               // funcion para pocisionar el scroll al final de la pagina
                               echo "<script language='javascript'>";
                               echo "window.scroll({ top: 2500, left: 0, behavior: 'smooth' });";
                               echo "</script>";
                            }else{
                                // echo "<h2>Consulte el fomulario para obtener un resultado:</h2>";
                            }
                        ?>       
                   </pre>
                </ul>
        </div>

          
    </main>
</div>