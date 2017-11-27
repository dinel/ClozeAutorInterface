/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* global filled_gaps, no_gaps, idTest */

var saveClicked = false;
var operations = "";

function allowDrop(ev) {
    ev.preventDefault();
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
        
        // in some cases the target is the div that contains the filler
        if(target.classList.contains("filled-gap")) {
            target = target.lastChild;
        }
        
        target.classList.remove('filler-in-text');
        target.classList.add('filler-in-list');        
        target.classList.add('filler');
        target.setAttribute('draggable', 'true');
        var idWordLog = target.id;
        
        var parent = target.parentNode;
        $('#words').append(target);
        target = parent;
                        
        var idGapLog = parent.id;        
        logAction("Removed:" + idWordLog + ":" + idGapLog);
        
        $("#words").shuffleChildren();
        filled_gaps--;
    }
    
    // check the parent
    if(document.getElementById(idWord).parentNode.classList.contains("filled-gap")) {
        document.getElementById(idWord).parentNode.classList.remove("filled-gap");
        document.getElementById(idWord).parentNode.classList.add("gap");
    }
    
    target.appendChild(document.getElementById(idWord));
    document.getElementById(idWord).setAttribute('draggable', 'false');
    document.getElementById(idWord).classList.remove("filler-in-list");
    document.getElementById(idWord).classList.add("filler-in-text");
    target.classList.remove("gap");
    target.classList.add("filled-gap");
    filled_gaps++;
    
    if(filled_gaps === no_gaps) {
        $('#submit-result').removeClass("disabled");
    }
    
    idGap = ev.target.id;
    
    logAction("Moved:" + idWord + ":" + idGap);
}

$( document ).ready(function() {
    $("#words").shuffleChildren();
    logAction("Opened cloze test " + idTest);
    
    $('#text').on('click', '.filled-gap', function() {
        $(this).addClass('gap');
        $(this).removeClass('filled-gap');
        $(this).children().last().removeClass('filler-in-text');
        $(this).children().last().addClass('filler filler-in-list');
        $(this).children().last().attr('draggable', 'true');
        idWord = $(this).children().last().attr('id');
        $('#words').append($(this).children().last());
        filled_gaps--;
    
        if(filled_gaps !== no_gaps) {
            $('#submit-result').addClass("disabled");
        }
                
        idGap = $(this).attr('id');
        
        logAction("Removed:" + idWord + ":" + idGap);
        $("#words").shuffleChildren();
    });
    
    $(window).bind('beforeunload', function(){
        if(! saveClicked && idTest != -1) {
            return 'Your work is not saved. Are you sure you want to continue?';
        }
    });
    
    $(window).blur(function() {
        logAction("Changed to a different window");
    });
    
    $(window).focus(function() {
        logAction("Back to quiz window " + idTest);
    });
    
    $('#submit-result').click(function() {
        saveClicked = true;
        logAction("Submit cloze test " + idTest);        
    });
    
    $('#try-again').click( function() {
        show_exercise();
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

function show_results() {
    $('#results').show();
    $('#text').hide();
    $('#words').hide();
}

function show_exercise() {
    $('#results').hide();
    $('#text').show();
    $('#words').show();
}

/* Inspired from https://css-tricks.com/snippets/jquery/shuffle-children/ */
$.fn.shuffleChildren = function() {
    $.each(this.get(), function(index, el) {
        var $el = $(el);
        var $find = $el.children();

        /* For random order
        $find.sort(function() {
            return 0.5 - Math.random();
        });
        */
        
        $find.sort(function(a, b) {
            var vA = $(a).text();
            var vB = $(b).text();
            return (vA < vB) ? -1 : (vA > vB) ? 1 : 0;
        });

        $el.empty();
        $find.appendTo($el);
    });
};

