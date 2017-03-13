<?php
function fn_hrf_styles()
{

   $main_title_size   = ( get_option('hrf_main_title_size') != '' ) ? get_option('hrf_main_title_size', '18px') : '18px';
   $question_color    = ( get_option('hrf_question_text_color') != '' ) ? get_option('hrf_question_text_color', '#444444') : '#444444';
   $question_bgcolor  = ( get_option('hrf_question_bgcolor') != '' ) ? get_option('hrf_question_bgcolor', '#ffffff') : '#ffffff';
   $question_size     = ( get_option('hrf_question_text_size') != '' ) ? get_option('hrf_question_text_size', '18px') : '18px';
   $answer_color      = ( get_option('hrf_answer_text_color') != '' ) ? get_option('hrf_answer_text_color', '#444444') : '#444444';
   $answer_bgcolor    = ( get_option('hrf_answer_bgcolor') != '' ) ? get_option('hrf_answer_bgcolor', '#ffffff') : '#ffffff';
   $answer_size       = ( get_option('hrf_answer_text_size') != '' ) ? get_option('hrf_answer_text_size', '14px') : '14px';
   $bullets_style     = ( get_option('bullets_style') != '' ) ? get_option('bullets_style', 'light') : 'light';
   $bullets_bgcolor   = ( get_option('hrf_bullets_bgcolor') != '' ) ? get_option('hrf_bullets_bgcolor', '#444444') : '#444444';
   $faqs_bottom_gap   = ( get_option('hrf_faqs_bottom_gap') != '' ) ? get_option('hrf_faqs_bottom_gap', '0px') : '0px';
   $heading_style     = ( get_option('hrf_question_headingtype') != '' ) ? get_option('hrf_question_headingtype', 'h3') : 'h3';
   $css = '<style type="text/css">';
   
   $css .= '
            h2.frq-main-title{
               font-size: '.$main_title_size.';
            }
            .hrf-entry{
               border:none !important;
               margin-bottom: '.$faqs_bottom_gap.' !important;
               padding-bottom: 0px !important;
            }
            .hrf-content{
               display:none;
               color: '.$answer_color.';
               background: '.$answer_bgcolor.';
               font-size: '.$answer_size.';
               padding: 10px;
               padding-left: 50px;
               word-break: normal;
            }
            '.$heading_style.'.hrf-title{
               font-size: '.$question_size.' ;
               color: '.$question_color.';
               background: '.$question_bgcolor.';
               padding: 10px ;
               padding-left: 50px;
               margin: 0;
               -webkit-touch-callout: none;
               -webkit-user-select: none;
               -khtml-user-select: none;
               -moz-user-select: none;
               -ms-user-select: none;
               user-select: none;
               outline-style:none;
            }
            .hrf-title.close-faq{
               cursor: pointer;
            }
            .hrf-title.close-faq span{
               width: 30px;
               height: 30px;
               display: inline-block;
               position: relative;
               left: 0;
               top: 8px;
               margin-right: 12px;
               margin-left: -42px;
               background: '.$bullets_bgcolor.' url('.plugins_url( 'html5-responsive-faq/images/open.png' ).') no-repeat center center;
            }
            }.hrf-title.open-faq{
            
            }
            .hrf-title.open-faq span{
               width: 30px;
               height: 30px;
               display: inline-block;
               position: relative;
               left: 0;
               top: 8px;
               margin-right: 12px;
               margin-left: -42px;
               background: '.$bullets_bgcolor.' url('.plugins_url( 'html5-responsive-faq/images/close.png' ).') no-repeat center center;
            }
            .hrf-entry p{
            
            }
            .hrf-entry ul{
            
            }
            .hrf-entry ul li{
            
            }';
   $css .= '</style>';
   echo $css;
}
add_action( 'wp_footer','fn_hrf_styles' );