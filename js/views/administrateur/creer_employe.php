<?php include('/../include/header.php');
include('include/menu.php');?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
           echo heading('Ajouter un employé ','3');
 echo validation_errors('<div class="error_box">', '</div>');
	if(isset($errorMessage))
             {
               echo $errorMessage;
             }
        $attributes = array('class' => 'niceform');

        echo form_open('administrateur/creer_employe', $attributes);
        echo form_fieldset();
        ?>

        <table class="form">
            <tr>
                <td><?php echo form_label('Nom'.'<span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?></td>
                <td><?php
                    $input = array('type' => 'text', 'size' => '40', 'name' => 'lastName');
                    echo form_input($input,'',''); ?>
                </td>
            </tr>

            <tr>
                <td><?php echo form_label('Prénom'.'<span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?></td>
                <td><?php
                    $input = array('type' => 'text', 'size' => '40', 'name' => 'firstName');
                    echo form_input($input,'','');?>
                </td>
            </tr>

            <tr>
                <td><?php echo form_label('E-mail', 'email'); ?></td>
                <td><?php  $input = array('type' => 'text', 'size' => '40', 'name' => 'email');
                       echo form_input($input, '');?>
                </td>
            </tr>
        </table>

        
         <table id="profilTitre">
            <tr>
                <td id="profilTitre"><?php echo form_label('Profil'.'<span style="color:red;font-weight:bold;font-size:14px;">*</span>', 'profils'); ?></td>
                <td><?php echo form_checkbox(array('name' => 'enseignant'), 'professeur', False);?></td>
                <td><?php echo form_label('Enseignant', 'enseignant');?></td>
                <td><?php echo form_checkbox(array('name' => 'scolarite'), 'scolarite', False);?></td>
                <td><?php echo form_label('Scolarité', 'scolarité');?></td>

                <td><?php echo form_checkbox(array('name' => 'secretaire'), 'secretaire', False);?></td>
                <td><?php echo form_label('Secrétaire', 'secretaire');?></td>

                <td><?php echo form_checkbox(array('name' => 'chef_departement'), 'chef_departement', False);?></td>
                <td><?php echo form_label('Chef de département', 'chef_departement');?></td>

                <td><?php echo form_checkbox(array('name' => 'administrateur'), 'administrateur', False);?></td>
                <td><?php echo form_label('Administrateur', 'administrateur');?></td>
            </tr>
          </table>
        <div class="clear"></div>
        <div style="height:50px;"></div>
          
        <table style="width:500px;">
            <tr id="dep">
                
                <td style="width:150px;"><?php echo form_label('Département'.'<span style="color:red;font-weight:bold;font-size:14px;">*</span>', 'departement'); ?>  </td>
                <td style="width:300px;"><?php echo ' <select size="1" name="departement"> ';
                            for($i =0 ; $i < count($idDep); $i++)
                            {
                                echo '<option value="'.$idDep[$i].'">'.$nomDep[$i].'</option>';
                            }
                    echo '</select>';?>
                    
                </td>
            </tr>
            
            <tr><td><br></td></tr>
          </table>
   
            <tr>
                <td class="submit">
                    <?php $pw = array('type' => 'submit',  'id' => 'submit', 'value' => 'Enregistrer');
                    echo form_input($pw);
                    ?>
                </td>
            </tr>
        </table>
        <?php echo form_close(); ?>
    </div>                      
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
