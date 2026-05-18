<?php include('/../include/header.php');
    include('include/menu.php');?>
   <div class="center_content">  
    
     
    
    <div class="right_content">            
        
         <?php 
       
         echo heading('Ajouter un employé (2/2)','3');
          echo heading('Informations générées','3');
          
           $attributes = array('class' => 'niceform');
         
          echo form_open('administrateur/inserer_employe',$attributes);
             ?>
             <table class="form">
                    <tr>
                        <td><?php  echo form_label('Code d\'accès:','login');?></td>
                        <td><?php $input = array('type' => 'text', 'size' => '25', 'name'=>'login', "disabled" =>"disabled", "style" => "font-size:18px"); 
                             echo form_input($input,$login,'');
                             echo form_error('login','<span class="error">','</span>');?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php  echo form_label('Mot de passe:','pass');?></td>
                        <td><?php $input = array('type' => 'text', 'size' => '25', 'name'=>'pass', "disabled" =>"disabled" ,"style" => "font-size:18px"); 
                             echo form_input($input,$password,'');
                             echo form_error('pass','<span class="error">','</span>');?>
                        </td>
                    </tr>
                    <?php 
                    echo form_hidden($infos_perso);
                     echo form_hidden('password',$password);
                      echo form_hidden('login',$login);
                    ?>
                    <tr>
                        <td></td>
                     <td class="submit">
                         <?php $pw = array('type' => 'submit', 'name' => 'submit', 'id'=>'submit', 'value'=>'Enregistrer'); 
                             echo form_input($pw);?>
                     </td>
                     </form>
                     <td>
                             <?php
                             echo form_open('administrateur/regenerer_password');
                              echo form_hidden($infos_perso);
                              echo form_hidden('password',$password);
                            echo form_hidden('login',$login);
                             $pw = array('type' => 'submit', 'name' => 'submit', 'id'=>'submit', 'value'=>'Regenerer'); 
                             
                             echo form_input($pw);?>
                         </form>
                     </td>
                    </tr>
                </table> 
           </div> </div> 
           <div class="clear"></div>
    </div> <!--end of main content-->
	
    <?php
  include('/../include/footer.php');?>

       
 