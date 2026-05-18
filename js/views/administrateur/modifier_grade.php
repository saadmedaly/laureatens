
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
 echo validation_errors('<div class="error_box">', '</div>');
        echo heading('Modifier les crédits du grade ', '3');

        $attributes = array('class' => 'niceform');

        echo form_open('administrateur/modifier_grade_action/'.$idGrade, $attributes);?>
           <table>
                <tr><br><br><br><br></tr>
            <tr>
                <th>Id du grade</th>
                <th>Nombre de crédits<span style="color:red;font-weight:bold;font-size:14px;">*</span></th>
            </tr>
            <tr>
                <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'idGrade', 'readonly'=>"readonly") ;
                        echo form_input($input,$gradeInfo['idGrade'],'required');?>
                </td>
                <td><?php $input = array('type' => 'text', 'size' => '20', 'name'=>'nbCredits') ;
                        echo form_input($input,$gradeInfo['nbCredits'],'required');?>
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
