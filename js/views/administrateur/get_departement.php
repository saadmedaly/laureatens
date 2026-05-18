
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
        echo heading('Créer un département', '3');


        ?>
        <table>
         <tr><td><br><br></td></tr>
         </table>
        <table id="rounded-corner" summary="cycle">
            <thead>
                <tr>
                    <th id="col" class="rounded">Id Département</th>
                    <th id="col" class="rounded">Nom du département</th>
                    <th id="col" class="rounded">Description</th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php                        
                for($i=0;$i<sizeof($departementInfo)-1;$i=$i+3)
                {
                echo '<tr>
                    <td>'.$departementInfo[$i].'</td>
                    <td>'.$departementInfo[$i+1].'</td>
                         <td>'.$departementInfo[$i+2].'</td>
                    </tr>';
                }
                ?>
                        
            </tbody>
        </table>
    <?php 
  $title = '<span class="bt_green_lft"></span><strong>Ajouter un département</strong><span class="bt_green_r"></span>';
          $attributes = 'class="bt_green"';
          echo anchor('administrateur/ajouter_departement', $title, $attributes);
          
 
          ?>
    </div>                      
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
