
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
    echo validation_errors('<div class="error_box">', '</div>');
        echo heading('Créer un département ', '3');
        
        $attributes = array('class' => 'niceform');
        echo $errorInfo;
        echo form_open('administrateur/ajouter_departement_succe', $attributes);
        ?>
        
        <table>
         <tr><td><br><br></td></tr>
         </table>
        <table id="rounded-corner" summary="departement">
            <thead>
                <tr>
                    <th id="col" class="rounded">Id Département</th>
                    <th id="col" class="rounded">Nom du département</th>
                    <th id="col" class="rounded">Description</th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php                        
                for($i=0;$i<sizeof($departementInfo)-1;$i=$i+3)
                {
                echo '<tr>
                    <td>'.$departementInfo[$i].'</td>
                    <td>'.$departementInfo[$i+1].'</td>
                         <td>'.$departementInfo[$i+2].'</td>
                    </tr>';
                }
                ?>
                        
            </tbody>
        </table>
 <table id="departement">

            <tr>
                 <td id="titre"><label>Id Département<span style="color:red;font-weight:bold;font-size:14px;">*</span></label></td>
                <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'idDepartement') ;
                        echo form_input($input,'','');
                        ?>
                </td>
            </tr>  
            <tr>
                <td id="titre"><label> Nom du département<span style="color:red;font-weight:bold;font-size:14px;">*</span></label></td>
                <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'nom') ;
                        echo form_input($input,'','');
                       ?>
                </td>
            </tr>
            <tr>
                <td id="titre"><label> Description<span style="color:red;font-weight:bold;font-size:14px;">*</span></label></td>   
                <td><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'description') ;
                        echo form_input($input,'','');
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
    </form>
    </div>                      
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
