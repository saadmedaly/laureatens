
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
        echo heading('Liste des personnes-clés qui recevront un e-mail lors de tout changement dans le plan d\'études : ', '3');

        $attributes = array('class' => 'niceform');

        ?>
           <table id="rounded-corner" summary="personneCle">
            
            <thead>
                <tr>
                    <th id="col" class="rounded">Personne-clé</th>
                    <th id="col" class="rounded">Envoi actif</th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php                        
                function OuiNon($actif) {
				if ($actif == 1) {
					return "Oui" ;
					} else 
					return "Non";
				}
				for($i=0;$i<  count($personneCle)-1;$i=$i+2)
                {
                echo '<tr>
                    <td>'.$personneCle[$i].'</td>
                    <td>'.OuiNon($personneCle[$i+1]).'</td>
                    </tr>';
                }
                ?>
                        
            </tbody>
        </table>
  <?php 
  $title = '<span class="bt_green_lft"></span><strong>Ajouter un élément</strong><span class="bt_green_r"></span>';
          $attributes = 'class="bt_green"';
          echo anchor('administrateur/ajouter_personneCle', $title, $attributes);
          
 
          ?>
    </div>                      
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
