
<?php include('/../include/header.php');
include('include/menu.php');
?> 
<!-- Mai 2013 nouveau fichier 2.2.1 construit à partir de get_grade.php -->

<div class="center_content">  

    <div class="right_content">  



        <?php
        echo heading('Grades existants ', '3');

        $attributes = array('class' => 'niceform');

        ?>
          <table>
         <tr><td><br><br></td></tr>
         </table>
        <table id="rounded-corner" summary="grade">
            <thead>
                <tr>
                    <th id="col" class="rounded">Id Grade</th>
                    <th id="col" class="rounded">Nombre de crédits</th>
                </tr>
            </thead>
            <tbody id="listeClasse">
                <?php                        
                for($i=0;$i<sizeof($gradeInfo)-1;$i=$i+2)
                {
                echo '<tr>
                    <td>'.$gradeInfo[$i].'</td>
                    <td>'.$gradeInfo[$i+1].'</td>
                    </tr>';
                }
                ?>
                        
            </tbody>
        </table>
  <?php 
  /*
  $title = '<span class="bt_green_lft"></span><strong>Ajouter un élément</strong><span class="bt_green_r"></span>';
          $attributes = 'class="bt_green"';
          echo anchor('administrateur/ajouter_grade', $title, $attributes);
   */       
 
          ?>
    </div>                      
</div>         

<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php');
?>
