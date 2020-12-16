<?php // Se copi e incolli questo codice in un file "function.php", fai attenzione a sta riga qua.


// Tutte le variabili utili:
//
// 1- Nome della categoria
// 2- Valore Porto Franco per ciascuna categoria ( = prezzo entro cui scatta la spedizione gratuita )
// 3- Costo della Spedizione ITALIA
// 4- Costo della Spedizione ESTERO
// 5- Sconto applicato o meno? (booleano)
// 6- Sconto totale di tutto il carrello
//

// Dichiaro la funzione che conta Il TOTALE del costo per ogni CATEGORIA

function contaProdPerCat( $cat_name ) {
		
	$NperCat = 0; 

	foreach(WC()->cart->get_cart() as $cart_item)  
		if( has_term( $cat_name, 'product_cat', $cart_item['product_id']))		
			$NperCat += $cart_item['line_total'];
	return  $NperCat;
}

// funzione di WP che aggancia la nostra funzione custom a WP.
add_action( 'woocommerce_cart_calculate_fees', 'sconti_portfo_franco', 10, 1 );


// Tutto quello che c'è in questa funzione, viene "sparato" in quel hook.
function sconti_portfo_franco($cart_object) {

	
	global $woocommerce;
	
    	if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;
	
	// Funzione che non funziona XD 
	//function ImpostaSconti($catTot, $ValPortoFranco, $CostoSped) {

	//	if ( $catTot > $ValPortoFranco) {	
	//		echo $CostoSped;	
	//	} 
	//}
	// Variabili CONTATORE

    	$conta_prodotti_ceci =
	$conta_prodotti_muraglia = 
	$conta_prodotti_dambra = 0;
	
	// Variabili TOTALI
	
	$categoria_ceci_total = 
	$categoria_muraglia_total =
	$categoria_dambra_total = 0;

	// Variabili SCONTI
	
	$sconto_ceci =
	$sconto_muraglia = 
	$sconto_dambra = 0;
	
	// Variabili GENERALI
	
	$discount = 0;
	$sconto_complessivo = 0;

	$ZonaSpedizione = wc_get_shipping_zone( $package );
    	$ID_Zona = $ZonaSpedizione->get_id();
		
	//* Add countries to array('US'); to include more countries to surcharge
	// * http://en.wikipedia.org/wiki/ISO_3166-1#Current_codes for available alpha-2 country codes 
	
	$italia = array('IT');
	$Usa_e_Canada = array('CA, US');

	/***
	Effettua conteggio dei prodotti per ciascuna categoria
	****/

	$categoria_ceci_total = contaProdPerCat( 808 ) + contaProdPerCat( 930 ) ; // Calcola sia prodotti in ITA che in ENG
	$categoria_muraglia_total = contaProdPerCat( 810 ) + contaProdPerCat( 852 ) + contaProdPerCat( 926 ) + contaProdPerCat( 928 ); //  Muraglia food che Muraglia Design (ita + eng)
	$categoria_dambra_total = contaProdPerCat( 1235 ); //+ cat_cart_count( 856 ); // Calcola sia prodotti in ITA che in ENG

	
	$discount_text_output = "Sconti porto franco";
    	$discount_text = __( 'Sconto ', 'woocommerce' );

    	///////////////
	//////// ## CALCULATIONS ##
	///////////////
	
	$SpedizioneItalia = in_array( $woocommerce->customer->get_shipping_country(), $italia );
	$SpedizioneEstero = !in_array( $woocommerce->customer->get_shipping_country(), $italia );


	// 1 -------- Sconto CECI in base al paese di SPEDIZIONE e al COSTO TOTALE DELLA CATEGORIA

	if ( $categoria_ceci_total > 50) {		
		$sconto_ceci = 16.50;
	} 
	
	// 2 -------- Sconto Muraglia in base al paese di SPEDIZIONE e al COSTO TOTALE DELLA CATEGORIA
	
	if ( $categoria_muraglia_total > 100 && $SpedizioneItalia) { 	
		$sconto_muraglia = 7; 		
	} elseif ( $categoria_muraglia_total > 200 && $SpedizioneEstero) { 	
		$sconto_muraglia = 19; 		
	} 
	
	// 2 -------- Sconto DAMBRA in base al paese di SPEDIZIONE e al COSTO TOTALE DELLA CATEGORIA
	
	if ( $categoria_dambra_total > 35 && $SpedizioneItalia) { 	
		$sconto_dambra = 9; 		
	} elseif ( $categoria_dambra_total > 90 && $SpedizioneEstero) { 	
		$sconto_dambra = 25; 		
	} 
		
	///////////////
	// Calcolo dello sconto --> AGGIUNGERE VARIABILE SCONTO SE SI AGGIUNGONO NUOVE ATTIITA'
	//////////////////
	
	$sconto_complessivo = $sconto_ceci + $sconto_muraglia + $sconto_dambra; // Aggiungi variabile qui

	// Questo resta come sta

	$discount = $sconto_complessivo * -1;	

	// Note: Last argument in add_fee() method is related to applying the tax or not to the discount (true or false)	
	if ( $discount != 0 ) {$cart_object->add_fee( $discount_text_output, $discount, false );}
}


///////////////
///// Conteggio prodotti per categoria
///////////////

add_action( 'woocommerce_proceed_to_checkout', 'conta_categorie', 10, 1 ); // Conta in carrello
add_action( 'woocommerce_review_order_before_payment', 'conta_categorie', 10, 1 ); // Conta in cassa
 
function conta_categorie() {
	
	
	function cat_cart_count( $cat_name ) {
		
		$cat_count = 0; 
		foreach(WC()->cart->get_cart() as $cart_item)  
			if( has_term( $cat_name, 'product_cat', $cart_item['product_id']))		
				$cat_count += $cart_item['line_total'];
		return  $cat_count;
	}
	
	$costo_prodotti_ceci = cat_cart_count( 808 ) + cat_cart_count( 930 ) ; // Calcola sia prodotti in ITA che in ENG
	$costo_prodotti_muraglia = cat_cart_count( 810 ) + cat_cart_count( 852 ) + cat_cart_count( 926 ) + cat_cart_count( 928 ); //  Muraglia food che Muraglia Design (ita + eng)
	$costo_prodotti_agrimperiale = cat_cart_count( 806 ) + cat_cart_count( 936 ); // Calcola sia prodotti in ITA che in ENG
	$costo_prodotti_maskhave = cat_cart_count( 854 ) + cat_cart_count( 856 ); // Calcola sia prodotti in ITA che in ENG
	$costo_prodotti_dambra = cat_cart_count( 1235 ); //+ cat_cart_count( 856 ); // Calcola sia prodotti in ITA che in ENG

	
	echo '<div class="contenitore__conteggio"><ul>';
	
	// ImpostaSconti($categoria_ceci_total, 50, 16.50);
	
	
	if( $costo_prodotti_ceci > 0 ) {
		
		if (get_locale() == 'it_IT') {
			echo '<li class="conteggio"><b>Totale Prodotti Ceci</b> : ' . $costo_prodotti_ceci . "€";
			echo '<br>Ricevi la <span>SPEDIZIONE GRATUITA su questi prodotti acquistando <b>+ di 50€</b> (solo Italia)</span></li>';
		} else {
			echo '<li class="conteggio"><b>Ceci Products Total</b> : ' . $costo_prodotti_ceci . "€";
			echo '<span>FREE SHIPPING OVER <b>50€</b></span></li>';
		};	
	};

	if ($costo_prodotti_muraglia > 0 ) {
		
		if (get_locale() == 'it_IT') {
			
			echo '<li class="conteggio"><b> Prodotti Muraglia</b> : ' . $costo_prodotti_muraglia . "€";
			echo '<br>Ricevi la <span>SPEDIZIONE GRATUITA IN <b>ITALIA</b> su questi prodotti acquistando <b>+ di 100€</b></span></li>';
			
		} else {
			
			echo '<li class="conteggio"><b>Muraglia Products Total</b> : ' . $costo_prodotti_muraglia . "€";
			echo '<span>FREE SHIPPING IN <b>ITALY</b>: over <b>100€</b><BR>FREE SHIPPING <b>Foreign countries</b>: over <b>200€</b></span></li>';
		
		};		
	};
	if ($costo_prodotti_dambra > 0 ) {
		
		if (get_locale() == 'it_IT') {
			
			echo '<li class="conteggio"><b> Prodotti Dambra</b> : ' . $costo_prodotti_dambra . "€<br>";
			echo 'Ricevi la <b>SPEDIZIONE GRATUITA</b> su questi prodotti acquistando:<br>';
			echo "<b>+ di 35€</b> per sped. <b>ITALIA</b><br>";
			echo "<b>+ di 90€</b> per sped. <b>ESTERO</b>";
			echo '</li>';
			
		} else {
			
			echo '<li class="conteggio"><b>Dambra Products Total</b> : ' . $costo_prodotti_dambra . "€";
			echo '<span>FREE SHIPPING IN <b>ITALY</b>: over <b>100€</b><BR>FREE SHIPPING <b>Foreign countries</b>: over <b>90€</b></span></li>';
		
		};		
	};
	echo '</ul></div>';
	
}
