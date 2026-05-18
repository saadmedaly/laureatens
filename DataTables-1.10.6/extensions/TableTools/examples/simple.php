<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/favicon.ico">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=2.0">

	<title>TableTools example - Basic initialisation</title>
	<link rel="stylesheet" type="text/css" href="../../../media/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="../css/dataTables.tableTools.css">
	<link rel="stylesheet" type="text/css" href="../../../examples/resources/syntax/shCore.css">
	<link rel="stylesheet" type="text/css" href="../../../examples/resources/demo.css">
	<style type="text/css" class="init">

	</style>
	<script type="text/javascript" language="javascript" src="../../../media/js/jquery.js"></script>
	<script type="text/javascript" language="javascript" src="../../../media/js/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript" src="../js/dataTables.tableTools.js"></script>
	<script type="text/javascript" language="javascript" src="../../../examples/resources/syntax/shCore.js"></script>
	<script type="text/javascript" language="javascript" src="../../../examples/resources/demo.js"></script>
	<script type="text/javascript" language="javascript" class="init">


/*$(document).ready(function() {
	$('#example').DataTable( {
		dom: 'T<"clear">lfrtip',
 
	} );
} );*/
    $(document).ready(function() {
    var table = $('#example').DataTable( {
        "pagingType": "full_numbers",
        "iDisplayLength": 10,
        "dom": 'T<"clear">lfrtip',
        "oTableTools": {
          "aButtons": [
            {'sExtends':'copy',
              "oSelectorOpts": { filter: 'applied', order: 'current' },
            },
            {'sExtends':'xls',
              "oSelectorOpts": { filter: 'applied', order: 'current' },
            },
            {'sExtends':'print',
              "oSelectorOpts": { filter: 'applied', order: 'current' },
            }
          ]
        },
        "language": {
        "url": "../../../lang/French.json"
        }
    });
});


	</script>
</head>

<?php
$hote = 'localhost';
$base = 'db_toponymie';
$user = 'root';
$pass = '';
$cnx = mysql_connect ($hote, $user, $pass) or die (mysql_error ());
$ret = mysql_select_db ($base) or die (mysql_error ());
mysql_set_charset('utf8',$cnx); 
$result = mysql_query("select distinct id_toponyme, T2.libele_type, nom_toponyme,  C.nom_commune_fr, M.nom_mougataa_fr, W.nom_wilaya_fr from toponyme_commune NATURAL JOIN toponyme T1 NATURAL JOIN commune C, type_toponyme T2, mougataa M, wilaya W where T1.id_type_toponyme = T2.id_type_toponyme and M.id_mougataa=C.id_mougataa and M.id_wilaya=W.id_wilaya");	
?>
<body class="dt-example">
	<div class="container">
	

			<table id="example" class="display" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>ID topo</th>
                                                <th>Type</th>
                                                <th>Nom</th>
                                                <th>Commune</th>
                                                <th>Mougataa</th>
                                                <th>Wilaya</th>
					</tr>
                        
				</thead>

				<tfoot>
					<tr>
						<th>ID topo</th>
                                                <th>Type</th>
                                                <th>Nom</th>
                                                <th>Commune</th>
                                                <th>Mougataa</th>
                                                <th>Wilaya</th>
					</tr>
				</tfoot>

				<tbody>
                                    <?php
                                    while ($row = mysql_fetch_array($result)) {
					echo '<tr>';
                                        echo '<td>'.$row['id_toponyme'].'</td><td>'.$row['libele_type'].'</td>';
                                        echo '<td>'.$row['nom_toponyme'].'</td><td>'.$row['nom_commune_fr'].'</td>';
                                        echo '<td>'.$row['nom_mougataa_fr'].'</td><td>'.$row['nom_wilaya_fr'].'</td>';
					echo '</tr>';
                                        
                                    }?>
				</tbody>
			</table>

			

			
</body>
</html>