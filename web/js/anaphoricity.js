/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* global idTest */

var saveClicked = false;
var operations = "";


$( document ).ready(function() {
    $(".gap-fillers").each(function() {
        $(this).shuffleChildren();
    });
    
    logAction("Opened text for anaphora " + idTest);    
    
    $('.filler').click(function() {
        var select = $.grep(this.className.split(" "), function(v, i){
                return v.indexOf('filler-') === 0;
        }).join();
        logAction("Selected " + $(this).attr('id'));
        $("." + select).removeClass("highlighted");
        $(this).addClass("highlighted");
        $(this).parent().removeClass("red-border");
        $(this).parent().removeClass("not-answered");
    });
    
    $(window).bind('beforeunload', function(){
        if(! saveClicked) {
            return 'Your work is not saved. Are you sure you want to continue?';
        }
    });
    
    $(window).blur(function() {
        logAction("Changed " + idTest);
    });
    
    $(window).focus(function() {
        logAction("Back " + idTest);
    });
    
    $('#submit-result').click(function(e) {
        var notAnswered = $('.not-answered');
        
        if(notAnswered.length === 0) {
            saveClicked = true;
            logAction("Submit the results " + idTest);
        } else {            
            $('.not-answered').addClass("red-border");
            
            $('#incomplete').show();
            setTimeout(function() {
                $('#incomplete').hide();
            }, 2000);
            e.preventDefault();
        }
    });
    
});

/* Inspired from https://css-tricks.com/snippets/jquery/shuffle-children/ */
$.fn.shuffleChildren = function() {
    $.each(this.get(), function(index, el) {
        var $el = $(el);
        var $find = $el.children();

        /* For random order */
        $find.sort(function() {
            return 0.5 - Math.random();
        });
        
        /* For alphabetical order 
        $find.sort(function(a, b) {
            var vA = $(a).text();
            var vB = $(b).text();
            return (vA < vB) ? -1 : (vA > vB) ? 1 : 0;
        });*/

        $el.empty();
        $find.appendTo($el);
    });
};

