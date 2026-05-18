
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
        echo heading('Créer un local ', '3');
         echo validation_errors('<div class="error_box">', '</div>');
            $attributes = array('class' => 'niceform');
         echo form_open('administrateur/ajouter_local_succe', $attributes);
        ?>
        <table>
         <tr><td><br><br></td></tr>
         </table>
        <table id="rounded-corner" summary="cycle">
            
            <thead>
                <tr>
                    <th id="col" class="rounded">Local</th>
                    <th id="col" class="rounded">Description </th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php                        
                for($i=0;$i<sizeof($localInfo)-1;$i=$i+2)
                {
                echo '<tr>
                    <td>'.$localInfo[$i].'</td>
                    <td>'.$localInfo[$i+1].'</td>
                    </tr>';
                }
                ?>
                        
            </tbody>
        </table>
        <?php 
        if(!isset($ajouter))
        {
            $title = '<span class="bt_green_lft"></span><strong>Ajouter un élément</strong><span class="bt_green_r"></span>';
          $attributes = 'class="bt_green"';
          echo anchor('administrateur/ajouter_local', $title, $attributes);
        }
        else
        {
           echo  '<div id="ajouterClasse">
        </div>
        <table id="cycle" style="width:700px;">
            <tr>
                <th>Local</th>
                <th>Description</th>
            </tr>
            <tr>
                <td style="width:300px;">';
             $input = array('type' => 'text', 'size' => '30', 'name'=>'sigleLocal') ;
                        echo form_input($input,'','');
                        
               echo '</td>
                <td style="width:300px;">';
                $input = array('type' => 'text', 'size' => '30', 'name'=>'descriptions') ;
                        echo form_input($input,'','');
                        
                echo '</td>
            </tr>
             <tr class="submit">
                 <td style="width:300px;">';
                   $pw = array('type' => 'submit', 'name' => 'submit', 'id'=>'submit', 'value'=>'Enregistrer'); 
                   echo form_input($pw);
                  echo '</td>
                  <td></td>
              </tr>
        </table>';
        }
       echo form_close();
          ?>
  
    </div>                      
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
