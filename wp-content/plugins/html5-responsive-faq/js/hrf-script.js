jQuery(function($){
   $(document).ready(function(){
      
      $('.hrf-title').click(function(){
         var my_content_id = $(this).attr('data-content-id');
         $('#' + my_content_id ).slideToggle();
         $(this).toggleClass(function(){
            if( $(this).is('.close-faq')){
               return 'open-faq';
            }else{
               return 'close-faq';
            }
            
         });
      }); //.hrf-title click
   });

});