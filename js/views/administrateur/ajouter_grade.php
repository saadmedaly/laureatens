
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  

 <?php echo validation_errors('<div class="error_box">', '</div>'); ?>

        <?php
        echo heading('Créer un grade ', '3');

        $attributes = array('class' => 'niceform');

        echo form_open('administrateur/ajouter_grade_succe', $attributes);
        echo form_fieldset();
        ?>
        <table id="rounded-corner" summary="cycle">
            <thead>
                <tr>
                    <th id="col" class="rounded">Grade</th>
                    <th id="col" class="rounded">Nombre de crédits</th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php        
                if(is_array($gradeInfo))
                {
                    for($i=0;$i<sizeof($gradeInfo)-1;$i=$i+2)
                    {
                    echo '<tr>
                        <td>'.$gradeInfo[$i].'</td>
                        <td>'.$gradeInfo[$i+1].'</td>
                        </tr>';
                    }
                }
                ?>
                        
            </tbody>
        </table>
        
        <table id="cycle" style="width: 600px;">
       
         <tr><td><br><br></td></tr>
        
            <tr>
                <th>Grade<span style="color:red;font-weight:bold;font-size:14px;">*</span></th>
                <th>Nombre de crédits<span style="color:red;font-weight:bold;font-size:14px;">*</span></th>
            </tr>
            <tr>
                <td  style="width: 300px;"><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'idGrade') ;
                        echo form_input($input,'','');
                       ?>
                </td>
                <td  style="width: 300px;"><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'nbreCredits') ;
                        echo form_input($input,'','');
                       ?>
                </td>
            </tr>
             <tr class="submit">
                  <td  style="width: 400px;">
                         <?php $pw = array('type' => 'submit', 'name' => 'submit', 'id'=>'submit', 'value'=>'Enregistrer'); 
                             echo form_input($pw);?>
                  </td>
                  <td></td>
              </tr>
        </table>
    </form>
    </div>                      
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
