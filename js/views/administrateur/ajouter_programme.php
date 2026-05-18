
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
 echo validation_errors('<div class="error_box">', '</div>');
        echo heading('Créer un programme ', '3');

        $attributes = array('class' => 'niceform');

        echo form_open('administrateur/ajouter_programme_succe', $attributes);
         echo $errorInfo;
        ?>
         <table>
            <tr><td><br><br></td></tr>
        </table>
        <table id="rounded-corner" summary="cycle">
            <thead>
                <tr>
                    <th id="col" class="rounded">Id Programme</th>
                    <th id="col" class="rounded">Nom</th>
                    <th id="col" class="rounded">Description</th>
                    <th id="col" class="rounded">Cycle</th>
                    <th id="col" class="rounded">Nombre de crédits</th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php                        
                for($i=0;$i<sizeof($programmeInfo)-1;$i=$i+5)
                {
                echo '<tr>
                    <td>'.$programmeInfo[$i].'</td>
                    <td>'.$programmeInfo[$i+1].'</td>
                    <td>'.$programmeInfo[$i+2].'</td>
                        <td>'.$programmeInfo[$i+3].'</td>
                            <td>'.$programmeInfo[$i+4].'</td>
                    </tr>';
                }
                ?>
                
            </tbody>
        </table>
 
        <table id="programme" style="width: 500px;">
            
            <tr>
                  <td><?php echo form_label('Id Programme'.'<span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?></td>
                <td style="width: 300px;"><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'idProgramme'); 
                    echo form_input($input,'','');?>
                </td>
              </tr>
              <tr>
                    <td><?php echo form_label('Nom'.'<span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?></td>
                  <td style="width: 300px;"><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'nom'); 
                        echo form_input($input,'','');?>
                  </td>
              </tr>
              <tr>
                      <td><?php echo form_label('Description'.'<span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?></td>
                    <td style="width: 300px;"><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'description'); 
                        echo form_input($input,'','');?>
                    </td>
               </tr>
             
               <tr>
                     <td><?php echo form_label('Nombre de crédits'.'<span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?></td>
                    <td style="width: 300px;"><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'nbreCredits'); 
                        echo form_input($input,'','');?>
                    </td>
                </tr>  
                </table>
        <div style="height:50px;"></div>
        <table style="width:700px;">
                 <tr id="dep">
                     <td style="width: 150px;"><?php echo form_label('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cycle'.
                             '<span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?></td>
                    <td style="width: 350px;">
                    <?php
                        echo ' <select size=1 name="cycle" id=""> ';

                        for($i=0;$i<sizeof($cycle['nomCycle']);$i++)
                            echo '<option value="'.$cycle['nomCycle'][$i].'">'.$cycle['nomCycle'][$i].'</option>';
                  
                        echo '</select>';
                    ?>
                    </td>
               </tr>
                </table>
                <table>
            <tr>
                <td></td>
                <td id="submit" style="width: 300px;"><input type="submit" name="submit" id="submit" value="Enregistrer" /></td>

            </tr>
        </table>  
         <?php echo form_close();?>
    </div>      <!-- right content -->                
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
