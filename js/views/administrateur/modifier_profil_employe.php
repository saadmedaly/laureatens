<?php include('/../include/header.php');
  include('include/menu.php');
  ?>
   
    <div class="center_content">  
       
    <div class="right_content">          

         <?php 
              if(isset($errorMessage))
          {
              echo $errorMessage;
          }
			echo validation_errors('<div class="error_box">', '</div>');
			echo heading('Modification du profil employé','3');
         
          $attributes = array('class' => 'niceform');
		  echo form_open('administrateur/modifier_information_personnelle',$attributes);
     
         ?>
        <div id="matriculeEmploye"><?php echo $matricule['matricule'];?></div>
       
		<?php  echo form_fieldset();?>

          
         <table class="form">
              <tr><td><br></td></tr>
                    <tr>
                    
                        <td id="titre"><label>Matricule </label></td>
                        <td id="contenu"><label><?php echo $matricule['matricule'];?></label></td>
                    </tr>
                    <tr><td><br></td></tr>
                    <tr>
                    
                        <td id="titre"><label>Nom </label></td>
                        <td id="contenu"><label><?php echo $nom;?></label></td>
                    </tr>
                    <tr><td><br></td></tr>
                    <tr>
                        <td id="titre"><label>Prénom </label></td>
                        <td id="contenu"><label><?php echo $prenom;?></label></td>
                    </tr>
                     <tr><td><br></td></tr>
                    <tr>
                        <td id="titre"><label>E-mail </label></td>
                       <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'email') ;
                             echo form_input($input,$email,'');?>
                        </td>
                    </tr>     
                 
             <tr><td><br></td></tr>
         </table>
              <div style="height: 40px;"></div>
              <table id="profilTitre">
            <tr>
                <td id="profilTitre"><?php echo form_label('Profil'.'<span style="color:red;font-weight:bold;font-size:14px;">*</span>', 'profils'); ?></td>
                <td><?php  echo form_checkbox(array('name' => 'enseignant'), 'professeur',  $checked['professeur']);?></td>
                <td><?php  echo form_label('Enseignant', 'enseignant');?></td>
                <td><?php  echo form_checkbox(array('name' => 'scolarite'), 'scolarite', $checked['scolarite']);?></td>
                <td><?php  echo form_label('Scolarité', 'scolarité');?></td>

                <td><?php   echo form_checkbox(array('name' => 'secretaire'), 'secretaire', $checked['secretaire']);?></td>
                <td><?php    echo form_label('Secrétaire', 'secretaire');?></td>

                <td><?php    echo form_checkbox(array('name' => 'chef_departement'), 'chef_departement', $checked['chef_departement']);?></td>
                <td><?php   echo form_label('Chef de département', 'chef_departement');?></td>

                <td><?php    echo form_checkbox(array('name' => 'administrateur'), 'administrateur', $checked['administrateur']);?></td>
                <td><?php    echo form_label('Administrateur', 'administrateur');?></td>

            </tr>
          </table>
            <table class="form"> 
              <tr><td><br><br><br></td></tr>
             
             <tr id="dep">
                <td id="titre"><label>Département </label></td>
                <td>
                <?php echo ' <select size="1" name="departement"> ';

                         echo '<option value="'.$nomDep['idDepartement'].'">'.$nomDep['nomDepartement'].'</option>';
                        for($i=0; $i< sizeof($departements['nomDep']); $i++)
                        {
                            if($departements['nomDep'][$i] != $nomDep['nomDepartement'])
                                 echo '<option value="'.$departements['idDepartement'][$i].'">'.$departements['nomDep'][$i].'</option>';
                        }
                        echo '</select>';
                      ?>
                </td>
            </tr>
            
            <tr><td><br><br><br><br></td></tr>
            <tr>
                <td><?php echo form_label('Actif', 'actif'); ?></td>
                <td><?php echo form_checkbox('Actif', 'actif',  $actif);?></td>
            </tr> 
             <tr><td><br><br><br></td></tr>
            <tr>
                <td><?php echo form_label('Date et raison du statut Inactif '); ?></td>
                <td><?php
                    $input = array('type' => 'text', 'size' => '54', 'name' => 'raison');
                    echo form_input($input, $raison);
                    ?>
                </td>
            </tr>
            
           </table>
       
            <td><?php
                    echo form_hidden(array('matricule' => $matricule['matricule']));
                    ?>
            </td>
       
        <table>
            <tr><td><br></td></tr>
            <tr class="submit">
                  <td>
                         <?php $pw = array('type' => 'submit', 'name' => 'submit', 'id'=>'submit', 'value'=>'Enregistrer'); 
                             echo form_input($pw);?>
                  </td>
              </tr>
      
       </table>
           <?php echo form_close(); ?>
     </div>            
  </div>               

<div class="clear"></div>
        
</div>
 <?php include('/../include/footer.php');?>