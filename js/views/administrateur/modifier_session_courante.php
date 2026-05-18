<?php include('/../include/header.php');
include('include/menu.php');

?>


<div class="center_content">  



    <div class="right_content">            


        <?php
        echo heading('Modifier le semestre courant:', '3');

        echo form_open('administrateur/modifier_session_courante');
        echo form_fieldset();
        ?>
        <table class="form">
            <tr>

                <td><?php echo form_label('Semestre <span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?> 
                </td>
                <td>
                    <select  name="session">
                        <?php

                        echo '<option value="02"';

                        if ($semestre[0] == "02") {
                            echo ' selected';
                        }
                        echo '> Été</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="03"';
                        if ($semestre[0] == "03") {
                            echo ' selected';
                        }
                        echo '> Automne</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="01"';
                        if ($semestre[0] == "01") {
                            echo ' selected';
                        }
                        echo '> Printemps</option>' . "\n";
                        ?>
                    </select>                           


                </td>


                <td><?php echo form_label('Année <span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?> 
                </td>
                <td><select  name="annee">

                        echo '> 2011</option>'."\n";
                        ?>
                        <?php
                        echo '<option value="2011"';
                        if ($annee[0] == "2011") {
                            echo ' selected';
                        }
                        echo '> 2011</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2012"';
                        if ($annee[0] == "2012") {
                            echo ' selected';
                        }
                        echo '> 2012</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2013"';
                        if ($annee[0] == "2013") {
                            echo ' selected';
                        }
                        echo '> 2013</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2014"';
                        if ($annee[0] == "2014") {
                            echo ' selected';
                        }
                        echo '> 2014</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2015"';
                        if ($annee[0] == "2015") {
                            echo ' selected';
                        }
                        echo '> 2015</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2016"';
                        if ($annee[0] == "2016") {
                            echo ' selected';
                        }
                        echo '> 2016</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2017"';
                        if ($annee[0] == "2017") {
                            echo ' selected';
                        }
                        echo '> 2017</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2018"';
                        if ($annee[0] == "2018") {
                            echo ' selected';
                        }
                        echo '> 2018</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2019"';
                        if ($annee[0] == "2019") {
                            echo ' selected';
                        }
                        echo '> 2019</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2020"';
                        if ($annee[0] == "2020") {
                            echo ' selected';
                        }
                        echo '> 2020</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2021"';

                        if ($annee[0] == "2021") {
                            echo ' selected';
                        }
                        echo '> 2021</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2022"';

                        if ($annee[0] == "2022") {
                            echo ' selected';
                        }
                        echo '> 2022</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2023"';
                        if ($annee[0] == "2023") {
                            echo ' selected';
                        }
                        echo '> 2023</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2024"';
                        if ($annee[0] == "2024") {
                            echo ' selected';
                        }
                        echo '> 2024</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2025"';
                        if ($annee[0] == "2025") {
                            echo ' selected';
                        }
                        echo '> 2025</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2026"';
                        if ($annee[0] == "2026") {
                            echo ' selected';
                        }
                        echo '> 2026</option>' . "\n";
                        ?>

                        <?php
                        echo '<option value="2027"';
                        if ($annee[0] == "2027") {
                            echo ' selected';
                        }
                        echo '> 2027</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2028"';
                        if ($annee[0] == "2028") {
                            echo ' selected';
                        }
                        echo '> 2028</option>' . "\n";
                        ?>

                        <?php
                        echo '<option value="2029"';
                        if ($annee[0] == "2029") {
                            echo ' selected';
                        }
                        echo '> 2029</option>' . "\n";
                        ?>
                        <?php
                        echo '<option value="2030"';
                        if ($annee[0] == "2030") {
                            echo ' selected';
                        }
                        echo '> 2030</option>' . "\n";
                        ?>                           
                </td>
            </tr>
        <script>
	$(function() {
		$( "#dateDebut" ).datepicker({
			showOtherMonths: true,
			selectOtherMonths: true
		});
                $( "#dateFin" ).datepicker({
			showOtherMonths: true,
			selectOtherMonths: true
		});
	});
	</script>
            <tr>
                <td>
                    <?php echo form_label('Date du début des cours<span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?> 
                </td>
                <td>
                    <?php
                    $input = array('type' => 'text', 'id' => 'dateDebut', 'name' => 'dateDebut');
					if($dateDebut[0]==Null)
					{
						echo form_input($input,'','required');
					}
					else
					{
						echo form_input($input,$dateDebut[0],'required');
					}
                    ?>
                </td>
                <td style="width:60px;">
                    <?php echo form_label('Date de la fin des cours<span style="color:red;font-weight:bold;font-size:14px;">*</span>'); ?> 
                </td>
                <td>
                    <?php
                    $input = array('type' => 'text', 'id' => 'dateFin', 'name' => 'dateFin');
                    echo form_input($input,$dateFin[0],'required');
                    ?>
                </td>
            </tr>
            <tr>                     
                <td class="submit">
                        <?php $pw = array('type' => 'submit', 'name' => 'submit', 'id' => 'submit', 'value' => 'Modifier');
                        echo form_input($pw);
                        ?>
                </td>
            </tr>                
        </table>
                        <?php echo form_fieldset_close();
                        echo form_close('</div>');
                        ?>      
    </div>  <!--right content-->
    <div class="clear"></div>
</div> <!--end of main content-->

<?php include('/../include/footer.php'); ?>


