setTimeout(function(){
    if($('iframe.cheditor-editarea').length){
        $('iframe.cheditor-editarea').each(function(){
            $(this).css('background-color','rgb(6,13,27)')
            $(this).contents().find('body').css('color','rgba(255,255,255)');
        });
    }
},2000);