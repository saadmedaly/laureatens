
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
echo validation_errors('<div class="error_box">', '</div>');
        echo heading('Modifier Programme ', '3');
 
        $attributes = array('class' => 'niceform');

        echo form_open('administrateur/modifier_programme_action/'.$idProgramme, $attributes);?>
           <table id="programme">
            <tr>
                
                <td><?php echo form_label('ID du programme '); ?></td>
                <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'idProgramme', 'readonly'=>"readonly") ;
                        echo form_input($input,$programmeInfo['idProgramme'],'required');?>
                </td>
           </tr>
           <tr>
                <td><?php echo form_label('Nom du programme <span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?></td>
                <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'nom') ;
                        echo form_input($input,$programmeInfo['nom'],'required');?>
                </td>
           </tr>
           <tr>
                <td><?php echo form_label('Description <span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?></td>
                <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'description') ;
                        echo form_input($input,$programmeInfo['description'],'required');?>
                </td>
           </tr>
           <tr>
               
               <td><?php echo form_label('Nombre de crédits <span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?></td>
               <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'nbCredits') ;
                        echo form_input($input,$programmeInfo['nbCredits'],'required');?>
                </td>
           </tr>
           
           
             <tr>
                   <td><?php echo form_label('Cycle :'); ?></td>
                    <td id="sellect">
                    <?php
                        echo ' <select size="1" name="cycle" id=""> ';

                        for($i=0;$i<sizeof($cycle['nomCycle']);$i++)
                            echo '<option value="'.$cycle['nomCycle'][$i].'">'.$cycle['nomCycle'][$i].'</option>';
                  
                        echo '</select>';
                    ?>
                    </td>
               </tr>
               
               
             <tr class="submit">
                  <td>
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
