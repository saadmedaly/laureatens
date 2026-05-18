<?php include('/../include/header.php');
    include('include/menu.php');?>
   
    <div class="center_content">  
    <div class="right_content">            
        
   <h2>Modifier le mot de passe</h2> 
   
   <div class="warning_box" >
              Le nouveau mot de passe doit contenir 8 à 10 lettres et chiffres dont au moins une lettre et un chiffre.
          </div>
         <div class="form">
          <?php 
           echo validation_errors('<div class="error_box">', '</div>');
          $attributes = array('class' => 'niceform');
         
          echo form_open('administrateur/modifier_mot_de_passe',$attributes);?>
         
               <fieldset>
                     <div><?php echo $errorMessage; ?></div>
                   <table id="modifier_pass">
                    <tr>
                        <td><label>Ancien mot de Passe<span style="color:red;font-weight:bold;font-size:14px;">*</span></label></td>
                        <td><?php $input = array('type' => 'password', 'size' => '40', 'name'=>'old_password'); 
                             echo form_input($input,'','required');?></td>
                    </tr>
                    <tr>
                        <td><label>Nouveau mot de Passe<span style="color:red;font-weight:bold;font-size:14px;">*</span></label></td>
                        <td><?php $input = array('type' => 'password', 'size' => '40', 'name'=>'new_password'); 
                             echo form_input($input,'','required');?></td>
                    </tr>
                    <tr>
                        <td><label>Confirmer le mot de passe<span style="color:red;font-weight:bold;font-size:14px;">*</span></label></td>
                        <td><?php $input = array('type' => 'password', 'size' => '40', 'name'=>'confirmed_password'); 
                             echo form_input($input,'','required');?></td>
                    </tr>
    
                     <tr class="submit">
                         <td></td>
                         <td id="submit"><input type="submit" name="submit" id="submit" value="Valider" /></td>
                     </tr>
                     
                     
                    </table>
                </fieldset>
                
         <?php echo form_close('</div>');?>
      
     
     </div><!-- end of right content-->
            
                    
  </div>   <!--end of center content -->               
                    
                    
    
    
    <div class="clear"></div>
    </div> <!--end of main content-->
	
    
  <?php include('/../include/footer.php');?>