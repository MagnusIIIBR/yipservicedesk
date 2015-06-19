﻿<?php 
 /*                        Copyright 2005 Flávio Ribeiro

         This file is part of OCOMON.

         OCOMON is free software; you can redistribute it and/or modify
         it under the terms of the GNU General Public License as published by
         the Free Software Foundation; either version 2 of the License, or
         (at your option) any later version.

         OCOMON is distributed in the hope that it will be useful,
         but WITHOUT ANY WARRANTY; without even the implied warranty of
         MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
         GNU General Public License for more details.

         You should have received a copy of the GNU General Public License
         along with Foobar; if not, write to the Free Software
         Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
  */session_start();
	include ("../../includes/include_geral.inc.php");
	include ("../../includes/include_geral_II.inc.php");
	include ('includes/header.php');

	$_SESSION['s_page_invmon'] = $_SERVER['PHP_SELF'];

	$cab = new headers;
	$cab->set_title(TRANS('TTL_INVMON'));

	$auth = new auth;
	$auth->testa_user($_SESSION['s_usuario'],$_SESSION['s_nivel'],$_SESSION['s_nivel_desc'],2);

	$hoje = date("Y-m-d H:i:s");


	$cor  = TD_COLOR;
	$cor1 = TD_COLOR;
	$cor3 = BODY_COLOR;

	$queryInst = "SELECT * from instituicao order by inst_nome";
	$resultadoInst = mysql_query($queryInst);
	$linhasInst = mysql_num_rows($resultadoInst);		

		$saida="";
		if (isset ($_POST['instituicao'])) {
			for ($i=0; $i<count($_POST['instituicao']); $i++){
				$saida.= $_POST['instituicao'][$i].",";
			}
		}
		if (strlen($saida)>1) {
			$saida = substr($saida,0,-1);
		}

		$msgInst = "";
		if (($saida=="")||($saida=="-1")) {
			$clausula = "";
			$clausula2 = "";
			$msgInst = "".TRANS('ALL')."";
		} else {
			$sqlA ="select inst_nome as inst from instituicao where inst_cod in (".$saida.")";
			$resultadoA = mysql_query($sqlA);
			while ($rowA = mysql_fetch_array($resultadoA)) {
				$msgInst.= $rowA['inst'].', ';
			}
			$msgInst = substr($msgInst,0,-2);

			$clausula = " and comp_inst in (".$saida.")";
			$clausula2 = " and comp_inst in (".$saida.") ";
		}

		$queryB = "SELECT count(*) from equipamentos  where comp_tipo_equip in (1,2) ".$clausula."";
		$resultadoB = mysql_query($queryB);
		$total = mysql_result($resultadoB,0);

		// Select para retornar a quantidade e percentual de equipamentos cadastrados no sistema
		$query= "select count(*) as qtd, t.tipo_nome as equipamento, t.tipo_cod as tipo, h.mdit_cod as tipo_hd,
				concat(h.mdit_fabricante,' ',h.mdit_desc_capacidade,'GB') as hd, concat(count(*)/".$total."*100,'%') as porcento from
				equipamentos, tipo_equip as t, modelos_itens as h where
				comp_tipo_equip = t.tipo_cod and comp_modelohd = h.mdit_cod and h.mdit_tipo=1 ".$clausula2."
				group by hd order by qtd desc, hd";

		$resultado = mysql_query($query);
		$linhas = mysql_num_rows($resultado);

		print "<TABLE border='0' cellpadding='5' cellspacing='0' align='left' width='80%' bgcolor='".$cor3."'>";
			print "<tr><td width='80%' align='center'><b>".TRANS('TTL_QTD_COMP_FOR_HD')."<p>".TRANS('OCO_FIELD_UNIT').": ".$msgInst.".</p></b></td></tr>";

		print "<td class='line'>";
		print "<fieldset><legend>".TRANS('TTL_COMP_X_HD')."</legend>";
		print "<TABLE border='0' cellpadding='5' cellspacing='0' align='center' width='80%' bgcolor='".$cor3."'>";
			print "<TR><TD bgcolor='".$cor3."'><b>".TRANS('MNL_CAD_EQUIP')."</TD><TD bgcolor='".$cor3."'><b>".TRANS('MNL_HD')."</TD><TD bgcolor='".$cor3."'><b>".TRANS('COL_QTD')."</TD><TD bgcolor='".$cor3."'><b>".TRANS('COL_PORCENTEGE')."</TD>";

		print "<td rowspan='100%'><div id='Layer2''>";//  <!-- Ver: overflow: auto    não funciona para o Mozilla-->
		print "<b>".TRANS('OCO_FIELD_UNIT').":</font></font></b>";
		print "<FORM name='form1' method='post' action='".$_SERVER['PHP_SELF']."'>";
		$sizeLin = $linhasInst+1;
		print "<select name='instituicao[]' size='".$sizeLin."' multiple='yes'>";

		print "<option value='-1' selected>".TRANS('ALL')."</option>";
		while ($rowInst = mysql_fetch_array($resultadoInst))
		{
			print "<option value='".$rowInst['inst_cod']."'>".$rowInst['inst_nome']."</option>";
		}
		print "</select>";
		print "<input type='submit' class='button' value='".TRANS('BT_APPLY')."' name='OK'>";

		print "</form>";
		print "</div></td>";

		print "</tr>";

		$i=0;
		$j=2;
		$totalFull = 0;

		while ($row = mysql_fetch_array($resultado)) {
			$color =  BODY_COLOR;
			$totalFull+=$row['qtd'];
			$j++;
			print "<TR>";
			print "<TD bgcolor='".$color."'>".$row['equipamento']."</TD>";
			print "<TD bgcolor='".$color."'><a href='mostra_consulta_comp.php?comp_tipo_equip=".$row['tipo']."&comp_modelohd=".$row['tipo_hd']."&ordena=local,etiqueta' title='".TRANS('HNT_LIST_COMP_CAD_MODEL_HD')."'>".$row['hd']."</a></TD>";
			print "<TD bgcolor='".$color."'>".$row['qtd']."</TD>";
			print "<TD bgcolor='".$color."'>".$row['porcento']."</TD>";
			print "</TR>";
			$i++;
		}

        	print "<TR><TD bgcolor='".$cor3."'><b></TD><TD bgcolor='".$cor3."'><b></TD><TD bgcolor='".$cor3."'><b>".TRANS('TOTAL').": <font color='red'>".$totalFull."</font></TD><TD bgcolor='".$cor3."'></TD></tr>";
		print "</TABLE>";
		print "</fieldset>";

		print "<TABLE width='80%' align='center'>";
		// print "<tr><td class='line'></TD></tr>";
		// print "<tr><td class='line'></TD></tr>";
		// print "<tr><td class='line'></TD></tr>";
		// print "<tr><td class='line'></TD></tr>";
		// print "</TABLE>";

		// print "<TABLE width='80%' align='center'>";
		// print "<tr><td class='line'></TD></tr>";
		// print "<tr><td class='line'></TD></tr>";
		// print "<tr><td class='line'></TD></tr>";
		// print "<tr><td class='line'></TD></tr>";

		print "<tr><td width='80%' align='center'><b>".TRANS('SLOGAN_OCOMON')." <a href='http://www.yip.com.br' target='_blank'>".TRANS('COMPANY')."</a>.</b></td></tr>";
		print "</TABLE>";

print "</BODY>";
print "</HTML>";
?>
