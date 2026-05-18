 
<?php include('/../include/header.php');
    include('include/menu.php');?>
   
    <div class="center_content">  
  
    <div class="right_content">            
        
          <div class="  <?php echo $typeBox;?>" >
                  <?php echo $informations;?>
              
          </div>
        <?php
        if(isset($local))
        {
            $title = '<span class="bt_green_lft"></span><strong>Ajouter un nouveau local.</strong><span class="bt_green_r"></span>';
                $attributes = 'class="bt_green"';
                echo anchor('administrateur/get_local', $title, $attributes);
        }
        
        if(isset($creerEmploye))
        {
            echo '<table>
                <tr style="height:20px;">
                </tr>
                <tr>
                    <td>
                        <label id="creerEmploye2" >Code d\'accès :</label>
                    </td>
                    <td>
                        <label id="creerEmploye">'.$login.'</label>
                    </td>
                </tr>
                 <tr>
                    <td>
                        <label  id="creerEmploye2">Mot de passe :</label>
                    </td>
                    <td>
                        <label id="creerEmploye">'.$password.'</label>
                    </td>
                </tr>
            </table>';
            $title = '<span class="bt_green_lft"></span><strong>Ajouter un nouvel Employé.</strong><span class="bt_green_r"></span>';
                $attributes = 'class="bt_green"';
                echo anchor('administrateur/creer_employe', $title, $attributes);
        }
        ?>
 
     </div><!-- end of right content-->
               
  </div>   <!--end of center content -->               

    <div class="clear"></div>
    </div> <!--end of main content-->
	 
  <?php include('/../include/footer.php');?>


