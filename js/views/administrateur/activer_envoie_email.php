 
<?php include('/../include/header.php');
    include('include/menu.php');?>
   
    <div class="center_content">  
  
    <div class="right_content">            
        
         <?php 
       
         echo heading('Activer et désactiver l\'envoi des e-mails aux personnes-clés','3');
          
           $attributes = array('class' => '');
         
          echo form_open('administrateur/valider_activation_monitoring',$attributes);?>
        <div style="height: 80px;"></div>
        <table>
            <tr>
                <td style="font-size: 18px;font-weight: bold;">Envoi activé</td>
                <td style="width: 100px;"></td>
                <td style="font-size: 18px;font-weight: bold;">Envoi désactivé</td>
            </tr>
            <tr>
                <td>  
                    <?php if ($isActive == 1)
                    {
                        echo  '<input type="radio" name="monitoring" value="actif" checked ="checked">';
                    }
                    else
                    {
                        echo  '<input type="radio" name="monitoring" value="actif">';
                    }
                   ?>
                   
                </td>
                <td></td>
                <td>
                    <?php 
                    if ($isActive == 1)
                    {
                        echo '<input type="radio" name="monitoring" value="inactif">';
                    }
                    else 
                    {
                         echo '<input type="radio" name="monitoring" value="inactif" checked ="checked">';
                    }
                         ?>
                </td>
            </tr>
        </table>
            
            <?php 
             $pw = array('type' => 'submit', 'name' => 'submit', 'id'=>'submit', 'value'=>'Valider'); 
                             
                             echo form_input($pw);?>
        </form>
 
 
     </div><!-- end of right content-->
               
  </div>   <!--end of center content -->               

    <div class="clear"></div>
    </div> <!--end of main content-->
	 
  <?php include('/../include/footer.php');?>


