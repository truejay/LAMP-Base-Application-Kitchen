/*
 * TextLimit - jQuery plugin for counting and limiting characters for input and textarea fields
 * 
 * pass '-1' as speed if you don't want slow char deletion effect. (don't just put 0)
 * Example: jQuery("Textarea").textlimit('span.counter',256)
 *
 * $Version: 2007.10.24 +r1
 * Copyright (c) 2007 Yair Even-Or
 * vsync.design@gmail.com
 */
jQuery.fn.textlimit=function(counter_el, thelimit, speed) {
    var charDelSpeed = speed || 15;
    var toggleCharDel = speed != -1;
    var toggleTrim = true;
    
    var that = this[0];
    updateCounter();
    
    function updateCounter(){
        jQuery(counter_el).text(thelimit - that.value.length);
    };
    
    this.keypress (function(e){ if( this.value.length >= thelimit && e.charCode != '0' ) e.preventDefault() })
    .keyup (function(e){
        updateCounter();
        if( this.value.length >= thelimit && toggleTrim ){
            if(toggleCharDel){
                // first, trim the text a bit so the char trimming won't take forever
                that.value = that.value.substr(0,thelimit+100);
                var init = setInterval
                    ( 
                        function(){ 
                            if( that.value.length <= thelimit){ init = clearInterval(init); updateCounter() }
                            else{ that.value = that.value.substring(0,that.value.length-1); jQuery(counter_el).text('trimming...  '+(thelimit - that.value.length)); };
                        } ,charDelSpeed 
                    );
            }
            else this.value = that.value.substr(0,thelimit);
        }
    });
    
};