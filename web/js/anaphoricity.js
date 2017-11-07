/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var saveClicked = false;
var operations = "";

function allowDrop(ev) {
    
    if(ev.target.id[0] === "w") {
        idGap = ev.target.id.substring(ev.target.id.indexOf("-") + 1, 
                                       ev.target.id.lastIndexOf("-"));
    } else {
        idGap = ev.target.id.substring(3);
    }
    
    refWord = ev.dataTransfer.getData("text").substring(
                    ev.dataTransfer.getData("text").indexOf("-") + 1, 
                    ev.dataTransfer.getData("text").lastIndexOf("-"));
    
    if(idGap === refWord) {
        ev.preventDefault();
    }
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);    
}

function drop(ev) {
    ev.preventDefault();
    var idWord = ev.dataTransfer.getData("text");
    var target = ev.target;
    
    // ensure that the gap is not filled already
    if(! target.classList.contains("gap")) {
        target.classList.remove('filler-in-text');
        target.classList.add('filler-in-list');        
        target.classList.add('filler');
        var parent = target.parentNode;
        $('#words'+parent.id.substring(3)).append(target);
        target = parent;
        
        /*
        idWord = $(this).children().last().attr('id');
        idGap = $(this).attr('id');
        
        updateLog("R:" + idWord + ":" + idGap);
        */
        $("#words"+parent.id.substring(3)).shuffleChildren();
    }    
    
    $('#'+target.id).find('.gap-fillers').removeClass("red-border");
    
    // check the parent
    if(document.getElementById(idWord).parentNode.classList.contains("filled-gap")) {
        document.getElementById(idWord).parentNode.classList.remove("filled-gap");
        document.getElementById(idWord).parentNode.classList.add("gap");
    }
    
    target.appendChild(document.getElementById(idWord));
    document.getElementById(idWord).classList.remove("filler-in-list");
    document.getElementById(idWord).classList.add("filler-in-text");
    target.classList.remove("gap");
    target.classList.add("filled-gap");   
    
    idGap = ev.target.id;
    
    updateLog("M:" + idWord + ":" + idGap);
}

$( document ).ready(function() {
    $(".gap-fillers").each(function() {
        $(this).shuffleChildren();
    });
    
    updateLog("Opened text");    
    
    $('#text').on('click', '.filled-gap', function() {
        $(this).addClass('gap');
        $(this).removeClass('filled-gap');
        $(this).children().last().removeClass('filler-in-text');
        $(this).children().last().addClass('filler filler-in-list');
        idGap = $(this).attr('id');
        idWord = $(this).children().last().attr('id');
        $('#words'+idGap.substring(3)).append($(this).children().last());        
        updateLog("R:" + idWord + ":" + idGap);
        $("#words").shuffleChildren();
    });
    
    $(window).bind('beforeunload', function(){
        if(! saveClicked) {
            return 'Your work is not saved. Are you sure you want to continue?';
        }
    });
    
    $(window).blur(function() {
        updateLog("Changed");
    });
    
    $(window).focus(function() {
        updateLog("Back");
    });
    
    $('#submit-result').click(function() {
        var notAnswered = $('.gap');
        
        if(notAnswered.length === 0) {
            alert("Going to submit the results");
        } else {
            notAnswered.each(function() {
               $(this).find('.gap-fillers').addClass("red-border");
            });
            
            $('#incomplete').show();
            setTimeout(function() {
                $('#incomplete').hide();
            }, 2000);
        }
    });
    
});

function updateLog(s) {
    var d = new Date();
    console.log("At " + d.getHours() + ":" + d.getMinutes() 
            + ":" + d.getSeconds() + ":" + s);
    operations += "[" + d.getHours() + ":" + d.getMinutes() 
            + ":" + d.getSeconds() + ":" + s + "]";
    console.log(operations);
}

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

