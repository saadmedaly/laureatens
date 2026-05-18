<?php
include("include/header.php");
include($controlleur . "/include/menu.php");


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<div class="center_content">  
<?php
    if(isset($confirmation)){
?>
        <script type="text/javascript">
    
    function controle()
    {
        
        return confirm("<?php echo $confirmation ; ?> ");
             }
</script>
<?php }
else
    {
?>
        <script type="text/javascript">
    
    function controle()
    {
        return true;
    }
</script>
<?php }
    
?>

    <div class="right_content">

        <?php
  
        
        echo heading($titre, '3')  ?>
    </div>  
  
    <div class="clear"></div>
     <div id="data">
            <?php
          if(isset($result)) 
              echo $result;
            ?>
        </div>
</div>
</div> <!--end of main content-->

<?php include('/include/footer.php'); ?>


<script language="javascript" type="text/javascript">  
    var filtres = {  
       on_keyup: true,
       filters_row_index: 1
     }  
    var table = new TF("table1",1,filtres);  
    table.AddGrid();  
</script> 