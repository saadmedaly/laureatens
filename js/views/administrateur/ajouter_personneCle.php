
<?php include('/../include/header.php');
include('include/menu.php');
?> 


<div class="center_content">  

    <div class="right_content">  



        <?php
        echo heading('Liste des prsonnes clés qui recevront un e-mail lors de
            tout changement de note : ', '3');

        $attributes = array('class' => 'niceform');
        
         echo form_open('administrateur/enregistrer_email', $attributes);

        ?>
           <table id="rounded-corner" summary="personneCle">
            
            <thead>
                <tr>
                    <th id="col" class="rounded">Personne-clé</th>
                    <th id="col" class="rounded">Envoi actif</th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php                        
                                function OuiNon($actif) {
				if ($actif == 1) {
					return "Oui" ;
					} else 
					return "Non";
				}
				for($i=0;$i<  count($personneCle)-1;$i=$i+2)
                {
                echo '<tr>
                    <td>'.$personneCle[$i].'</td>
                    <<td>'.OuiNon($personneCle[$i+1]).'</td>
                    </tr>';
                }
                ?>
                        
            </tbody>
        </table>
  <table id="cycle" style="width: 600px;">
       
         <tr><td><br><br></td></tr>
        
            <tr>
                <th>Email<span style="color:red;font-weight:bold;font-size:14px;">*</span></th>
                <td  style="width: 300px;"><?php $input = array('type' => 'text', 'size' => '30', 'name'=>'email') ;
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
