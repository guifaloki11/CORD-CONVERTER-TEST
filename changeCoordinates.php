<?php
/********************************************************************
A seguir estão as funções escritas em PHP que realizam conversão de 
coordenadas de projeção para coordenadas geográficas e vice versa  e
que são utilizadas na Calculadora Geografica do INPE
(www.dpi.inpe.br/calcula/)

Referencia:
SNYDER, John Parr. Map projections--A working manual. 
US Government Printing Office, 1987.

********************************************************************/

/********************************************************************
Parametros dos datuns utilizados
********************************************************************/
$datum_parametros = array
(
//id datum semi_eixo achat deltax deltay deltaz
"0	Null  0.000000e+00	0.000000e+00	0.000000e+00	0.000000e+00	0.000000e+00",
"1	SAD69	6.3781600e+06	3.35289187e-03	-6.735000e+01	3.880000e+00	-3.822000e+01",
"2	CorregoAlegre	6.3783880e+06	3.36700337e-03	-2.060500e+02	1.682800e+02	-3.820000e+00",
"3	AstroChua	6.3783880e+06	3.36700337e-03	-1.443500e+02	2.433700e+02	-3.322000e+01",
"4	WGS84	6.3781370e+06	3.35281066e-03	0.000000e+00	0.000000e+00	0.000000e+00",
"5	SIRGAS2000	6.3781370e+06	3.35281068e-03	0.000000e+00	0.000000e+00	0.000000e+00",
);

/********************************************************************
 geo_to_utm				       
 Autor		: Julio Cesar Lima d'Alge		       jul-88
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas geodesicas em coordenadas UTM
 Entradas	: 
 			lat		coordenadas geodesicas (em radianos)
		  lon		coordenadas geodesicas (em radianos)
		  lon_mc	meridiano central (em radianos)
		  semi_eixo	semi_eixo_maior do elipsoide
		  achat		achatamento do elipsoide
			hemis		norte ou sul
 Saidas		: x y coordenadas UTM (em metros)
********************************************************************/
function geo_to_utm($lat,$lon,$semi_eixo,$achat,$hemis,$lon_mc,&$x,&$y)
{
 		      // define PI
					$pi = pi();
					
					// verifica hemisferio
					if ($hemis == "norte")
   				$offy = 0.;
					else
					$offy = 10000000;
		 	 											
					// Converte Lat Long em UTM

					$k0 = 1. - (1./2500.);
					$equad = 2.*$achat - pow($achat,(double)2);
					$elinquad = $equad/(1. - $equad);

					$aux1 = $equad*$equad;
					$aux2 = $aux1*$equad;
					$aux3 = sin((double)2*$lat);
					$aux4 = sin((double)4*$lat);
					$aux5 = sin((double)6*$lat);
					$aux6 = (1. - $equad/4. - 3.*$aux1/64. - 5.*$aux2/256.)*$lat;
					$aux7 = (3.*$equad/8. + 3.*$aux1/32. + 45.*$aux2/1024.)*$aux3;
					$aux8 = (15.*$aux1/256. + 45.*$aux2/1024.)*$aux4;
					$aux9 = (35.*$aux2/3072.)*$aux5;

					$n = $semi_eixo/sqrt((double)1-$equad*pow(sin($lat),(double)2));
					$t = pow(tan($lat),(double)2);
					$c = $elinquad*pow(cos($lat),(double)2);
					$ag = ($lon-$lon_mc)*cos($lat);
					$m = $semi_eixo*($aux6 - $aux7 + $aux8 - $aux9);

					$aux10 = (1.-$t+$c)*$ag*$ag*$ag/6.;
					$aux11 = (5.-18.*$t+$t*$t+72.*$c-58.*$elinquad)*(pow($ag,(double)5))/120.;
					$aux12 = (5.-$t+9.*$c+4.*$c*$c)*$ag*$ag*$ag*$ag/24.;
					$aux13 = (61.-58.*$t+$t*$t+600.*$c-330.*$elinquad)*(pow($ag,(double)6))/720.;

					$x = 500000. + $k0*$n*($ag + $aux10 + $aux11);
  				$y = $offy + $k0*($m + $n*tan($lat)*($ag*$ag/2. + $aux12 + $aux13));
}
/********************************************************************
 utm_to_geo				       
 Autor		: Julio Cesar Lima d'Alge		       jul-88
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas UTM em coordenadas geodesicas
 Entradas	: 
 			x		coordenadas UTM (em metros)
		  y		coordenadas UTM (em metros)
		  lon_mc	meridiano central (em radianos)
		  semi_eixo	semi_eixo maior do elipsoide
		  achat		achatamento do elipsoide
			hemis		norte ou sul
 Saidas		: lat		lon coordenadas geodesicas (em radianos)
********************************************************************/
function utm_to_geo($x,$y,$semi_eixo,$achat,$hemis,$lon_mc,&$lat,&$lon)
{
 				    // define PI
						$pi = pi();
		 	 	 
						// verifica hemisferio
						if ($hemis == "norte")
   					$y = $y + 10000000;
								
						// Converte UTM para Lat/Long;
  					$k0 = 1. - (1./2500.);
						$equad = 2.*$achat - pow($achat,(double)2);
						$elinquad = $equad/(1. - $equad);
						$e1 = (1.-sqrt((double)1-$equad))/(1.+sqrt((double)1-$equad));

						$aux1 = $equad*$equad;
						$aux2 = $aux1*$equad;
						$aux3 = $e1*$e1;
						$aux4 = $e1*$aux3;
						$aux5 = $aux4*$e1;

						$m = ($y - 10000000.)/$k0;
						$mi = $m/($semi_eixo*(1.-$equad/4.-3.*$aux1/64.-5.*$aux2/256.));

						$aux6 = (3.*$e1/2. - 27.*$aux4/32.)*sin((double)2*$mi);
						$aux7 = (21.*$aux3/16. - 55.*$aux5/32.)*sin((double)4*$mi);
						$aux8 = (151.*$aux4/96.)*sin((double)6*$mi);

						$lat1 = $mi + $aux6 + $aux7 + $aux8;
						$c1 = $elinquad*pow(cos($lat1),(double)2);
						$t1 = pow(tan($lat1),(double)2);
						$n1 = $semi_eixo/sqrt((double)1-$equad*pow(sin($lat1),(double)2));
						$quoc = pow(((double)1-$equad*sin($lat1)*sin($lat1)),(double)3);
						$r1 = $semi_eixo*(1.-$equad)/sqrt($quoc);
						$d = ($x - 500000.)/($n1*$k0);

						$aux9 = (5.+3.*$t1+10.*$c1-4.*$c1*$c1-9.*$elinquad)*$d*$d*$d*$d/24.;
						$aux10 = (61.+90.*$t1+298.*$c1+45.*$t1*$t1-252.*$elinquad-3.*$c1*$c1)*pow($d,(double)6)/720.;
						$aux11 = $d - (1.+ 2.*$t1 + $c1)*$d*$d*$d/6.;
						$aux12 = (5.-2.*$c1+28.*$t1-3.*$c1*$c1+8.*$elinquad+24.*$t1*$t1)*pow($d,(double)5)/120.;

						$lat = $lat1 - ($n1*tan($lat1)/$r1)*($d*$d/2. - $aux9 + $aux10);
						$lon = $lon_mc + ($aux11 + $aux12)/cos($lat1);

}
/********************************************************************
 poli_to_geo				       
 Autor		: Julio Cesar Lima d'Alge		       fev-90
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas policonicas em coordenadas geodesicas
 Entradas	: 
 			x		coordenadas policonicas (em metros)
		  y		coordenadas policonicas (em metros)
		  lat0		paralelo origem (em radianos)
		  long0		meridiano origem (em radianos)
		  semi_eixo	semi_eixo maior do elipsoide
		  achat		achatamento do elipsoide
 Saidas		: lat	lon coordenadas geodesicas (em radianos)
********************************************************************/
function poli_to_geo($x,$y,$semi_eixo,$achat,$latO,$longO,&$lat,&$lon)
{
$equad = 2.*$achat - pow($achat,(double)2);

	/* Para calculo de mo lat vira lat0 */

	$aux01 = (1.-$equad/4.-3.*$equad*$$equad/64.-5.*$equad*$equad*$equad/256.)*$latO;
	$aux02 = (3.*$equad/8+3.*$equad*$equad/32.+45.*$equad*$equad*$equad/1024.)*sin((double)2*$latO);
	$aux03 = (15.*$equad*$equad/256.+45.*$equad*$equad*$equad/1024.)*sin((double)4*$latO);
	$aux04 = (35.*$equad*$equad*$equad/3072.)*sin((double)6*$latO);
	$m0 = $semi_eixo*($aux01 - $aux02 + $aux03 - $aux04);

	if ($y == (-$m0))
	{
		$lat = 0.;
		$lon = $x/$semi_eixo + $longO;
	}
	else
	{
		$A = ($m0 + $y)/$semi_eixo;
		$B = (($x*$x)/($semi_eixo*$semi_eixo)) +($A*$A);

		$lat2 = $A;	/* Inicializando a $latitude para a iteracao */

					do
					{
					$C = (sqrt(1.- $equad*sin($lat2)*sin($lat2)))*tan($lat2);

					/* Calculo de mn */	
					$aux21 = (1.-$equad/4.-3.*$equad*$equad/64.-5.*$equad*$equad*$equad/256.)*$lat2;
					$aux22 = (3.*$equad/8.+3.*$equad*$equad/32.+45.*$equad*$equad*$equad/1024.)*sin((double)2*$lat2);
					$aux23 = (15.*$equad*$equad/256.+45.*$equad*$equad*$equad/1024.)*sin((double)4*$lat2);
					$aux24 = (35.*$equad*$equad*$equad/3072.)*sin((double)6*$lat2);
					$mn = $semi_eixo*($aux21 - $aux22 + $aux23 - $aux24);
		
					/* Calculo de mnl*/
					$aux05 = 1.- $equad/4.-3.*$equad*$equad/64.-5.*$equad*$equad*$equad/256.;
					$aux06 = 2.*(3.*$equad/8.+3.*$equad*$equad/32.+45.*$equad*$equad*$equad/1024.)*cos((double)2*$lat2);
					$aux07 = 4.*(15.*$equad*$equad/256.+45.*$equad*$equad*$equad/1024.)*cos((double)4*$lat2);
					$aux08 = 6.*(35.*$equad*$equad*$equad/3072.)*cos((double)6*$lat2);
					$mnl = $aux05 - $aux06 + $aux07- $aux08;

					/* Calculo de ma */
					$ma = $mn/$semi_eixo;
					$aux09 = ($A*($C*$ma+1)-$ma)-(0.5*($ma*$ma+$B)*$C);
					$aux10 = $equad*sin((double)2*$lat2)*($ma*$ma+$B-2.*$A*$ma);
					$aux11 = 4.*$C+($A-$ma);
					$aux12 = $C*$mnl-(2./sin((double)2*$lat2));

					/* Calculo da nova $latitude */ 
					$lat1 = $lat2 - ($aux09/(($aux10/($aux11*$aux12)) - $mnl));
					$cp = abs($lat1-$lat2) ;
					$lat2 = $lat1;

					}
					while($cp > 0.000000001);

		$lat = $lat1;
		$lon = ((asin(($x*$C)/$semi_eixo))/(sin($lat1))) + $longO;
  }
}
/********************************************************************
 geo_to_poli				       
 Autor		: Julio Cesar Lima d'Alge		       fev-90
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas geodesicas em coordenadas policonicas
 Entradas	: 
 			lat		coordenadas geodesicas (em radianos)
		  lon		coordenadas geodesicas (em radianos)
		  lat0		paralelo origem (em radianos)
		  long0	meridiano origem (em radianos)
		  semi_eixo	semi_eixo maior do elipsoide
		  achat		achatamento do elipsoide
 Saidas		: x	y coordenadas policonicas (em metros)
********************************************************************/
function geo_to_poli($lat,$lon,$semi_eixo,$achat,$latO,$longO,&$x,&$y)
{

				 $equad = 2.*$achat - pow($achat,(double)2);
				 
				 $aux01 = (1.-$equad/4.-3.*$equad*$equad/64.-5.*$equad*$equad*$equad/256.)*$latO;
				 $aux02 = (3.*$equad/8+3.*$equad*$equad/32.+45.*$equad*$equad*$equad/1024.)*sin((double)2*$latO);
				 $aux03 = (15.*$equad*$equad/256.+45.*$equad*$equad*$equad/1024.)*sin((double)4*$latO);
				 $aux04 = (35.*$equad*$equad*$equad/3072.)*sin((double)6*$latO);
				 $m0 = $semi_eixo*($aux01 - $aux02 + $aux03 - $aux04);

				 if ($lat == 0.)
				 {
				 $x = $semi_eixo*($lon - $longO);
				 $y = -$m0;
				 }
				 else
				 {
				 $aux1 = (1.-$equad/4.-3.*$equad*$equad/64.-5.*$equad*$equad*$equad/256.)*$lat;
				 $aux2 = (3.*$equad/8+3.*$equad*$equad/32.+45.*$equad*$equad*$equad/1024.)*sin((double)2*$lat);
				 $aux3 = (15.*$equad*$equad/256.+45.*$equad*$equad*$equad/1024.)*sin((double)4*$lat);
				 $aux4 = (35.*$equad*$equad*$equad/3072.)*sin((double)6*$lat);
				 $m = $semi_eixo*($aux1 - $aux2 + $aux3 - $aux4);
				 $n = $semi_eixo/sqrt((double)1 - $equad*pow(sin($lat),(double)2));
				 $e = ($lon - $longO)*sin($lat);
				 $x = $n*sin($e)/tan($lat);
				 $y = $m - $m0 + ($n*(1. - cos($e))/tan($lat));
				 }
}
/********************************************************************
 lamb_to_geo				       
 Autor		: Julio Cesar Lima d'Alge		       set-88
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas Lambert em coordenadas geodesicas
 Entradas	: 
 			x		coordenadas Lambert (em metros)
		  y		coordenadas Lambert (em metros)
		  lat0		paralelo origem (em radianos)
		  long0	meridiano origem (em radianos)
		  lat1		paralelo padrao 1 (em radianos)
		  lat2		paralelo padrao 2 (em radianos)
		  semi_eixo	semi_eixo maior do elipsoide
		  achat		achatamento do elipsoide
 Saidas		: lat		lon coordenadas geodesicas (em radianos)
********************************************************************/
function lamb_to_geo($x,$y,$latO,$longO,$lat1,$lat2,$semi_eixo,$achat,&$lat,&$lon)
{
         // define PI
				 $pi = pi();
				 //$pi = 4.*atan((double)1);
				 
 				 $equad = 2.*$achat - pow($achat,(double)2);
				 $e = sqrt($equad);
				
				 $m1 = cos($lat1)/sqrt((double)1-$equad*pow(sin($lat1),(double)2));
				 $m2 = cos($lat2)/sqrt((double)1-$equad*pow(sin($lat2),(double)2));
				 $aux1 = sqrt(((double)1-$e*sin($lat1))/((double)1+$e*sin($lat1)));
				 $aux2 = sqrt(((double)1-$e*sin($lat2))/((double)1+$e*sin($lat2)));
				 $aux0 = sqrt(((double)1-$e*sin($latO))/((double)1+$e*sin($latO)));
				 $t1 = ((1.-tan($lat1/(double)2))/(1.+tan($lat1/(double)2)))/pow($aux1,$e);
				 $t2 = ((1.-tan($lat2/(double)2))/(1.+tan($lat2/(double)2)))/pow($aux2,$e);
				 $t0 = ((1.-tan($latO/(double)2))/(1.+tan($latO/(double)2)))/pow($aux0,$e);

				 if ($lat1 == $lat2)
				 $n = sin($lat1);
				 else
				 $n = (log($m1)-log($m2))/(log($t1)-log($t2));

				 $efe = $m1/($n*pow($t1,$n));
				 $ro0 = $semi_eixo*$efe*pow($t0,$n);

				 $sinal = (int)($n/abs($n));
				 $ro = sqrt($x*$x + ($ro0-$y)*($ro0-$y));
				 $ro *= $sinal;
				 $teta = atan($x/($ro0-$y));
				 $t = pow(($ro/($semi_eixo*$efe)),(double)1/$n);
				 $xx = $pi/2. - 2.*atan($t);
				 $aux3 = $equad/2. + 5.*$equad*$equad/24. + $equad*$equad*$equad/12.;
				 $aux4 = 7.*$equad*$equad/48. + 29.*$equad*$equad*$equad/240.;
				 $aux5 = (7.*$equad*$equad*$equad/120.)*sin(12.*atan($t));

				 $lat = $xx + $aux3*sin(4.*atan($t)) - $aux4*sin(8.*atan($t)) + $aux5;
				 $lon = $teta/$n + $longO;
}

/********************************************************************
 geo_to_lamb				       
 Autor		: Julio Cesar Lima d'Alge		       set-88
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas geodesicas em coordenadas Lambert
 Entradas	: 
 			lat		coordenadas geodesicas (em radianos)
	  	lon   coordenadas geodesicas (em radianos)
		  lat0		paralelo origem (em radianos)
		  long0 	meridiano origem (em radianos)
		  lat1		paralelo padrao 1 (em radianos)
		  lat2		paralelo padrao 2 (em radianos)
		  semi_eixo	semi_eixo maior do elipsoide
		  achat		achatamento do elipsoide
 Saidas		: x		y coordenadas Lambert (em metros)
********************************************************************/
function geo_to_lamb($lat,$lon,$latO,$longO,$lat1,$lat2,$semi_eixo,$achat,&$x,&$y)
{

				 $equad = 2.*$achat - pow($achat,(double)2);
				 $e = sqrt($equad);

				 $m1 = cos($lat1)/sqrt((double)1-$equad*pow(sin($lat1),(double)2));
				 $m2 = cos($lat2)/sqrt((double)1-$equad*pow(sin($lat2),(double)2));
				 $aux1 = sqrt(((double)1-$e*sin($lat1))/((double)1+$e*sin($lat1)));
				 $aux2 = sqrt(((double)1-$e*sin($lat2))/((double)1+$e*sin($lat2)));
				 $aux0 = sqrt(((double)1-$e*sin($latO))/((double)1+$e*sin($latO)));
				 $t1 = ((1.-tan($lat1/(double)2))/(1.+tan($lat1/(double)2)))/pow($aux1,$e);
				 $t2 = ((1.-tan($lat2/(double)2))/(1.+tan($lat2/(double)2)))/pow($aux2,$e);
				 $t0 = ((1.-tan($latO/(double)2))/(1.+tan($latO/(double)2)))/pow($aux0,$e);

				 if ($lat1 == $lat2)
				 $n = sin($lat1);
				 else
				 $n = (log($m1)-log($m2))/(log($t1)-log($t2));

				 $efe = $m1/($n*pow($t1,$n));
				 $ro0 = $semi_eixo*$efe*pow($t0,$n);

				 $aux = sqrt(((double)1-$e*sin($lat))/((double)1+$e*sin($lat)));
				 $t = ((1.-tan($lat/(double)2))/(1.+tan($lat/(double)2)))/pow($aux,$e);
				 $ro = $semi_eixo*$efe*pow($t,$n);
				 $teta = $n*($lon-$longO);
	
				 $x = $ro*sin($teta);
				 $y = $ro0 - $ro*cos($teta);
}

/********************************************************************
 merc_to_geo				       
 Autor		: Julio Cesar Lima d'Alge		       jul-88
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas Mercator em coordenadas geodesicas
 Entradas	: 
 			x		coordenadas Mercator (em metros)
		  y   coordenadas Mercator (em metros)
		  lat1	latitude padrao ou reduzida (em radianos)
		  long0	meridiano origem (em radianos)
		  semi_eixo	semi_eixo maior do elipsoide
		  achat		achatamento
 Saidas		: lat		lon coordenadas geodesicas (em radianos)
********************************************************************/
function merc_to_geo($x,$y,$lat1,$longO,$semi_eixo,$achat,&$lat,&$lon)
{
         // define PI
				 $pi = pi();
				 
				 $equad = 2.*$achat - pow($achat,(double)2);
				
				 $aux1 = cos($lat1);
				 $aux2 = 1./sqrt((double)1-$equad*pow(sin($lat1),(double)2));
				 $x = $x/($aux1*$aux2);
				 $y = $y/($aux1*$aux2);
				 $t = exp(-$y/$semi_eixo);
				 $xx = $pi/2. - 2.*atan($t);
				 $aux3 = ($equad/2.+5.*$equad*$equad/24.+$equad*$equad*$equad/12.)*sin(4.*atan($t));
				 $aux4 = -(7.*$equad*$equad/48.+29.*$equad*$equad*$equad/240.)*sin(8.*atan($t));
				 $aux5 = (7.*$equad*$equad*$equad/120.)*sin(12.*atan($t));

				 $lat = $xx + $aux3 + $aux4 + $aux5;
				 $lon = $x/$semi_eixo + $longO;
}

/********************************************************************
 geo_to_merc				       
 Autor		: Julio Cesar Lima d'Alge		       jul-88
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas geodesicas em coordenadas Mercator
 Entradas	: 
      lat		coordenadas geodesicas (em radianos)
		  lon   coordenadas geodesicas (em radianos)
		  lat1	latitude padrao ou reduzida (em radianos)
		  long0	meridiano origem (em radianos)
		  semi_eixo	semi_eixo maior do elipsoide
		  achat		achatamento do elipsoide
 Saidas		: x		y coordenadas Mercator (em metros)
********************************************************************/
function geo_to_merc($lat,$lon,$lat1,$longO,$semi_eixo,$achat,&$x,&$y)
{

				 $equad = 2.*$achat - pow($achat,(double)2);
				 $aux1 = (1. + tan($lat/(double)2))/(1. - tan($lat/(double)2));
				 $aux2 = ($equad+$equad*$equad/4.+$equad*$equad*$equad/8.)*sin($lat);
				 $aux3 = ($equad*$equad/12.+$equad*$equad*$equad/16.)*sin((double)3*$lat);
				 $aux4 = ($equad*$equad*$equad/80.)*sin((double)5*$lat);
				 $aux5 = cos($lat1);
				 $aux6 = 1./sqrt((double)1-$equad*pow(sin($lat1),(double)2));

				 $x = $semi_eixo*($lon - $longO)*$aux5*$aux6;
				 $y = $semi_eixo*(log($aux1) - $aux2 + $aux3 - $aux4)*$aux5*$aux6;
}

/********************************************************************
 alb_to_geo				       
 Autor		: Julio Cesar Lima d'Alge		       mar-90
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas Albers em coordenadas geodesicas
 Entradas	: 
      x		coordenadas Albers (em metros)
		  y		coordenadas Albers (em metros)
		  lat0		paralelo origem (em radianos)
		  long0	meridiano origem (em radianos)
		  lat1		paralelo padrao 1 (em radianos)
		  lat2		paralelo padrao 2 (em radianos)
		  semi_eixo 	semi_eixo_maior do elipsoide
		  achat		achatamento do elipsoide
 Saidas		: lat		lon coordenadas geodesicas (em radianos)
********************************************************************/
function alb_to_geo($x,$y,$latO,$longO,$lat1,$lat2,$semi_eixo,$achat,&$lat,&$lon)
{

	$sinal = (int)($lat2/abs($lat2));
	$equad = 2.*$achat - pow($achat,(double)2);
  $e = sqrt($equad);

	$m1 = cos($lat1)/sqrt((double)1-$equad*pow(sin($lat1),(double)2));
	$m2 = cos($lat2)/sqrt((double)1-$equad*pow(sin($lat2),(double)2));
	$aux10 = sin($latO)/((double)1-$equad*pow(sin($latO),(double)2));
	$aux11 = sin($lat1)/((double)1-$equad*pow(sin($lat1),(double)2));
	$aux12 = sin($lat2)/((double)1-$equad*pow(sin($lat2),(double)2));
	$aux20 = log((1. - $e*sin($latO))/(1. + $e*sin($latO)));
	$aux21 = log((1. - $e*sin($lat1))/(1. + $e*sin($lat1)));
	$aux22 = log((1. - $e*sin($lat2))/(1. + $e*sin($lat2)));
	$q0 = (1. - $equad)*($aux10 - (1./(2.*$e))*$aux20);
	$q1 = (1. - $equad)*($aux11 - (1./(2.*$e))*$aux21);
	$q2 = (1. - $equad)*($aux12 - (1./(2.*$e))*$aux22);
	$n = ($m1*$m1 - $m2*$m2)/($q2 - $q1);
	$c = $m1*$m1 + $n*$q1;
	$ro0 = $semi_eixo*sqrt($c - $n*$q0)/$n;
	$ro = sqrt($x*$x + ($ro0 - $y)*($ro0 - $y));
	$q = ($c - ($ro*$ro*$n*$n/($semi_eixo*$semi_eixo)))/$n;
	$aux = ((1. - $equad)/(2.*$e))*log((1. - $e)/(1. + $e));
	$beta = asin($q/(1. - $aux));
	$aux1 = ($equad/3. + 31.*$equad*$equad/180. + 517.*$equad*$equad*$equad/5040.)*sin(2.*$beta);
	$aux2 = (23.*$equad*$equad/360. + 251.*$equad*$equad*$equad/3780.)*sin(4.*$beta);
	$aux3 = (761.*$equad*$equad*$equad/45360.)*sin(6.*$beta);
	$teta = abs(atan($x/($ro0 - $y)));

	if ($sinal == 1)
	{
		if ($x < 0.)
			$teta = -$teta;
	}

	if ($sinal == -1)
	{
		if ($x > 0.)
			$teta *= $sinal;
	}

	$lat = $beta + $aux1 + $aux2 + $aux3;
	$lon = $longO + ($teta/$n);
}

/********************************************************************
 geo_to_alb				       
 Autor		: Julio Cesar Lima d'Alge		       mar-90
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas geodesicas em coordenadas Albers
 Entradas	: 
      lat		coordenadas geodesicas (em radianos)
		  lon		coordenadas geodesicas (em radianos)
		  lat0		paralelo origem (em radianos)
		  long0	meridiano origem (em radianos)
		  lat1		paralelo padrao 1 (em radianos)
		  lat2		paralelo padrao 2 (em radianos)
		  semi_eixo	semi_eixo maior do elipsoide
		  achat		achatamento do elipsoide
 Saidas		: x		y coordenadas Albers (em metros)
********************************************************************/
function geo_to_alb($lat,$lon,$latO,$longO,$lat1,$lat2,$semi_eixo,$achat,&$x,&$y)
{

	$equad = 2.*$achat - pow($achat,(double)2);
	$e = sqrt($equad);

	$m1 = cos($lat1)/sqrt((double)1-$equad*pow(sin($lat1),(double)2));
	$m2 = cos($lat2)/sqrt((double)1-$equad*pow(sin($lat2),(double)2));
	$aux1 = sin($lat)/((double)1-$equad*pow(sin($lat),(double)2));
	$aux10 = sin($latO)/((double)1-$equad*pow(sin($latO),(double)2));
	$aux11 = sin($lat1)/((double)1-$equad*pow(sin($lat1),(double)2));
	$aux12 = sin($lat2)/((double)1-$equad*pow(sin($lat2),(double)2));
	$aux2 = log((1. - $e*sin($lat))/(1. + $e*sin($lat)));
	$aux20 = log((1. - $e*sin($latO))/(1. + $e*sin($latO)));
	$aux21 = log((1. - $e*sin($lat1))/(1. + $e*sin($lat1)));
	$aux22 = log((1. - $e*sin($lat2))/(1. + $e*sin($lat2)));
	$q0 = (1. - $equad)*($aux10 - (1./(2.*$e))*$aux20);
	$q1 = (1. - $equad)*($aux11 - (1./(2.*$e))*$aux21);
	$q2 = (1. - $equad)*($aux12 - (1./(2.*$e))*$aux22);
	$q = (1. - $equad)*($aux1 - (1./(2.*$e))*$aux2);
	$n = ($m1*$m1 - $m2*$m2)/($q2 - $q1);
	$c = $m1*$m1 + $n*$q1;
	$ro0 = $semi_eixo*sqrt($c - $n*$q0)/$n;
	$teta = $n*($lon - $longO);
	$ro = $semi_eixo*sqrt($c - $n*$q)/$n;

	$x = $ro*sin($teta);
	$y = $ro0 - $ro*cos($teta);
}

/********************************************************************
 geo_to_cilequi			       
 Autor		: Julio Cesar Lima d'Alge		       fev-90	
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas geodesicas em coordenadas cilindricas 
 equidistantes
 Entradas	: 
 			lat		coordenadas geodesicas (em radianos)
		  lon		coordenadas geodesicas (em radianos)
		  r		raio da Terra (em metros)
		  lon_mc	meridiano origem
		  lat_padrao	paralelo padrao
 Saidas		: x		coordenadas cilindricas equidistantes
		  y
********************************************************************/
function geo_to_cilequi($lat,$lon,$r,$lat1,$longO,&$x,&$y)
{
				 $x = $r*($lon - $longO)*cos($lat1);
				 $y = $r*$lat;
}

/********************************************************************
 cilequi_to_geo			       
 Autor		: Julio Cesar Lima d'Alge		       fev-90	
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas cilindricas equidistantes em
		  coordenadas geodesicas
 Entradas	: 
      x		coordenadas cilindricas equidistantes (em metros)
		  y   coordenadas cilindricas equidistantes (em metros)
		  r		raio da Terra (6371000 metros)
		  long0	meridiano origem (em radianos)
		  lat1	paralelo padrao (em radianos)
 Saidas		: lat		lon coordenadas geodesicas (em radianos)
********************************************************************/
function cilequi_to_geo($x,$y,$r,$lat1,$longO,&$lat,&$lon)
{
				 $lat = $y/$r;
				 $lon = $longO + $x/($r*cos($lat1));
}

/********************************************************************
 miller_to_geo				       
 Autor		: Julio Cesar Lima d'Alge		       mar-90
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas Miller em coordenadas geodesicas
 Entradas	: 
      x		coordenadas Miller (em metros)
		  y		coordenadas Miller (em metros)
		  r		raio da Terra (6371000 metros)
		  long0 meridiano origem (em radianos)
 Saidas		: lat		lon coordenadas geodesicas (em radianos)
********************************************************************/
function miller_to_geo($x,$y,$r,$longO,&$lat,&$lon)
{
	  		 // define PI
				 $pi = pi();
				 $e = 2.718281828;
				 $sinal = (int)(abs($y)/$y);

				 $lon = $longO + ($x/$r);

				 if ($sinal == 1)
				 $lat = 2.5*atan(pow($e,(0.8*$y/$r))) - 5.*$pi/8.;
				 else
				 {
				 $y *= $sinal;
				 $lat = 2.5*atan(pow($e,(0.8*$y/$r))) - 5.*$pi/8.;
				 $lat *= $sinal;
				 }
}

/********************************************************************
 geo_to_miller				       
 Autor		: Julio Cesar Lima d'Alge		       mar-90
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transforma coordenadas geodesicas em coordenadas Miller
 Entradas	: 
 			lat		coordenadas geodesicas (em radianos)
		  lon		coordenadas geodesicas (em radianos)
		  r		raio da Terra (6371000 metros)
		  long0	meridiano origem (em radianos)
 Saidas		: x		y coordenadas Miller (em metros)
********************************************************************/
function geo_to_miller($lat,$lon,$r,$longO,&$x,&$y)
{
 				 // define PI
	 			 $pi = pi();
	
				 $sinal = (int)(abs($lat)/$lat);
				 $x = $r*($lon - $longO);

				 if ($sinal == 1)
				 $y = ($r/0.8)*(log(tan($pi/4. + 0.4*$lat)));
				 else
				 {
				 $lat *= $sinal;
				 $y = ($r/0.8)*(log(tan($pi/4. + 0.4*$lat)));
				 $y *= $sinal;
				 }
}

/********************************************************************
 datum1_to_datum2			       
 Autor		: Julio Cesar Lima d'Alge		       out-88
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: transformacao de datum horizontal
 Entradas	: 
	  semi_eixo	semi_eixo maior do elipsoide (datum entrada)
		achat		achatamento do elipsoide (datum entrada)
		lat1		coordenadas geodesicas (em radianos)
		lon1		coordenadas geodesicas (em radianos)
		h1 			0
		semi_eixo2	semi_eixo maior do elipsoide (datum saida)
		achat2	achatamento do elipsoide (datum saida)
		lat2  	coordenadas geodesicas (em radianos)
		lon2		coordenadas geodesicas (em radianos)
 
 Saidas		: lat2 lon2 (em radianos)
********************************************************************/
function datum1_to_datum2 ($semi_eixo,$achat,$deltax,$deltay,$deltaz,$lat1,$lon1,$h1,$semi_eixo2,$achat2,$deltax2,$deltay2,$deltaz2,&$lat2,&$lon2,&$h2)
{
	/* calcula coordenadas geocentricas cartesianas no datum 1 */
	$equad1 = 2.* $achat - pow((double)1 * $achat,(double)2);
	$n1 = $semi_eixo/sqrt((double)1-$equad1*pow(sin($lat1),(double)2));
	$x1 = ($n1+$h1)*cos($lat1)*cos($lon1);
	$y1 = ($n1+$h1)*cos($lat1)*sin($lon1);
	$z1 = ($n1*(1-$equad1)+$h1)*sin($lat1);
	
	
	 
	/* calcula coordenadas geocentricas cartesianas no datum 2 */
	$x2 = $x1 + ($deltax - $deltax2);
	$y2 = $y1 + ($deltay - $deltay2);
	$z2 = $z1 + ($deltaz - $deltaz2);
	
	//echo "x1 = $x1 y1 = $y1 z1 = $z1 <br>";
	//echo "x2 = $x2 y2 = $y2 z2 = $z2 <br>";
	

	/* calcula coordenadas geodesicas no datum 2 */
	$equad2  = 2.* $achat2 - pow((double)1 * $achat2,(double)2);
	$lat2 = $lat1;
	do
	{
		$n2 = $semi_eixo2/sqrt((double)1-$equad2*pow(sin($lat2),(double)2));
		$lat2 = atan(($z2 + $n2*$equad2*sin($lat2))/sqrt($x2*$x2 + $y2*$y2));
		$d = $semi_eixo2/sqrt((double)1-$equad2*pow(sin($lat2),(double)2))- $n2;
	}
	while (abs($d) > 0.00000000001);
	$lon2 = atan($y2/$x2);
	$h2 = $h1;
	//echo "($semi_eixo,$achat,$deltax,$deltay,$deltaz,$lat1,$lon1,$h1,$semi_eixo2,$achat2,$deltax2,$deltay2,$deltaz2,&$lat2,&$lon2,&$h2)";
}

/********************************************************************
 geo_to_gauss				       
 Autor		: Julio Cesar Lima d'Alge		       ago-88
 Resumo		: transforma coordenadas geodesicas em coordenadas
 Conversao p/ Php	: Luis Maurano						 2020
 Entradas	: 
	  semi_eixo	semi_eixo maior do elipsoide (datum entrada)
		achat		achatamento do elipsoide (datum entrada)
		lat		coordenadas geodesicas (em radianos)
		lon		coordenadas geodesicas (em radianos)
		lon_mc	meridiano central (em radianos)

 Saidas		: x		y coordenadas Gauss (em metros)
********************************************************************/
function geo_to_gauss ($lat,$lon,$lon_mc,$semi_eixo,$achat,&$x,&$y)
{

	$k0 = 1. - (1./1500.);
	$equad = 2.*$achat - pow($achat,(double)2);
	$elinquad = $equad/(1. - $equad);

	$aux1 = $equad*$equad;
	$aux2 = $aux1*$equad;
	$aux3 = sin((double)2*$lat);
	$aux4 = sin((double)4*$lat);
	$aux5 = sin((double)6*$lat);
	$aux6 = (1. - $equad/4. - 3.*$aux1/64. - 5.*$aux2/256.)*$lat;
	$aux7 = (3.*$equad/8. + 3.*$aux1/32. + 45.*$aux2/1024.)*$aux3;
	$aux8 = (15.*$aux1/256. + 45.*$aux2/1024.)*$aux4;
	$aux9 = (35.*$aux2/3072.)*$aux5;

	$n = $semi_eixo/sqrt((double)1-$equad*pow(sin($lat),(double)2));
	$t = pow(tan($lat),(double)2);
	$c = $elinquad*pow(cos($lat),(double)2);
	$ag = ($lon-$lon_mc)*cos($lat);
	$m = $semi_eixo*($aux6 - $aux7 + $aux8 - $aux9);

	$aux10 = (1.-$t+$c)*$ag*$ag*$ag/6.;
	$aux11 = (5.-18.*$t+$t*$t+72.*$c-58.*$elinquad)*(pow($ag,(double)5))/120.;
	$aux12 = (5.-$t+9.*$c+4.*$c*$c)*$ag*$ag*$ag*$ag/24.;
	$aux13 = (61.-58.*$t+$t*$t+600.*$c-330.*$elinquad)*(pow($ag,(double)6))/720.;

	$x = 500000. + $k0*$n*($ag + $aux10 + $aux11);
  $y = 5000000. + $k0*($m + $n*tan($lat)*($ag*$ag/2. + $aux12 + $aux13));

}
/********************************************************************
		gauss_to_geo				       
 Autor		: Julio Cesar Lima d'Alge		       ago-88
 Resumo		: transforma coordenadas Gauss em coordenadas
		  geodesicas
 Conversao p/ Php	: Luis Maurano						 2020
 Entradas	: 
 		x		coordenadas Gauss (em metros)
		y   coordenadas Gauss (em metros)
		lon_mc	meridiano central (em radianos)
		semi_eixo	semi_eixo maior do elipsoide (datum entrada)
		achat		achatamento do elipsoide (datum entrada)
		
 Saidas		: lat	lon 	coordenadas geodesicas (em radianos)
********************************************************************/

function gauss_to_geo ($x,$y,$lon_mc,$semi_eixo,$achat,&$lat,&$lon)
{
	$k0 = 1. - (1./1500.);
	$equad = 2.*$achat - pow($achat,(double)2);
	$elinquad = $equad/(1. - $equad);
	$e1 = (1.-sqrt((double)1-$equad))/(1.+sqrt((double)1-$equad));

	$aux1 = $equad*$equad;
	$aux2 = $aux1*$equad;
	$aux3 = $e1*$e1;
	$aux4 = $e1*$aux3;
	$aux5 = $aux4*$e1;

	$m = ($y - 5000000.)/$k0;
	$mi = $m/($semi_eixo*(1.-$equad/4.-3.*$aux1/64.-5.*$aux2/256.));

	$aux6 = (3.*$e1/2. - 27.*$aux4/32.)*sin((double)2*$mi);
	$aux7 = (21.*$aux3/16. - 55.*$aux5/32.)*sin((double)4*$mi);
	$aux8 = (151.*$aux4/96.)*sin((double)6*$mi);

	$lat1 = $mi + $aux6 + $aux7 + $aux8;
	$c1 = $elinquad*pow(cos($lat1),(double)2);
	$t1 = pow(tan($lat1),(double)2);
	$n1 = $semi_eixo/sqrt((double)1-$equad*pow(sin($lat1),(double)2));
	$quoc = pow(((double)1-$equad*sin($lat1)*sin($lat1)),(double)3);
	$r1 = $semi_eixo*(1.-$equad)/sqrt($quoc);
	$d = ($x - 500000.)/($n1*$k0);

	$aux9 = (5.+3.*$t1+10.*$c1-4.*$c1*$c1-9.*$elinquad)*$d*$d*$d*$d/24.;
	$aux10 = (61.+90.*$t1+298.*$c1+45.*$t1*$t1-252.*$elinquad-3.*$c1*$c1)*pow($d,(double)6)/720.;
	$aux11 = $d - (1.+ 2.*$t1 + $c1)*$d*$d*$d/6.;
	$aux12 = (5.-2.*$c1+28.*$t1-3.*$c1*$c1+8.*$elinquad+24.*$t1*$t1)*pow($d,(double)5)/120.;

	$lat = $lat1 - ($n1*tan($lat1)/$r1)*($d*$d/2. - $aux9 + $aux10);
	$lon = $lon_mc + ($aux11 + $aux12)/cos($lat1);

}

/********************************************************************
 define_mer_cent			       	       
 Autor		: Julio Cesar Lima d'Alge		       fev-90
 Conversao p/ Php	: Luis Maurano						 2008
 Resumo		: calcula meridianos centrais dos fusos UTM e GAUSS
 a partir da longitude geodesica
 Entradas	: lon (em grau decimal)
 Saidas		: mc1_utm,mc2_utm,mc1_gauss,mc2_gauss
 Obs.		: os dois valores para UTM e Gauss referem-se 'as
 situacoes de bordas de fusos
********************************************************************/
function define_mer_cent($lon,&$mc1_utm,&$mc2_utm)
{
// calcula MC
	if ($lon != 0.)
		$sinal = (int)($lon/abs($lon));
	else
	{
		$mc1_utm = 3.;
		$mc2_utm = -3.;
	}

	for ($k = $ind1 = 0, $ind2 = 6; abs($lon) > (double)$ind2; $k++, $ind1 = 6*$k, $ind2 = $ind1 + 6);
		$mc = $ind1 + 3.;

	if ((abs($lon) < (double)$mc) && ($lon != 0.))
	{
		$mc1_utm = $mc2_utm = (double)$mc;
	}

	if (((abs($lon) > (double)$mc)) && (abs($lon) != ((double)$mc + 3.)))
	{
		$mc1_utm = $mc2_utm = (double)$mc;
	}

	if ((abs($lon) == ((double)$mc + 3.)) && (abs($lon) != 180.))
	{
		$mc1_utm = (double)$mc;
		$mc2_utm = (double)$mc + 6.;
	}

	if ($lon == 180.)
	{
		$mc1_utm = 177.;
		$mc2_utm = -177.;
	}

	if ($lon == -180.)
	{
		$mc1_utm = -177.;
		$mc2_utm = 177.;
	}

	if (abs($lon) == (double)$mc)
	{
		$mc1_utm = $mc2_utm = (double)$mc;
	}

	if (($lon != 0.) && (abs($lon) != 180))
	{
		$mc1_utm *= $sinal;
		$mc2_utm *= $sinal;
	}
}

?>