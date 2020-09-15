<?php

//requerimos la libreria nusoap para manejo de webservice con php
require_once('include/nusoap.php');

//url donde se invoca el webservice a consumir
$wsdl="http://www3.ubilop.com/factws/IntegraCF.asmx?wsdl";

//instanciando un nuevo objeto cliente para consumir el webservice
$client=new nusoap_client($wsdl,'wsdl');

//llamando al método y pasándole el array con los parámetros
$resultado = $client->call('ServidorInformacion');

$result= $resultado['ServidorInformacionResult'];
$info = $result['Value'];

$usuario = 'user';
$pass = 'pass';

if ($result['Ok'] == true and $result['CodigoError'] == 0 and $info['ServidorEstado'] == 'OK' ) {

    echo 'Conectado al servidor - Ok'."\n";

    $param=array('_Usuario'=>$usuario, '_Clave' => $pass);

    //llamando al método y pasándole el array con los parámetros
    $conexion = $client->call('LoginTest', $param);

    //si ocurre algún error al consumir el Web Service
    if ($client->fault) { // si
        $error = $client->getError();
    if ($error) { // Hubo algun error
            echo 'Error:  ' . $client->faultstring;
        }
        
        die();
    }

    $ini = $conexion['LoginTestResult'];

    echo $ini['Info']."\n";

    if ($ini['Ok'] == true and $ini['CodigoError'] == 0) {

        $albaran = 0;
        $acode = '48H';
        $dnombre = 'Pepe';  
        $ddes = 'Calle de la Toronga 23 7D';    
        $dpob = 'Madrid';  
        $codep = '28043';  
        $bultos = 1;  
        $peso = 1; 
        $salida = date("Y-m-d", strtotime('tomorrow'));

        //pasando los parámetros a un array
    $param1=array('_Usuario'=>$usuario, '_Clave' => $pass, '_AlbaranNumero' => $albaran, 
    '_ArticuloCodigo' => $acode, '_Fecha' => $salida, 
    '_DNombre' => $dnombre, '_DDireccion' => $ddes, '_DPoblacion' => $dpob, 
    '_DCodigoPostal' => $codep,
    '_Bultos' => $bultos, '_Peso' => $peso);

    //llamando al método y pasándole el array con los parámetros
    $orden = $client->call('GrabarEnvio', $param1);
   
    //si ocurre algún error al consumir el Web Service
    if ($client->fault) { // si
        $error = $client->getError();
    if ($error) { // Hubo algun error

            echo 'Error:  ' . $client->faultstring;
        }
        
        die();
    }

        echo 'Agregada la orden '.$orden['GrabarEnvioResult']['Value'].' al sistema...'."\n";
        print_r($orden['GrabarEnvioResult']);
    }

}else{
    echo 'Problemas con el servidor...'."\n";
}

?>