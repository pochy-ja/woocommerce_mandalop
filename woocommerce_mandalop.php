<?php
/*
 * Plugin Name: Woocommerce y Mandalop
 * Plugin URI: https://github.com/pochy-ja/woocommerce_mandalop
 * Description: Integra woocommerce con el transportista mandalop, envía las órdenes completas hacia el API de mandalop
 * Author: Pochy
 * Version: 1.0
 * Author URI: https://github.com/pochy-ja/
 * License: GPLv2+
*/

function wcm_Add_My_Admin_Link()
{
      add_menu_page(
        'WC Mandalop - Opciones',
        'WC - Mandalop',
        'manage_options',
        'woocommerce_mandalop/include/wc_mandalop-page.php'
    );
}

add_action( 'admin_menu', 'wcm_Add_My_Admin_Link' );

/* after an order has been processed, we will use the  'woocommerce_thankyou' hook, to add our function, to send the data */
add_action('woocommerce_thankyou', 'wdm_send_order_to_ext'); 
function wdm_send_order_to_ext( $order_id ){
    // get order object and order details
    $order = new WC_Order( $order_id );

    $email = $order->billing_email;
    $phone = $order->billing_phone;
    $name = $order->shipping_first_name.' '.$order->shipping_last_name;
    $adress = $order->shipping_address_1;
    $postalcode = $order->shipping_postcode;
    $poblado = $order->shipping_city;
    $provincia = $order->shipping_state;

    $options_r = get_option('wcm_custom_option');

    if(isset($options_r)){

require_once(plugin_dir_path(__FILE__).'include/nusoap.php');
    
		$usuario = $options_r["usuario"];
        $pass = $options_r["pass"];
        $wsdl = $options_r["url"];

        $email_to = $options_r["correo"];
        $email_subject = 'Error al enviar a mandalop la orden '.$order_id;
    
    $client=new nusoap_client($wsdl,'wsdl');


    $resultado = $client->call('ServidorInformacion');

    $result= $resultado['ServidorInformacionResult'];
    $info = $result['Value'];
        
    if ($result['Ok'] == true and $result['CodigoError'] == 0 and $info['ServidorEstado'] == 'OK' ) {

        echo 'Conectado al servidor - Ok'."\n";
    
        $param=array('_Usuario'=>$usuario, '_Clave' => $pass);
    
        $conexion = $client->call('LoginTest', $param);
    
        //si ocurre algún error al consumir el Web Service
        if ($client->fault) { // si
            $error = $client->getError();
        if ($error) { // Hubo algun error
                echo 'Error:  ' . $client->faultstring;
                
                $email_body = 'Error:  ' . $client->faultstring;
                wp_mail($email_to, $email_subject, $email_body);
            }
            
            die();
        }
    
        $ini = $conexion['LoginTestResult'];
    
        echo $ini['Info']."\n";
    
        if ($ini['Ok'] == true and $ini['CodigoError'] == 0) {
    
            $albaran = 0;
            $acode = '48H';
            $dnombre = $name;  
            $ddes = $adress;    
            $dpob = $poblado;  
            $codep = $postalcode;  
            $bultos = 1;  
            $peso = 1; 
            $salida = date("Y-m-d", strtotime('tomorrow'));
    
        $param1=array('_Usuario'=>$usuario, '_Clave' => $pass, '_AlbaranNumero' => $albaran, 
        '_ArticuloCodigo' => $acode, '_Fecha' => $salida, 
        '_DNombre' => $dnombre, '_DDireccion' => $ddes, '_DPoblacion' => $dpob, 
        '_DCodigoPostal' => $codep,
        '_Bultos' => $bultos, '_Peso' => $peso);
    
        $orden = $client->call('GrabarEnvio', $param1);
       
        //si ocurre algún error al consumir el Web Service
        if ($client->fault) { // si
            $error = $client->getError();
        if ($error) { // Hubo algun error
    
                echo 'Error:  ' . $client->faultstring;

                $email_body = 'Error:  ' . $client->faultstring;
                wp_mail($email_to, $email_subject, $email_body);
            }
            
            die();
        }
    
            echo 'Agregada la orden '.$orden['GrabarEnvioResult']['Value'].' al sistema...'."\n";
            // print_r($orden['GrabarEnvioResult']);
        }else{
            echo 'Problemas de login...'."\n";
        
            $email_body = "Problemas de login, código de error: ".$ini['CodigoError']."\n Info: ".$ini['Info']."\n";
            wp_mail($email_to, $email_subject, $email_body);
        }
    
    }else{
        echo 'Problemas con el servidor...'."\n";
        
        $email_body = "Problemas con el servidor, código de error: ".$result['CodigoError']."\n Info: ".$result['Info']."\n Detalles: ".$result['Value'];
        wp_mail($email_to, $email_subject, $email_body);
    }
    
    }
    
 }

?>