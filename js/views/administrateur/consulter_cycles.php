
<?php include('/../include/header.php');
include('include/menu.php');
?> 
<!-- Fihier créé à partir de get_cycle.php pour 2.2.1 Mai 2013. -->

<div class="center_content">  

    <div class="right_content">  



        <?php
        echo heading('Cycles existants ', '3');
            $attributes = array('class' => 'niceform');
         echo form_open('administrateur/supprimer_cycle', $attributes);
        ?>
        <table>
         <tr><td><br><br></td></tr>
         </table>
        <table id="rounded-corner" summary="cycle">
            
            <thead>
                <tr>
                    <th id="col" class="rounded">Id Cycle</th>
					<th id="col" class="rounded">Nom du cycle</th>
                    <th id="col" class="rounded">Nombre de crédits</th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php                        
                for($i=0;$i<sizeof($cycleInfo)-1;$i=$i+3)
                {
                echo '<tr>
                    <td>'.$cycleInfo[$i].'</td>
                    <td>'.$cycleInfo[$i+1].'</td>
					<td>'.$cycleInfo[$i+2].'</td>
                    </tr>';
                }
                ?>
                        
            </tbody>
        </table>
        <?php 
  /* $title = '<span class="bt_green_lft"></span><strong>Ajouter un élément</strong><span class="bt_green_r"></span>';
          $attributes = 'class="bt_green"';
          echo anchor('administrateur/ajouter_Cycle', $title, $attributes);
      
       echo form_close(); */
          ?>
  
    </div>                      
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
