
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
 echo validation_errors('<div class="error_box">', '</div>');
        echo heading('Créer un Cycle ', '3');

        $attributes = array('class' => 'niceform');

        echo form_open('administrateur/ajouter_cycle_succe', $attributes);
        echo form_fieldset();
        ?>
        <table id="rounded-corner" summary="cycle">
            <thead>
                <tr>
                    <th id="col" class="rounded">Nom du cycle</th>
                    <th id="col" class="rounded">Nombre de crédits</th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php                        
                for($i=0;$i<sizeof($cycleInfo)-1;$i=$i+2)
                {
                echo '<tr>
                    <td>'.$cycleInfo[$i].'</td>
                    <td>'.$cycleInfo[$i+1].'</td>
                    </tr>';
                }
                ?>
                        
            </tbody>
        </table>
        <div id="ajouterClasse">
        </div>
        <table id="cycle" style="width: 600px;">
            <tr>
                <th>Nom du cycle<span style="color:red;font-weight:bold;font-size:14px;">*</span></th>
                <th style="margin-right:30px;">Nombre de crédits<span style="color:red;font-weight:bold;font-size:14px;">*</span></th>
            </tr>
            <tr>
                <td style="width: 300px;"><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'nomCycle') ;
                        echo form_input($input,'','');?>
                </td>
                <td style="width: 300px;"><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'nbreCredits') ;
                        echo form_input($input,'','');
                        ?>
                </td>
            </tr>
             <tr class="submit">
                  <td style="width: 300px;">
                         <?php $pw = array('type' => 'submit', 'name' => 'submit', 'id'=>'submit', 'value'=>'Enregistrer'); 
                           echo form_input($pw);?>
                  </td>
                  <td></td>
              </tr>
        </table>
        
       <?php echo form_close();?>
    </div>                      
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
