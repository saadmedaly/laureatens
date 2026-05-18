
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
        echo heading('Créer un programme', '3');

        ?>
        <table>
            <tr><td><br><br></td></tr>
        </table>
        
        <table id="rounded-corner" summary="cycle">
            <thead>
                <tr>
                    <th id="col" class="rounded">Id Programme</th>
                    <th id="col" class="rounded">Nom du programme</th>
                    <th id="col" class="rounded">Description</th>
                    <th id="col" class="rounded">Cycle</th>
                    <th id="col" class="rounded">Nombre de crédits</th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php                        
                for($i=0;$i<sizeof($programmeInfo)-1;$i=$i+5)
                {
                echo '<tr>
                    <td>'.$programmeInfo[$i].'</td>
                    <td>'.$programmeInfo[$i+1].'</td>
                    <td>'.$programmeInfo[$i+2].'</td>
                        <td>'.$programmeInfo[$i+3].'</td>
                            <td>'.$programmeInfo[$i+4].'</td>
                    </tr>';
                }
                ?>
                
            </tbody>
        </table>
         <?php 
  $title = '<span class="bt_green_lft"></span><strong>Ajouter un élément</strong><span class="bt_green_r"></span>';
          $attributes = 'class="bt_green"';
          echo anchor('administrateur/ajouter_programme', $title, $attributes);
          
 
          ?>

    </div>      <!-- right content -->                
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
