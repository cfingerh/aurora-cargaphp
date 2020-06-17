<?php
session_start();



function getDetDatosProc($_codP, $_codDiv, $_estado){
	$res=$where=null;
	include('connect.php');
	if($_codP!=''){
		$where=' sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO" = \''.$_codP.'\'';
	}
	if($_codDiv!=''){
		$where=' sgdp."SGDP_PROCESOS"."ID_UNIDAD" = '. $_codDiv;
	}
	if($_codDiv!='' && $_codP!=''){
		$where=' sgdp."SGDP_PROCESOS"."ID_UNIDAD" = '. $_codDiv.' AND sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO" = \''.$_codP.'\'';
	}
	if($_estado!=''){
		$where.=' AND sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" = '. $_estado;
	}
	
	$sql='SELECT
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO",
			sgdp."SGDP_ESTADOS_DE_PROCESOS"."A_NOMBRE_ESTADO_DE_PROCESO",
			sgdp."SGDP_PROCESOS"."ID_PROCESO",
			sgdp."SGDP_PROCESOS"."A_NOMBRE_PROCESO",
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."A_NOMBRE_EXPEDIENTE",
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_INICIO",
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_FIN",
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_VENCIMIENTO",
			( EXTRACT ( DAY FROM ( sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_FIN" - sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_INICIO" )) ) AS dura,
			( EXTRACT ( DAY FROM ( sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_FIN" - sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_VENCIMIENTO" )) ) AS desv ,
			sgdp."SGDP_MACRO_PROCESOS"."A_NOMBRE_MACRO_PROCESO",
			sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO"
		FROM
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"
			INNER JOIN sgdp."SGDP_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO" = sgdp."SGDP_PROCESOS"."ID_PROCESO"
			INNER JOIN sgdp."SGDP_ESTADOS_DE_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" = sgdp."SGDP_ESTADOS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" 
			INNER JOIN sgdp."SGDP_MACRO_PROCESOS" ON sgdp."SGDP_PROCESOS"."ID_MACRO_PROCESO" = sgdp."SGDP_MACRO_PROCESOS"."ID_MACRO_PROCESO"
		WHERE
			'.$where.' 
		ORDER BY
			sgdp."SGDP_PROCESOS"."ID_PROCESO" ASC';

	$result=pg_query($dbconn, $sql);
	$res='<table id="example" class="display">
			<thead>
			<tr>	
				<th>C&oacute;digo</th>
				<th>Proceso</th>
				<th>Subproceso</th>
				<th>Versi&oacute;n</th>
				<th>Estado</th>
				<th>Expediente</th>
				<th>Fecha Inicio</th>
				<th>Fecha Fin</th>
				<th>Duraci&oacute;n</th>
				<th>Desviaci&oacute;n plazo</th>
				<th>t m&aacute;x Tarea</th>	
				<th>Tareas ejecutadas</th>
				<th>Tareas avanzadas</th>	
				<th>&Iacute;ndice avance</th>			
			</tr>
			</thead>';
	$contDiv=0;
	$totDiv=0;
	$div=$codPAux=null;
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		
		$desv=$row['desv'];
		$fFin=$row['D_FECHA_FIN'];
		$fVen=$row['D_FECHA_VENCIMIENTO'];
		if($fFin==''){
			$fFin=date('Y-m-d H:i:s');
			$temp1=strtotime($fVen); 
			
			$temp2=strtotime($fFin); 
			$diferencia= $temp1-$temp2;
			$desv=floor($diferencia/60/60/24);
		}
		
		$color='';
		$datTar=getDatosTareaProceso($row['ID_INSTANCIA_DE_PROCESO']);
		if($fFin<=$fVen){
			$color='#7FD570';				
		}else{
			$color='#FFB0B0';
		}
		if($row['A_NOMBRE_ESTADO_DE_PROCESO']=='ANULADO' || $row['A_NOMBRE_ESTADO_DE_PROCESO']=='ASIGNADO'){
			//$color='';
		}	
		$porcTar=0;
		
		if($row['dura']!=0){
			$porcTar=round(($datTar[1]/$row['dura']),2)*100;
		}
		
		$res.='<tr>
					<td>'.$row['A_CODIGO_PROCESO'].'</td>
					<td>'.$row['A_NOMBRE_MACRO_PROCESO'].'</td>
					<td>'.$row['A_NOMBRE_PROCESO'].'</td>
					<td>'.$row['ID_PROCESO'].'</td>
					<td>'.$row['A_NOMBRE_ESTADO_DE_PROCESO'].'</td>
					<td>'.$row['A_NOMBRE_EXPEDIENTE'].'</td>
					<td>'.$row['D_FECHA_INICIO'].'</td>
					<td>'.$fFin.'</td>
					<td>'.$row['dura'].'</td>
					<td style="cursor: help; background:'.$color.'" title="Fecha Vencimiento: '.$fVen.'">'.$desv.'</td>
					<td style="cursor: help" title="'.$porcTar.'% del tiempo total - Tarea: '.$datTar[7].'">'.$datTar[1].'</td>
					<td>'.$datTar[2].'</td>
					<td>'.$datTar[3].'</td>
					<td>'.(round(($datTar[3]/$datTar[2]),1)*100).'%</td>
				</tr>';/**/
	
	
	}
	$res.='<tfoot>
			<tr>	
				<th>C&oacute;digo</th>
				<th>Proceso</th>
				<th>Subproceso</th>
				<th>Versi&oacute;n</th>
				<th>Estado</th>
				<th>Expediente</th>
				<th>Fecha Inicio</th>
				<th>Fecha Fin</th>
				<th>Duraci&oacute;n</th>
				<th>Desviaci&oacute;n plazo</th>	
				<th>Tarea t m&aacute;x</th>	
				<th>Tareas ejecutadas</th>
				<th>Tareas avanzadas</th>	
				<th>&Iacute;ndice avance</th>					
			</tr>
			</tfoot>';
	//$fp = fopen("cache/getDatosP.ch", "w");
	//fputs($fp, $res);
	//fclose($fp);
	return $res;	
}

function getDatosTareaProceso($_idInsProc){
	$res=$nomT=null;
	include('connect.php');
	$sql='SELECT
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA",
			sgdp."SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_ASIGNACION",
			sgdp."SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_FINALIZACION",
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA",
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN",
			sgdp."SGDP_TAREAS"."A_NOMBRE_TAREA",
			sgdp."SGDP_TAREAS"."N_DIAS_HABILES_MAX_DURACION",
			( EXTRACT ( DAY FROM ( sgdp."SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_FINALIZACION" - sgdp."SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_ASIGNACION" )) ) AS dura 
		FROM
			sgdp."SGDP_INSTANCIAS_DE_TAREAS"
			INNER JOIN sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS" ON sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
			INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA" 
WHERE
	sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" = '.$_idInsProc.' 
			AND sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" IN ( 2, 3, 6, 7 )
			AND sgdp."SGDP_TAREAS"."B_ESPERAR_RESP" = false';
	$result=pg_query($dbconn, $sql);
	$max=$min=$tAvan=$tRet=$tfin=$tfin=0;
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);	
		if($row['dura']>$max){
			$max=$row['dura'];
			$nomT=$row['A_NOMBRE_TAREA'].' [plazo '.$row['N_DIAS_HABILES_MAX_DURACION'].' d&iacute;as] ('.$row['ID_USUARIO_ORIGEN'].')';
		}
		if($row['dura']<$min){
			$min=$row['dura'];
		}
		if($row['ID_ACCION_HISTORICO_INST_DE_TAREA']==2){
			$tRet++;
		}
		if($row['ID_ACCION_HISTORICO_INST_DE_TAREA']==3){
			$tAvan++;
		}
		if($row['ID_ACCION_HISTORICO_INST_DE_TAREA']==6){
			$tfin++;
		}
		if($row['ID_ACCION_HISTORICO_INST_DE_TAREA']==7){
			$tanul++;
		}
	}
	$res=array($min, $max, $i, $tAvan, $tRet, $tfin, $tanul, $nomT);
	return $res;

}

function getDatosProc(){
	$res=null;
	include('connect.php');
	
	$sql='SELECT DISTINCT
			t1."A_NOMBRE_PROCESO",
			t1."A_CODIGO_PROCESO",
			( SELECT COUNT ( sgdp."SGDP_PROCESOS"."ID_PROCESO" ) FROM sgdp."SGDP_PROCESOS" WHERE sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO" = t1."A_CODIGO_PROCESO" ) AS contv,
			sgdp."SGDP_MACRO_PROCESOS"."A_NOMBRE_MACRO_PROCESO" 
		FROM
			sgdp."SGDP_PROCESOS" AS t1
			INNER JOIN sgdp."SGDP_MACRO_PROCESOS" ON t1."ID_MACRO_PROCESO" = sgdp."SGDP_MACRO_PROCESOS"."ID_MACRO_PROCESO" 
		ORDER BY
			t1."A_CODIGO_PROCESO" ASC,
			t1."A_NOMBRE_PROCESO" ASC,
			sgdp."SGDP_MACRO_PROCESOS"."A_NOMBRE_MACRO_PROCESO" ASC';
	$result=pg_query($dbconn, $sql);
	$res='<table id="example" class="display">
			<thead>
			<tr>	
				<th>C&oacute;digo</th>
				<th>Proceso</th>
				<th>Subproceso</th>
				<th>Versiones</th>
				<th>Total iniciados</th>
				<th>Pendientes</th>
				<th>Anulados</th>
				<th>Fin dentro de plazo</th>
				<th>Total Tareas ejecutadas</th>
				<th>Duraci&oacute;n promedio</th>				
			</tr>
			</thead>';
	$contDiv=0;
	$totDiv=0;
	$div=$codPAux=null;
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);	
		
		//evita que se repita el nombre
		if($row['A_CODIGO_PROCESO']==$codPAux){
			continue;
		}
		$codPAux=$row['A_CODIGO_PROCESO'];
		$rat=getFinDentroPlazo($row['A_CODIGO_PROCESO']);
		//$tar=getTarDentroPlazo($row['A_CODIGO_PROCESO']);
		$tProm=getTiempoProm($row['A_CODIGO_PROCESO']);
		$ejec=getTarEjec($row['A_CODIGO_PROCESO']);
		$tarTot=getTarTot($row['A_CODIGO_PROCESO']);
		$porcEjec=0;
		
		$text='';
		$procTot=getProcIniT($row['A_CODIGO_PROCESO']);
		$contPEspera=getContProcEspera($row['A_CODIGO_PROCESO']);
		//cuenta pendientes
		$contPPend=$contPAnul=0;
		foreach($procTot as $val){
			$text.=key($val).': '.$val[key($val)]. ' - ';
			if(key($val)=='ASIGNADO'){
				$contPPend=$val[key($val)];
			}
			if(key($val)=='ANULADO'){
				$contPAnul=$val[key($val)];
			}
		}	
		/*
		$res.='<tr>
					<td>'.$row['A_CODIGO_PROCESO'].'</td>
					<td>'.$row['A_NOMBRE_MACRO_PROCESO'].'</td>
					<td>'.$row['A_NOMBRE_PROCESO'].'</td>
					<td>'.$row['contv'].'</td>
					<td>'.$contPPend.'</td>	
					<td title="'.$text.'" >'.$procTot[count($procTot)-1]['TOTAL'].'</td>
					<td style="cursor: help" title="Dentro de plazo: '.$rat[0].' - Fuera de plazo: '.$rat[1].' - Total: '.$rat[3].'">'.$rat[2].'%</td>
					<td style="cursor: help" title="Dentro de plazo: '.$tar[0].' - Fuera de plazo: '.$tar[1].' - Total: '.$tar[3].' - Retraso promedio: '.$tar[4].' d&iacute;as">'.$tar[2].'%</td>
					<td style="cursor: help" title="Avanzadas: '.$ejec[0].' - Retrocedidas: '.$ejec[1].' - Finalizadas: '.$ejec[2].'">'.$ejec[3].'</td>
					<td>'.$tarTot.'</td>
					<td style="cursor: help" title="'.($porcEjec-100).'% m&aacute;s que el total de tareas definidas">'.$porcEjec.'%</td>
				</tr>';*/
		$res.='<tr>
					<td><a href="detp_nivel1.php?codP='.$row['A_CODIGO_PROCESO'].'" target="_blank">'.$row['A_CODIGO_PROCESO'].'</a></td>
					<td><a href="detp_nivel1.php?codP='.$row['A_CODIGO_PROCESO'].'" target="_blank">'.$row['A_NOMBRE_MACRO_PROCESO'].'</a></td>
					<td><a href="detp_nivel1.php?codP='.$row['A_CODIGO_PROCESO'].'" target="_blank">'.$row['A_NOMBRE_PROCESO'].'</a></td>
					<td><a href="detp_nivel1.php?codP='.$row['A_CODIGO_PROCESO'].'" target="_blank">'.$row['contv'].'</a></td>
					<td style="cursor: help" title="'.$text.'" ><a href="detp_nivel1.php?codP='.$row['A_CODIGO_PROCESO'].'" target="_blank">'.$procTot[count($procTot)-1]['TOTAL'].'</a></td>
					<td style="cursor: help" title="En espera: '.$contPEspera.' - Total: '.$contPPend.'">'.($contPPend-$contPEspera).'</a></td>	
					<td><a href="detp_nivel1.php?codP='.$row['A_CODIGO_PROCESO'].'" target="_blank">'.$contPAnul.'</a></td>	
					<td style="cursor: help" title="Dentro de plazo: '.$rat[0].' - Fuera de plazo: '.$rat[1].' - Total: '.$rat[3].'"><a href="detp_nivel1.php?codP='.$row['A_CODIGO_PROCESO'].'" target="_blank">'.$rat[2].'%</a></td>
					<td style="cursor: help" title="Avanzadas: '.$ejec[0].' - Retrocedidas: '.$ejec[1].' - Finalizadas: '.$ejec[2].'"><a href="detp_nivel1.php?codP='.$row['A_CODIGO_PROCESO'].'" target="_blank">'.$ejec[3].'</a></td>
					<td style="cursor: help" title="t m&iacute;nimo: '.$tProm[0].' d&iacute;as - t m&aacute;ximo: '.$tProm[1].' d&iacute;as  - &sigma;: '.$tProm[3].' d&iacute;as"><a href="detp_nivel1.php?codP='.$row['A_CODIGO_PROCESO'].'" target="_blank">'.$tProm[2].'</a></td>
				</tr>';
	
	
	}
	$res.='<tfoot>
			<tr>	
				<th>C&oacute;digo</th>
				<th>Proceso</th>
				<th>Subproceso</th>
				<th>Versiones</th>
				<th>Total iniciados</th>
				<th>Pendientes</th>
				<th>Anulados</th>
				<th>Fin dentro de plazo</th>
				<th>Total Tareas ejecutadas</th>
				<th>Duraci&oacute;n promedio</th>					
			</tr>
			</tfoot>';
	//$fp = fopen("cache/getDatosP.ch", "w");
	//fputs($fp, $res);
	//fclose($fp);
	
	return $res;	

}

function getContProcEspera($_codP){
	include('connect.php');
	$tot=0;
	$sql='SELECT COUNT
	( sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" ) 
FROM
	sgdp."SGDP_INSTANCIAS_DE_TAREAS"
	INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA"
	INNER JOIN sgdp."SGDP_PROCESOS" ON sgdp."SGDP_TAREAS"."ID_PROCESO" = sgdp."SGDP_PROCESOS"."ID_PROCESO" 
WHERE
	sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_ESTADO_DE_TAREA" = 2 
	AND sgdp."SGDP_TAREAS"."B_ESPERAR_RESP" = true
	AND sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO" = \''.$_codP.'\'';
	$result=pg_query($dbconn, $sql);
	$row = pg_fetch_array($result, $i);
	return $row['count'];
	
}

function getTiempoProm($_codP){
	include('connect.php');
	$prom=$min=$max=$devS=0;
	$res=$duracion=null;
	$sql='	
		SELECT
		sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO",
		( EXTRACT ( DAY FROM ( sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_FIN" - sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_VENCIMIENTO" )) ) AS en_plazo,
		( EXTRACT ( DAY FROM ( sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_FIN" - sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_INICIO" )) ) AS dura 
	FROM
		sgdp."SGDP_INSTANCIAS_DE_PROCESOS"
		INNER JOIN sgdp."SGDP_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO" = sgdp."SGDP_PROCESOS"."ID_PROCESO" 
	WHERE
	  sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" = 3 AND
		sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO" = \''.$_codP.'\' 
	';
	
	$result=pg_query($dbconn, $sql);
	$tot=0;
	
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$prom+=$row['dura'];
		$plazos[]=$row['en_plazo'];
		$duracion[]=$row['dura'];
	}
	if($i!=0){
		$prom=round(($prom/$i),1);
		$min=min($duracion);
		$max=max($duracion);
	}
	
	$devS=devStandar($duracion);
	
	$res= array($min,$max, $prom, $devS);
	return $res;
	
}

function devStandar($_nums){
	if(count($_nums)<1){
		return 0;
	}
	$nums = $_nums;
	$sum=0;
	for($i=0;$i<count($nums);$i++){
		$sum+=$nums[$i];
	}
	$media = $sum/count($nums);
	$sum2=0;
	for($i=0;$i<count($nums);$i++){
		$sum2+=($nums[$i]-$media)*($nums[$i]-$media);
	}
	$vari = $sum2/count($nums);
	$sq = sqrt($vari);
	return round($sq,1);

}

function getProcIniT($_codP){
	include('connect.php');
	$res=null;
	
	$sql='SELECT
		Count(sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO"),
		sgdp."SGDP_ESTADOS_DE_PROCESOS"."A_NOMBRE_ESTADO_DE_PROCESO"
	FROM
		sgdp."SGDP_INSTANCIAS_DE_PROCESOS"
		INNER JOIN sgdp."SGDP_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO" = sgdp."SGDP_PROCESOS"."ID_PROCESO"
		INNER JOIN sgdp."SGDP_ESTADOS_DE_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" = sgdp."SGDP_ESTADOS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO"
	WHERE
		sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO" = \''.$_codP.'\'
	GROUP BY
		sgdp."SGDP_ESTADOS_DE_PROCESOS"."A_NOMBRE_ESTADO_DE_PROCESO"
	ORDER BY
		sgdp."SGDP_ESTADOS_DE_PROCESOS"."A_NOMBRE_ESTADO_DE_PROCESO" ASC';
	
	$result=pg_query($dbconn, $sql);
	$tot=0;
	
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$res[$i]= array($row['A_NOMBRE_ESTADO_DE_PROCESO']=>$row['count']);
		$tot+=$row['count'];
	}
	$res[$i]=array('TOTAL' => $tot);
	return $res;

}

function getTarTot($_codP){
	include('connect.php');
	$tot=0;
	$sql='SELECT  COUNT
				( DISTINCT sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" ) 
			FROM
				sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"
				INNER JOIN sgdp."SGDP_INSTANCIAS_DE_TAREAS" ON sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
				INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA"
				INNER JOIN sgdp."SGDP_PROCESOS" ON sgdp."SGDP_TAREAS"."ID_PROCESO" = sgdp."SGDP_PROCESOS"."ID_PROCESO"
				INNER JOIN sgdp."SGDP_ACCIONES_HIST_INST_DE_TAREAS" ON sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = sgdp."SGDP_ACCIONES_HIST_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" 
			WHERE
				sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" NOT IN ( 1, 7 ) 
				AND sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO" =  \''.$_codP."'";

	$result=pg_query($dbconn, $sql);
	$row = pg_fetch_array($result, 0);	
	$tot=$row['count'];	
	return $tot;

}

function getTarEjec($_codP){
	include('connect.php');
	$tot=$env=$dev=$fin=0;
	$res=null;
	$sql='SELECT COUNT
				( sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA" ),
				sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" 
			FROM
				sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"
				INNER JOIN sgdp."SGDP_INSTANCIAS_DE_TAREAS" ON sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
				INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA"
				INNER JOIN sgdp."SGDP_PROCESOS" ON sgdp."SGDP_TAREAS"."ID_PROCESO" = sgdp."SGDP_PROCESOS"."ID_PROCESO" 
			WHERE
				sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" NOT IN (1,7) AND
				sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO" = \''.$_codP.'\'
			GROUP BY
				sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA"';

	$result=pg_query($dbconn, $sql);
	
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);	
		if($row['ID_ACCION_HISTORICO_INST_DE_TAREA']=='3'){
			$env+=$row['count'];
		}
		if($row['ID_ACCION_HISTORICO_INST_DE_TAREA']=='2'){
			$dev+=$row['count'];
		}
		if($row['ID_ACCION_HISTORICO_INST_DE_TAREA']=='6'){
			$fin+=$row['count'];
		}
		/*$sql2='SELECT
					sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO" 
				FROM
					sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS" 
				WHERE
					sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = '..' 
					AND sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO" > \''..'\' 
				ORDER BY
					sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO" ASC 
					LIMIT 1';
		$result2=pg_query($dbconn, $sql2);
		for($j=0;$i<pg_num_rows($result2);$j++){
			
		}*/
		
	}	
	$tot=$env+$dev+$fin;
	$res=array($env,$dev,$fin,$tot);	
	return $res;
}

function getTarDentroPlazo($_codP){
	include('connect.php');
	$contIn=$contOut=$rat=$tot=0;
	$sql='SELECT
			sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA",
			sgdp."SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_FINALIZACION",
			sgdp."SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_ANULACION",
			sgdp."SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_VENCIMIENTO"
		FROM
			sgdp."SGDP_INSTANCIAS_DE_TAREAS"
			INNER JOIN sgdp."SGDP_ESTADOS_DE_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_ESTADO_DE_TAREA" = sgdp."SGDP_ESTADOS_DE_TAREAS"."ID_ESTADO_DE_TAREA"
			INNER JOIN sgdp."SGDP_INSTANCIAS_DE_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" = sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO"
			INNER JOIN sgdp."SGDP_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO" = sgdp."SGDP_PROCESOS"."ID_PROCESO" 
		WHERE
			sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_ESTADO_DE_TAREA" IN ( 3 ) 
			AND sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO" = \''.$_codP."'";
	$aux=0;
	$result=pg_query($dbconn, $sql);
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);	
		$fVen=$row['D_FECHA_VENCIMIENTO'];
		$fFin=$row['D_FECHA_FINALIZACION'];
		if($fFin==''){
			continue;
		}
		if($fVen==''){
			$fVen=$row['D_FECHA_ANULACION'];
		}
		
		
		if($fVen>$fFin){
			$temp1=strtotime($fVen); 
			
			$temp2=strtotime($fFin); 
			$diferencia= abs($temp2-$temp1);
			//echo $fFin.'<br>';
			//echo floor($diferencia/60/60/24).'<br>';
			$aux+=floor($diferencia/60/60/24); 
			$contOut++;
		}else{
			$contIn++;
		}	
		$tot++;
	}
	
	if($tot!=0){
		$rat=round(($contIn/$tot),3)*100;
		$aux=round(($aux/$tot),3);
	}
	
	$res = array($contIn, $contOut, $rat, $tot, $aux);
	return $res;
}

function getFinDentroPlazo($_codP){
	include('connect.php');
	
	$contIn=$contOut=$rat=$tot=0;
	$sql='SELECT
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO",
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_INICIO",
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_FIN",
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_VENCIMIENTO",
			sgdp."SGDP_ESTADOS_DE_PROCESOS"."A_NOMBRE_ESTADO_DE_PROCESO",
			sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO",
			sgdp."SGDP_PROCESOS"."N_DIAS_HABILES_MAX_DURACION" 
		FROM
			sgdp."SGDP_PROCESOS"
			INNER JOIN sgdp."SGDP_INSTANCIAS_DE_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO" = sgdp."SGDP_PROCESOS"."ID_PROCESO"
			INNER JOIN sgdp."SGDP_ESTADOS_DE_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" = sgdp."SGDP_ESTADOS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" 
		WHERE
			sgdp."SGDP_PROCESOS"."A_CODIGO_PROCESO" = \''.$_codP.'\' 
			AND sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" = 3 ';
	$result=pg_query($dbconn, $sql);
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);	
		$fVen=$row['D_FECHA_VENCIMIENTO'];
		$fFin=$row['D_FECHA_FIN'];
		
		if($fVen>=$fFin){
			$contIn++;
		}else{
			$contOut++;
		}	
		$tot++;
	}
	if($tot!=0){
		$rat=round(($contIn/$tot),3)*100;
	}else{
		$rat=0;
	}
	
	$res = array($contIn, $contOut, $rat, $tot);
	return $res;
}


function getDatos($_div){
	$idCache=date('i');
	if(($idCache%5)!=0){
		if(file_exists ('cache/getDatos.ch')){
			$cache = file_get_contents('cache/getDatos.ch');
			//return $cache;
		}
		
	}
	
	$res=null;
	include('connect.php');
	$cond=null;
	$contd=getTotalInOutDiv();
	if($_div!=''){
		$cond=', sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD"='.pg_escape_string($_div);
	}
	//obtener usuarios sin asignar

	$sql='SELECT
			t1."ID_USUARIO",
			sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD",
			(
			SELECT COUNT
				( sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" ) 
			FROM
				sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"
				INNER JOIN sgdp."SGDP_INSTANCIAS_DE_TAREAS" ON sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" 
			WHERE
				sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" NOT IN ( 4, 1, 5, 7 ) 
				AND sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN" = t1."ID_USUARIO" 
			) AS tareas_salida,
			(
			SELECT COUNT
				( sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_INSTANCIA_DE_TAREA" ) 
			FROM
				sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"
				INNER JOIN sgdp."SGDP_INSTANCIAS_DE_TAREAS" ON sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_INSTANCIA_DE_TAREA" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
				INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA" 
			WHERE
				sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_USUARIO" = t1."ID_USUARIO" 
				AND sgdp."SGDP_TAREAS"."B_ESPERAR_RESP" = false 
			) AS tareas_bandeja 
		FROM
			sgdp."SGDP_USUARIOS_ROLES" AS t1
			INNER JOIN sgdp."SGDP_UNIDADES" ON t1."ID_UNIDAD" = sgdp."SGDP_UNIDADES"."ID_UNIDAD" 
		WHERE
			t1."ID_USUARIO" NOT IN ( \'user1\', \'dfsgdp\', \'ibesgdp\', \'prtgadmin\', \'isaynsgdp\', \'user2\', \'user3\' ) 
		GROUP BY
			t1."ID_USUARIO",
			sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD" 
		ORDER BY
			sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD" ASC,
			tareas_salida DESC
					';
		
	$temp1=strtotime(date('Y-m-d'));
	
	
	$result=pg_query($dbconn, $sql);
	$res='<table id="example" class="display">
			<thead>
			<tr>	
				<th>Usuario</th>
					<th>In x d&iacute;a</th>
					<th>Out x d&iacute;a</th>
					<th>Tasa In/Out</th>
					<th tittle="Tiempo promedio de respuesta">t de respuesta (d&iacute;as x tarea)</th>
					<th>En bandeja</th>
					<th>&Aacute;rea</th>
			</tr>
			</thead>';
	$contDiv=0;
	$totDiv=0;
	$div=null;
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);		
		$temp2=strtotime(getFechaIni($row['ID_USUARIO'])); 
		$diferencia= abs($temp1-$temp2);
		$dias=floor($diferencia/60/60/24); 
		
		$totIn=$row['tareas_salida']+$row['tareas_bandeja'];
		$inXdia=round(($totIn/$dias),3);
		$outXdia=round(($row['tareas_salida']/$dias),3);
		if($inXdia==0){
			$tasa=0;
		}else{
			$tasa=round((($outXdia/$inXdia)*100),2);
		}
		
		
		$inXdia=round(($totIn/$dias),2);
		$outXdia=round(($row['tareas_salida']/$dias),2);
		$tresp=getTiempoRespPromedio($row['ID_USUARIO']);
		$color=null;
		$color2=null;
		
		switch (true) {
			case ($tasa == 0):
				$color='';
				break;
			case ($tasa >= 95):
				$color='#7FD570';
				break;
			case ($tasa >= 85 && $tasa < 95):
				$color='#A7E89C';
				break;
			case ($tasa >= 80 && $tasa < 85):
				$color='#FFFEB0';
				break;
			case ($tasa >= 70 && $tasa < 80):
				$color='#FFC489';
				break;
			default:
				$color='#FFB0B0';
		}
		
		switch (true) {
			case ($tasa == 0):
				$color2='';
				break;
			case ($tresp <= 1):
				$color2='#7FD570';
				break;
			case ($tresp > 1 && $tresp <= 2):
				$color2='#A7E89C';
				break;
			case ($tresp > 2 && $tresp <= 3):
				$color2='#FFFEB0';
				break;
			case ($tresp > 3 && $tresp <= 5):
				$color2='#FFC489';
				break;
			default:
				$color2='#FFB0B0';
		}
		
		$dVac=round(($row['tareas_bandeja']*$tresp),1);
		
		$res.='
			  <tr>
				<td><a href="det_nivel1.php?idDiv='.$row['A_CODIGO_UNIDAD'].'&tip=p" target="_blank">'.$row['ID_USUARIO'].'</a></td>
				<td title="Total hist&oacute;rico: '.$totIn.'"><a href="det_nivel1.php?idDiv='.$row['A_CODIGO_UNIDAD'].'&tip=p" target="_blank">'.$inXdia.'</a></td>
				<td title="Total hist&oacute;rico: '.$row['tareas_salida'].'"><a href="det_nivel1.php?idDiv='.$row['A_CODIGO_UNIDAD'].'&tip=f" target="_blank">'.$outXdia.'</a></td>
				<td style="background:'.$color.'" >'.$tasa.'%</td>
				<td title="'.$tresp.' d&iacute;as por tarea" style="background:'.$color2.'">'.$tresp.'</td>
				<td title="Necesita '.$dVac.' d&iacute;as para vaciar bandeja"><a href="det_nivel1.php?idDiv='.$row['A_CODIGO_UNIDAD'].'&tip=p" target="_blank">'.$row['tareas_bandeja'].'</a></td>
				<td><a href="det_nivel1.php?idDiv='.$row['A_CODIGO_UNIDAD'].'&tip=p" target="_blank">'.$row['A_CODIGO_UNIDAD'].'</a></td>
			  </tr>
			';
		$totDiv+=$row['tareas_ejec'];
	}
	
	$res.='<tfoot>
			<tr>	
				<th>Usuario</th>
				<th>In x d&iacute;a</th>
				<th>Out x d&iacute;a</th>
				<th>Tasa In/Out</th>
				<th tittle="Tiempo promedio de respuesta">t de respuesta (d&iacute;as x tarea)</th>
				<th>En bandeja</th>
				<th>&Aacute;rea</th>
			</tr>
			</tfoot>';
	$fp = fopen("cache/getDatos.ch", "w");
	fputs($fp, $res);
	fclose($fp);
	
	return $res;	
}

function getFechaIni($_user){
	include('connect.php');
	$sql='SELECT
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO",
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN" 
		FROM
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS" 
		WHERE
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN" = \''.$_user.'\'
		ORDER BY
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO" ASC 
			LIMIT 1';
	$result=pg_query($dbconn, $sql);
	if(pg_num_rows($result)<1){
		return '2017-08-01';
	}
	$row = pg_fetch_array($result, 0);
	return $row['D_FECHA_MOVIMIENTO'];

}

function getDatosTramo($_div, $_tipoT){
	$res=null;
	include('connect.php');
	$cond=null;
	$contd=getTotalInOutDiv();
	if($_div!=''){
		$cond=' AND sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD"=\''.pg_escape_string($_div).'\'';
	}
	$sql='SELECT DISTINCT
			sgdp."SGDP_USUARIOS_ROLES"."ID_USUARIO",
			sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD" 
		FROM
			sgdp."SGDP_USUARIOS_ROLES"
			INNER JOIN sgdp."SGDP_UNIDADES" ON sgdp."SGDP_USUARIOS_ROLES"."ID_UNIDAD" = sgdp."SGDP_UNIDADES"."ID_UNIDAD" 
		WHERE
			sgdp."SGDP_USUARIOS_ROLES"."B_ACTIVO" = true
			AND sgdp."SGDP_USUARIOS_ROLES"."ID_USUARIO" NOT IN ( \'user1\', \'dfsgdp\', \'user2\', \'user3\' ) 
			'.$cond.'
		ORDER BY
			sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD" ASC,
			sgdp."SGDP_USUARIOS_ROLES"."ID_USUARIO" ASC';
		  //return $sql;
	$tipoT=$_tipoT;
	$result=pg_query($dbconn, $sql);
	$res='<table id="example" class="display">
			<thead>
			<tr>	
				<th>Usuario</th>
				<th>0 a 5 d&iacute;as</th>
				<th>6 a 10 d&iacute;as</th>
				<th>11 a 20 d&iacute;as</th>
				<th>21 a 40 d&iacute;as</th>
				<th>41 a 80 d&iacute;as</th>
				<th>81 a 999 d&iacute;as</th>
				<th>Total</th>
				<th>&Aacute;rea</th>
			</tr>
			</thead>';	
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
	
		$res.='
			  <tr>
				<td>'.$row['ID_USUARIO'].'</td>
				<td><a href="det_nivel2.php?idUs='.$row['ID_USUARIO'].'&tram1=0&tram2=5&tip='.$tipoT.'" target="_blank">'.getContTareasTramo($row['ID_USUARIO'], 0, 5, $tipoT).'</a></td>
				<td><a href="det_nivel2.php?idUs='.$row['ID_USUARIO'].'&tram1=6&tram2=10&tip='.$tipoT.'" target="_blank">'.getContTareasTramo($row['ID_USUARIO'], 6, 10, $tipoT).'</a></td>
				<td><a href="det_nivel2.php?idUs='.$row['ID_USUARIO'].'&tram1=11&tram2=20&tip='.$tipoT.'" target="_blank">'.getContTareasTramo($row['ID_USUARIO'], 11, 20, $tipoT).'</a></td>
				<td><a href="det_nivel2.php?idUs='.$row['ID_USUARIO'].'&tram1=21&tram2=40&tip='.$tipoT.'" target="_blank">'.getContTareasTramo($row['ID_USUARIO'], 21, 40, $tipoT).'</a></td>
				<td><a href="det_nivel2.php?idUs='.$row['ID_USUARIO'].'&tram1=41&tram2=80&tip='.$tipoT.'" target="_blank">'.getContTareasTramo($row['ID_USUARIO'], 41, 80, $tipoT).'</a></td>
				<td><a href="det_nivel2.php?idUs='.$row['ID_USUARIO'].'&tram1=81&tram2=999&tip='.$tipoT.'" target="_blank">'.getContTareasTramo($row['ID_USUARIO'], 81, 999, $tipoT).'</a></td>
				<td><a href="det_nivel2.php?idUs='.$row['ID_USUARIO'].'&tram1=0&tram2=999&tip='.$tipoT.'" target="_blank">'.getContTareasTramo($row['ID_USUARIO'], 0, 999, $tipoT).'</a></td>
				<td>'.$row['A_CODIGO_UNIDAD'].'</td>
			  </tr>
			';
	}
	
	$res.='<tfoot>
			<tr>	
				<th>Usuario</th>
				<th>0 a 5 d&iacute;as</th>
				<th>6 a 10 d&iacute;as</th>
				<th>11 a 20 d&iacute;as</th>
				<th>21 a 40 d&iacute;as</th>
				<th>41 a 80 d&iacute;as</th>
				<th>81 a 999 d&iacute;as</th>
				<th>Total</th>
				<th>&Aacute;rea</th>
			</tr>
			</tfoot>';
	
	return $res;

}

function getContTareasTramo($_user, $_tramIni, $_tramFin, $_tipoT){
	include('connect.php');
	$cont=null;
	$fHoy=date('Y-m-d');
	if($_tipoT=='p'){
		$sql='SELECT
					count(
					sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_INSTANCIA_DE_TAREA"
					)
				FROM
					sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"
					INNER JOIN sgdp."SGDP_INSTANCIAS_DE_TAREAS" ON sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_INSTANCIA_DE_TAREA" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
					INNER JOIN sgdp."SGDP_ESTADOS_DE_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_ESTADO_DE_TAREA" = sgdp."SGDP_ESTADOS_DE_TAREAS"."ID_ESTADO_DE_TAREA" 
				WHERE
					sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_USUARIO" = \''.$_user.'\' 
					AND (EXTRACT (
						DAY 
				FROM
					( date(\''.$fHoy.'\')-"SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_ASIGNACION" )) BETWEEN '.$_tramIni.' AND '.$_tramFin.')';
	}elseif($_tipoT=='f'){
		$sql='SELECT COUNT
					( sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" ) 
				FROM
					sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"
					INNER JOIN sgdp."SGDP_INSTANCIAS_DE_TAREAS" ON sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" 
				WHERE
					sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" NOT IN ( 4, 1, 5, 7 ) 
					AND sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN" =  \''.$_user.'\' 
					AND (EXTRACT (
						DAY 
				FROM
					( "SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_ASIGNACION" - "SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_INICIO" )) BETWEEN '.$_tramIni.' AND '.$_tramFin.')';
	
	}
	
	$result=pg_query($dbconn, $sql);
	
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		
		$cont=$row['count'];
	}
	
	return $cont;

}

function getDetTramo($_user, $_tramIni, $_tramFin, $_tipoT, $_idDiv){
	include('connect.php');
	$cont=$colFF=null;
	$fHoy=date('Y-m-d');
	if($_user=='all'){
		$where='';
	}elseif($_tipoT=='p'){
		if($_idDiv!=''){
			$where='sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_USUARIO" = \''.$_user.'\' 
						AND sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD" = \''.$_idDiv.'\' AND ';

		}else{
			$where='sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_USUARIO" = \''.$_user.'\' 
						AND';
		}
		
	}else{
		$where='sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN" =  \''.$_user.'\' 
					AND';
	}
	
	if($_idDiv!=''){
		$where=' sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD" = \''.$_idDiv.'\' 
		AND ';

	}
	
	
	if($_tipoT=='p'){
		$sql='SELECT
					sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_INSTANCIA_DE_TAREA",
					sgdp."SGDP_PROCESOS"."A_NOMBRE_PROCESO",
					sgdp."SGDP_TAREAS"."A_NOMBRE_TAREA",
					sgdp."SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_ASIGNACION",
					sgdp."SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_VENCIMIENTO",
					sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."A_NOMBRE_EXPEDIENTE",
					sgdp."SGDP_USUARIOS_ROLES"."ID_USUARIO",
					sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD" 
				FROM
					sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"
					INNER JOIN sgdp."SGDP_INSTANCIAS_DE_TAREAS" ON sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_INSTANCIA_DE_TAREA" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
					INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA"
					INNER JOIN sgdp."SGDP_PROCESOS" ON sgdp."SGDP_TAREAS"."ID_PROCESO" = sgdp."SGDP_PROCESOS"."ID_PROCESO"
					INNER JOIN sgdp."SGDP_INSTANCIAS_DE_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" = sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO"
					INNER JOIN sgdp."SGDP_USUARIOS_ROLES" ON sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_USUARIO" = sgdp."SGDP_USUARIOS_ROLES"."ID_USUARIO"
					INNER JOIN sgdp."SGDP_UNIDADES" ON sgdp."SGDP_USUARIOS_ROLES"."ID_UNIDAD" = sgdp."SGDP_UNIDADES"."ID_UNIDAD" 
				WHERE
					'.$where.'
					 (EXTRACT (
						days 
				FROM
					( date(\''.$fHoy.'\') - "SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_ASIGNACION" )) BETWEEN '.$_tramIni.' AND '.$_tramFin.')
					AND sgdp."SGDP_TAREAS"."B_ESPERAR_RESP" = false ';
		
	}elseif($_tipoT=='f'){
		$sql='SELECT
					sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN",
					sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."A_NOMBRE_EXPEDIENTE",
					sgdp."SGDP_PROCESOS"."A_NOMBRE_PROCESO",
					sgdp."SGDP_TAREAS"."A_NOMBRE_TAREA",
					sgdp."SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_ASIGNACION",
					sgdp."SGDP_USUARIOS_ROLES"."ID_USUARIO",
					sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD" ,
					sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO"
				FROM
					sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"
					INNER JOIN sgdp."SGDP_INSTANCIAS_DE_TAREAS" ON sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
					INNER JOIN sgdp."SGDP_INSTANCIAS_DE_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" = sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO"
					INNER JOIN sgdp."SGDP_PROCESOS" ON sgdp."SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO" = sgdp."SGDP_PROCESOS"."ID_PROCESO"
					INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA"
					INNER JOIN sgdp."SGDP_USUARIOS_ROLES" ON sgdp."SGDP_USUARIOS_ROLES"."ID_USUARIO" = sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN"
					INNER JOIN sgdp."SGDP_UNIDADES" ON sgdp."SGDP_USUARIOS_ROLES"."ID_UNIDAD" = sgdp."SGDP_UNIDADES"."ID_UNIDAD" 
				WHERE
					sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" NOT IN ( 4, 1, 5, 7 ) AND 					
					'.$where.'
					 (EXTRACT (
						days 
				FROM
					( "SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_ASIGNACION" - "SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_INICIO" )) BETWEEN '.$_tramIni.' AND '.$_tramFin.')';
					
	}
	//return $sql;
	$det='<table id="example" class="display">
			<thead>
			<tr>	
				<th>Usuario</th>
				<th>Expediente</th>
				<th>Subproceso</th>
				<th>Tarea</th>
				<th>Fecha Asignaci&oacute;n</th>
				<th>Fecha Fin</th>
				<th>Fecha Fencimiento</th>
				<th>Desviaci&oacute;n</th>
				<th>&Aacute;rea</th>
			</tr>
			</thead>';
	$result=pg_query($dbconn, $sql);
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$fechaFin=null;
		
		if($row['D_FECHA_MOVIMIENTO']!=''){
			$fechaFin='<td>'.$row['D_FECHA_MOVIMIENTO'].'</td>';
		}
		
		$fVen=$row['D_FECHA_VENCIMIENTO'];
		if($fechaFin==''){
			$fechaFin=date('Y-m-d H:i:s');
			$temp1=strtotime($fVen); 
			$temp2=strtotime($fechaFin); 
			$diferencia= $temp2-$temp1;
			$desv=floor($diferencia/60/60/24);
			
		}
		
		$color='';
		
		if($fechaFin<=$fVen){
			$color='#7FD570';				
		}else{
			$color='#FFB0B0';
		}
		$fechaFin='';
		$det.='
			  <tr>
				<td>'.$row['ID_USUARIO'].'</td>
				<td>'.$row['A_NOMBRE_EXPEDIENTE'].'</td>
				<td>'.$row['A_NOMBRE_PROCESO'].'</td>
				<td>'.$row['A_NOMBRE_TAREA'].'</td>
				<td>'.$row['D_FECHA_ASIGNACION'].'</td>
				<td>'.$fechaFin.'</td>
				<td>'.$row['D_FECHA_VENCIMIENTO'].'</td>
				<td style="background:'.$color.'" >'.$desv.'</td>
				<td>'.$row['A_CODIGO_UNIDAD'].'</td>
			  </tr>
			';
	}
	
	$det.='<tfoot>
			<tr>	
				<th>Usuario</th>
				<th>Expediente</th>
				<th>Subproceso</th>
				<th>Tarea</th>
				<th>Fecha Asignaci&oacute;n</th>
				<th>Fecha Fin</th>
				<th>Fecha Fencimiento</th>
				<th>Desviaci&oacute;n</th>
				<th>&Aacute;rea</th>
			</tr>
			</tfoot>';

	return $det;
}

function getTotalInOutDiv(){
	include('connect.php');
	$contDiv=null;
	$sql='SELECT
			t2."A_CODIGO_UNIDAD",
			Count(sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA") AS contOutdiv,
			(SELECT
				Count(sgdp."SGDP_HISTORICO_USUARIOS_ASIGNADOS_A_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA") AS tareas_ejec
				FROM
				sgdp."SGDP_HISTORICO_USUARIOS_ASIGNADOS_A_TAREAS"
				LEFT JOIN sgdp."SGDP_USUARIOS_ROLES" AS t1 ON sgdp."SGDP_HISTORICO_USUARIOS_ASIGNADOS_A_TAREAS"."ID_USUARIO" = t1."ID_USUARIO"
				INNER JOIN sgdp."SGDP_UNIDADES" ON t1."ID_UNIDAD" = sgdp."SGDP_UNIDADES"."ID_UNIDAD"
				WHERE
				t1."ID_USUARIO" NOT IN (\'user1\', \'dfsgdp\', \'user2\', \'user3\') AND
				sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD" = t2."A_CODIGO_UNIDAD"
				GROUP BY
				sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD"
				ORDER BY
				sgdp."SGDP_UNIDADES"."A_CODIGO_UNIDAD" ASC,
				tareas_ejec DESC
				) as tarIn
			FROM
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"
			INNER JOIN sgdp."SGDP_INSTANCIAS_DE_TAREAS" ON sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
			INNER JOIN sgdp."SGDP_USUARIOS_ROLES" ON sgdp."SGDP_USUARIOS_ROLES"."ID_USUARIO" = sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN"
			INNER JOIN sgdp."SGDP_UNIDADES" AS t2 ON sgdp."SGDP_USUARIOS_ROLES"."ID_UNIDAD" = t2."ID_UNIDAD"
			WHERE
			sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_ESTADO_DE_TAREA" = 3 AND
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = 3
			GROUP BY
			t2."A_CODIGO_UNIDAD"';
	$result=pg_query($dbconn, $sql);
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$contDiv[$row['A_CODIGO_UNIDAD']]=array($row['tarin'],$row['contoutdiv']);
	}
	
	return $contDiv;

}

function getTiempoRespPromedio($_idUser){

	include('connect.php');
	$tProm=null;
	$sql='SELECT
			ht1."ID_USUARIO_ORIGEN",
			AVG (
				extract(day from (
					ht1."D_FECHA_MOVIMIENTO" - (
					SELECT
						sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO" 
					FROM
						sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS" 
					WHERE
						sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DESTINO" = ht1."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" 
						AND sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO" < ht1."D_FECHA_MOVIMIENTO" 
						AND sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" NOT IN ( 4, 5, 7 ) 
					ORDER BY
						sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO" DESC 
						LIMIT 1 
					) 
				) )
			) AS resp_prom 
		FROM
			sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS" AS ht1
			INNER JOIN sgdp."SGDP_INSTANCIAS_DE_TAREAS" ON ht1."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
			INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA"
		WHERE
			ht1."ID_USUARIO_ORIGEN" = \''.$_idUser.'\' 
			AND ht1."ID_ACCION_HISTORICO_INST_DE_TAREA" NOT IN ( 1, 4, 5, 7 ) AND
			sgdp."SGDP_TAREAS"."B_ESPERAR_RESP" = false
		GROUP BY
			ht1."ID_USUARIO_ORIGEN"';
	$result=pg_query($dbconn, $sql);
	if(pg_num_rows($result)<1){
		return 0;
	}
	$row = pg_fetch_array($result, 0);
	$tProm=$row['resp_prom'];
	
	return round($tProm,2);

}


?>