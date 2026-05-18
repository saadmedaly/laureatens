<?php include('/../include/header.php');
    include('include/menu.php');?>
   
    <div class="center_content">  
    
     
    
    <div class="right_content">            
        
   <h2>Accueil Administrateur</h2> 
         <div class="form">
          <?php $attributes = array('class' => 'niceform');
         
          echo form_open('connexion/login',$attributes);?>
         
               
         <?php echo form_close('</div>');?>
      
     
     </div><!-- end of right content-->
            
                    
  </div>   <!--end of center content -->               
                    
                    
    
    
    <div class="clear"></div>
    </div> <!--end of main content-->
	
    
  <?php include('/../include/footer.php');?>