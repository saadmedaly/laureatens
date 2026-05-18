
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
 echo validation_errors('<div class="error_box">', '</div>');
        echo heading('Modifier le département ', '3');

        $attributes = array('class' => 'niceform');

        echo form_open('administrateur/modifier_departement_action/'.$idDepartement, $attributes);?>
           <table>
               <tr><td><br><br><br></td></tr>
                
                
         
            <tr>
                <td><b>Id du département</b></td>
                <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'idDepartement', 'readonly'=>"readonly") ;
                        echo form_input($input,$departementInfo['idDepartement'],'required');?>
                </td>
            </tr>
            <tr>
                <td><b>Nom du département<span style="color:red;font-weight:bold;font-size:14px;">*</span></b></td>
                <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'nom') ;
                        echo form_input($input,$departementInfo['nom'],'required');?>
                </td>
            </tr>
            <tr>
                <td><b>Description<span style="color:red;font-weight:bold;font-size:14px;">*</span></b></td>
                <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'description') ;
                        echo form_input($input,$departementInfo['description'],'required');?>
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
