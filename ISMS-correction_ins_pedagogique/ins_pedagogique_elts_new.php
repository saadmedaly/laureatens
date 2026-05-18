<?php include('/../include/header.php');
include('include/menu.php');
?>

<script>
var sommeCreditsImp=0;
var sommeCreditsPaire=0;
</script>

 <div class="container">
    <div class="col-xs-12 hl-left">
            
<button  onClick="imprimer('printable');" style="float:right; height: 30px; background-color:#cceac4; width: 160px; margin-right:185px; margin-top:50px;"><b> Imprimer le fiche </b></button>

     
        <div id="printable">
            
            <table  border="0" width="100%">
             <tr>
                 <td><img src="../../images/<?php echo $parametres['logo'] ?>" height="70" width="90" ></td>
                  
                  <td> 
                      <table  border="0" style="line-height: 10px">
                          <tr>
                              <td align="center" style="font-size: 120%;font-family: Times;"><b><?php echo $parametres['nom'] ?></b></td>
                          </tr>
                          <tr>
                          <td>
                          <table  border="0" style="line-height: 7px">
                          <tr>
                              <td style="font-size: 50%;border-top: 1px solid black"><?php echo $parametres['adresse'] ?>, Téléphone : <?php echo $parametres['telephone'] ?> Mobile : <?php echo $parametres['telephone_2'] ?>, Email : <?php echo $parametres['email'] ?></td>
                          </tr>
                          <tr>
                              <td style="font-size: 50%">Boite Postale : <?php echo $parametres['boite_postale'] ?>, Siteweb : <?php echo $parametres['siteweb'] ?>, <?php echo $parametres['ville'] ?>-<?php echo $parametres['pays'] ?></td>
                          </tr>
                          </table>
                              </td>
                          <tr/>
                      </table>
                  </td> 
                   <td><!--<img src="../../images/ustm.png" height="70" width="100">--></td>
             </tr>
             </table>           
           <table  border="0" width="100%">
               <tr><td  style="font-size: 120%" align="center"><b>Fiche d'inscription pédagogique <?php echo($annee."-".($annee +1));?></b></td></tr>
                <tr><td style="font-size: 70%" align="center">LMD - Système (License - Master - Doctorat)</td></tr>
           </table>
            
          
            <br> <br>
            <table  border="0" width="100%" style="border-collapse: collapse;line-height: 8px">
                <tr>
                    <td>
                
          <table  width="100%" style="border-spacing: 4px; marign: 20px 20px 20px; border-bottom: 2px solid #E26C09;border-top: 2px solid #E26C09;font-family:Times; font-size:11.25px;line-height: 12px; " cellspacing="0"> 
              
              <tr>
                 
               <td><?php echo 'Nom de l\'Etudiant (e) :      '; ?></td>
               <td><strong><?php echo $info['nom']; ?></strong></td>
               <td><?php echo 'Prénom(s) :      '; ?></td>
               <td><strong><?php echo $info['prenom']; ?></strong></td>
               <td></td> <td></td> <td></td> <td></td>
             </tr>
             <tr>
               <td id="contenu"><?php echo 'Date et lieu de Naissance :      '; ?></td>
               <td id="contenu"><strong><?php echo date('d/m/Y',strTotime($info['dateNaissance'])).' '.$info['lieuNaissance']; ?></strong></td>
               <td id="contenu"><?php echo 'Niveau :      L'.$niveau ; ?></td>
               <!--
               <td id="contenu">
                   
                   <table  border="0" style="font-family:Times; font-size:10px;line-height: 2px" cellspacing="5">
                       <tr>
                           <td id="contenu"><? php  echo 'L'.$niveau ; ?></td>
                           <td id="contenu"><? php echo 'Genre : ' ; ?></td>
                           <td id="contenu"><? php echo $info['genre'] ; ?></td>
                           <td id="contenu"><? php echo 'Date d\'inscription :      '; ?></td>
                           <td id="contenu"><? php echo $info['dateInscription']; ?></td>
                        
                       </tr>
                   </table>
                          
                  
               </td>
               -->
               
               <td id="contenu"><strong><?php  /*echo $info['niveau'] ;*/ ?></strong></td>
                           <td id="contenu"><?php echo 'Genre : ' ; ?></td>
                           <td id="contenu"><strong><?php echo $info['genre'] ; ?></strong></td>
                           <td id="contenu"><?php echo 'Date d\'inscription :      '; ?></td>
                           <td id="contenu"><strong><?php echo date("d/m/Y", strtotime($info['dateInscription'])); ?></strong></td>
             </tr>
             <tr>
               <td id="contenu"><?php echo 'Filière de spécialité :      '; ?></td>
               <td id="contenu"><strong><?php echo $info['programme']; ?></strong></td>
               <td id="contenu"><?php// echo 'Semestre   '; ?></td>
               <!--
               <td id="contenu">
                   <table  border="0" border="0" style="font-family:Times; font-size:10px;line-height: 2px"cellspacing="5">
                       <tr>
                           <td id="contenu"><?php// echo $numSem ; ?></td>
                           <td id="contenu"><?php echo 'Numero d\'inscription : ' ; ?></td>
                           <td id="contenu"><?php echo $info['matriculeEtudiant'] ; ?></td>
                       </tr>
                   </table>               
               </td>
               -->
                <td id="contenu"><strong><?php //echo $numSem ; ?></strong></td>
                           <td id="contenu"><?php echo 'N° inscription : ' ; ?></td>
                           <td id="contenu"><strong><?php echo $info['matriculeEtudiant'] ; ?></strong></td>
             </tr>
                    
        </table>
          </td>          
         </tr>
         </table>
            <br>
            <?php
             $s=0;
       
             if($semestre==1){
             $s=1;}
            elseif($semestre==2){
                $s=3;
            }
            elseif($semestre==3){
                $s=5;
            }
            elseif($semestre==4){
                
       }?>
            <script>table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}
</style></script>
            <table  class="table-striped" border="1" style="font-size:1.5em;" width="100%"><tr><th align="center">Semestres impairs</th><th align="center">Semestres pairs </th></tr>
              <!-- -->  
              <?php   $attributes = array('class' => 'niceform','id'=>'myCoolForm','onsubmit'=>'return Submit_confirm()');
          
                echo form_open('scolarite/fiche_inscription_pedagogique',$attributes);?>
              <input type="hidden" name="annee" value="<?=$annee?>">
              <input type="hidden" name="niveau" value="<?=$niveau?>"><!-- add by MedBAkar 18-09-2020 -->
              <tr><td align="top"><table  class="table-striped" style="font-size:0.5em;" border="1" width="100%" >
                           <tr>
                             <td >Choix</td>
                             <td >Module</td>
                             <td  colspan="1">Element</td>
                              <td >Ects</td>
                             <td >S</td>
                             
                             
                            
                             </tr>
                             
                         
                          
                         <?php
                                
               echo'<input name="matriculeEtudiant" type="hidden" value="'.$info['matriculeEtudiant'].'"/>';
                         if(!empty($moduleR_impairelist))
                            for ($i = 0; $i < count($moduleR_impairelist); ++$i) { ?>
                              <tr>
                                
                                 <?php echo'<td> <input  type="hidden" name="sigleImpaire[]" value="'.$moduleR_impairelist[$i]->sigle.'" > <input  type="checkbox" name="sigleImpaire[]" value="'.$moduleR_impairelist[$i]->sigle.'"  disabled=disabled checked  onclick="sommes_Credits_imp(this.checked,'.$moduleR_impairelist[$i]->ects.')" ></td>';
//                                 $sommes_credit_imp_checked+=$moduleR_impairelist[$i]->ects;
                                 ?>
                                  <script>sommeCreditsImp+=<?=$moduleR_impairelist[$i]->ects?></script>
                                   <td><?php echo $moduleR_impairelist[$i]->idModule; ?></td>
                                   <td><?php echo '['.$moduleR_impairelist[$i]->sigle .']'. $moduleR_impairelist[$i]->titre; ?></td>
                                   <td><?php echo $moduleR_impairelist[$i]->ects; ?></td>
                                   <td><?php echo $moduleR_impairelist[$i]->semestre; ?></td>
                                    <?php   
                                   echo'<input name="semestreImpaire[]" type="hidden" value="'.$moduleR_impairelist[$i]->semestre.'" />';?>
                                <?php   //echo '<td>'.($s-2).'</td>' ;
                                ?>
                              </tr>
                              <?php } ?>
                  
                           <?php
                         
                         if(!empty($modules_a_etudies_impairelist))         
                           for ($i = 0; $i < count($modules_a_etudies_impairelist); ++$i) { ?>
                              <tr>
                                <?php
                                     if(($moduleR_impairelist !=null) ){
                                  echo '<td id="contenu" > <input  type="checkbox" name="sigleImpaire[]" value="'.$modules_a_etudies_impairelist[$i]->sigle.'"  onclick="sommes_Credits_imp(this.checked,'.$modules_a_etudies_impairelist[$i]->ects.')" checked ></td>' ;
                                  }else{
                                   
                                     echo '<td id="contenu"> <input type="hidden" name="sigleImpaire[]" value="'.$modules_a_etudies_impairelist[$i]->sigle.'"><input type="checkbox" name="sigleImpaire[]" value="'.$modules_a_etudies_impairelist[$i]->sigle.'"  disabled=disabled checked onclick="sommes_Credits_imp(this.checked,'.$modules_a_etudies_impairelist[$i]->ects.')" ></td>' ;
//                                   $sommes_credit_imp_checked+=$modules_a_etudies_impairelist[$i]->ects;
                               ?><script>sommeCreditsImp+=<?=$modules_a_etudies_impairelist[$i]->ects?></script>
                                     <?php
                                  }
                             ?>
                                   <td><?php echo $modules_a_etudies_impairelist[$i]->idModule; ?></td>
                                    <td><?php echo '['.$modules_a_etudies_impairelist[$i]->sigle .']'. $modules_a_etudies_impairelist[$i]->titre; ?></td>
                                   <td><?php echo $modules_a_etudies_impairelist[$i]->ects; ?></td>
                                <?php   echo '<td>'.$s.'</td>' ;
                                   echo'<input name="semestreImpaire[]" type="hidden" value="'.$s.'"/>';?>
                              </tr>
                            <?php }?>
                              <?php
                           
                           echo'</table></td>';
                          
                       ?>   
                              <td   align="top"><table  class="table-striped" style="font-size:0.5em;"  border="1"  width="100%" >
                           
                             <td >Choix</td>
                             <td >Module</td>
                             <td colspan="1">Element</td>
                              <td >Ects</td>
                             <td >S</td>
                            
                             
                             
                            
                             </tr>
                            
                             <?php 
                             if(!empty($moduleR_pairelist))
                             for ($i = 0; $i < count($moduleR_pairelist); ++$i) { ?>
                              <tr>
                                
                                  <?php  echo'<td> <input  value="'.$moduleR_pairelist[$i]->sigle.'" type="hidden" name="siglePaire[]" ><input  value="'.$moduleR_pairelist[$i]->sigle.'" type="checkbox" name="siglePaire[]"   disabled=disabled checked  onclick="sommes_Credits_paire(this.checked,'.$moduleR_pairelist[$i]->ects.')" ></td>';
                                  ?>
                                  <script>sommeCreditsPaire+=<?=$moduleR_pairelist[$i]->ects?></script>
                                   <td><?php echo $moduleR_pairelist[$i]->idModule; ?></td>
                                    <td><?php echo '['.$moduleR_pairelist[$i]->sigle .']'. $moduleR_pairelist[$i]->titre; ?></td>
                                   <td><?php echo $moduleR_pairelist[$i]->ects; ?></td>
                                   <td><?php echo $moduleR_pairelist[$i]->semestre; ?></td>
                                    <?php   
                                   echo'<input name="semestrePaire[]" type="hidden" value="'.$moduleR_pairelist[$i]->semestre.'"/>';?>
                                <?php  // echo '<td>'.(($s+1)-2).'</td>' ;?>
                              </tr>
                         <?php } ?>
                              <?php  
                              if(!empty($modules_a_etudies_pairelist))
                               for ($i = 0; $i < count($modules_a_etudies_pairelist); ++$i) { 
                             echo'<tr>';
                                
                                     if(!empty($moduleR_pairelist )){
                                  echo '<td id="contenu" > <input type="checkbox" name="siglePaire[]" value="'.$modules_a_etudies_pairelist[$i]->sigle.'" onclick="sommes_Credits_paire(this.checked,'.$modules_a_etudies_pairelist[$i]->ects.')" checked ></td>' ;
                               }else{
                                   
                                     echo '<td id="contenu"> <input  type="hidden" name="siglePaire[]" value="'.$modules_a_etudies_pairelist[$i]->sigle.'" ><input  type="checkbox" name="siglePaire[]" value="'.$modules_a_etudies_pairelist[$i]->sigle.'"   disabled=disabled checked onclick="sommes_Credits_paire(this.checked,'.$modules_a_etudies_pairelist[$i]->ects.')" ></td>' ;
                                 ?><script>sommeCreditsPaire+=<?=$modules_a_etudies_pairelist[$i]->ects?></script>
                                     <?php  
                               }
                              ?>
                                   <td><?php echo $modules_a_etudies_pairelist[$i]->idModule; ?></td>
                                    <td><?php echo '['.$modules_a_etudies_pairelist[$i]->sigle .']'. $modules_a_etudies_pairelist[$i]->titre; ?></td>
                                   <td><?php echo $modules_a_etudies_pairelist[$i]->ects; ?></td>
                                <?php   echo '<td>'.($s+1).'</td>' ;?>
                                    <?php   
                                   echo'<input name="semestrePaire[]" type="hidden" value="'.($s+1).'"/>';?>
                              </tr>
                         <?php }?>
                              <?php
                           echo'</table></td></tr>';
                          
                       ?>   
            </table>
                                  
                                  </div>          
                     <td class="submit">
                       
                 <?php
  
                 $pw = array('type' => 'submit', 'name' => 'submit', 'id'=>'submit', 'value'=>'Inscrire');
                 echo form_input($pw);?>
                 </td>
               
          <?php
                   
                   echo form_fieldset_close();
        echo form_close('</div>');?>
           </td><td><td></tr>
            
        
        <!-- end of right content-->
</div>   <!--end of center content -->               
<div class="clear"></div>
</div> <!--end of main content-->


<?php include('/../include/footer.php'); ?>
<script type="text/javascript">

<!--
function imprimer(id){
str=document.getElementById(id).innerHTML
newwin=window.open('','printwin','left=100,top=100,width=400,height=400')
newwin.document.write('<HTML>\n <HEAD>\n')
// Hafedh
// Supression de l'entete lors de l'impression
newwin.document.write('<style>@page { size: auto;  margin: 0mm; }</style>\n')
newwin.document.write('<style>body {-webkit-print-color-adjust: exact;}  </style>\n')


//newwin.document.write('<link rel="stylesheet" href="<?php echo base_url();?>css/printable.css" />\n');
newwin.document.write('<TITLE></TITLE>\n')
newwin.document.write('<script>\n')
newwin.document.write('function chkstate(){\n')
newwin.document.write('if(document.readyState=="complete"){\n')
newwin.document.write('window.close()\n')
newwin.document.write('}\n')
newwin.document.write('else{\n')
newwin.document.write('setTimeout("chkstate()",2000)\n')
newwin.document.write('}\n')
newwin.document.write('}\n')
newwin.document.write('function print_win(){\n')
newwin.document.write('window.print();\n')
newwin.document.write('chkstate();\n')
newwin.document.write('}\n')
newwin.document.write('<\/script>\n')
newwin.document.write('</HEAD>\n')
newwin.document.write('<BODY onload="print_win()">\n')
newwin.document.write('<br>' + str)
newwin.document.write('</BODY>\n')
newwin.document.write('</HTML>\n')
newwin.document.close()
}
//-->



function sommes_Credits_imp(status,value){
//    alert(status);
//    alert(value);
    if(status==true) sommeCreditsImp+=value;
    else
    sommeCreditsImp-=value;
//alert(sommeCreditsImp);
//    document.getElementById('sommeCreditsImp').innerHTML="<span>"+sommeCreditsImp+"</span>";
    //document.write(sommeCreditsImp);
}

function sommes_Credits_paire(status,value){
    //alert(status);
    //alert(value);
//    alert('le credit paire '+sommeCreditsPaire);
    if(status==true) sommeCreditsPaire+=value;
    else
    sommeCreditsPaire-=value;
//    document.getElementById('sommeCreditsPaire').innerHTML="<span>"+sommeCreditsPaire+"</span>";
//document.write(sommeCreditsImp);
}



//var el = document.getElementById('myCoolForm');
//var t=true;
//el.addEventListener('submit', function(){
//if(sommeCreditsImp>=40 && sommeCreditsPaire >= 40)
//    return confirm('Attention !\n Le total du crédits des semestres impairs est: '+sommeCreditsImp+', celui des semestres Pairs est: '+sommeCreditsPaire+' voulez-vous continué ?');
//else if(sommeCreditsImp >= 4)
//    return confirm('Attention !\n Le total du crédits des semestres impairs est: '+sommeCreditsImp+' voulez-vous continué ?');
//else if(sommeCreditsPaire >= 4)
//    return confirm('Attention !\n Le total du crédits des semestres Pairs est: '+sommeCreditsPaire+' voulez-vous continué ?');
//
//});
function Submit_confirm(){
    if(sommeCreditsImp>=40 && sommeCreditsPaire >= 40)
    return confirm('Attention !\n Le total du crédits des semestres impairs est: '+sommeCreditsImp+', celui des semestres Pairs est: '+sommeCreditsPaire+' voulez-vous continué ?');
else if(sommeCreditsImp >= 40)
    return confirm('Attention !\n Le total du crédits des semestres impairs est: '+sommeCreditsImp+' voulez-vous continué ?');
else if(sommeCreditsPaire >= 40)
    return confirm('Attention !\n Le total du crédits des semestres Pairs est: '+sommeCreditsPaire+' voulez-vous continué ?');

} 
</script>