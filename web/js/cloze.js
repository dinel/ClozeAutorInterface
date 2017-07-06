/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
    ev.target.appendChild(document.getElementById(idWord));
    document.getElementById(idWord).classList.remove("filler-in-list");
    document.getElementById(idWord).classList.add("filler-in-text");
    ev.target.classList.remove("gap");
    ev.target.classList.add("filled-gap");
    filled_gaps++;
    
    if(filled_gaps === no_gaps) {
        $('#submit-result').removeClass("disabled");
    }
    
    idGap = ev.target.id;
    
    updateLog("Moving " + idWord + " to gap " + idGap);
}

$( document ).ready(function() {
    $("#words").shuffleChildren();
    updateLog("Opened text");
    
    $('#text').on('click', '.filled-gap', function() {
        $(this).addClass('gap');
        $(this).removeClass('filled-gap');
        $(this).children().last().removeClass('filler-in-text');
        $(this).children().last().addClass('filler filler-in-list');
        idWord = $(this).children().last().attr('id');
        $('#words').append($(this).children().last());
        filled_gaps--;
    
        if(filled_gaps !== no_gaps) {
            $('#submit-result').addClass("disabled");
        }
                
        idGap = $(this).attr('id');
        
        updateLog("Remove " + idWord + " from " + idGap);
        $("#words").shuffleChildren();
    });
    
    $(window).bind('beforeunload', function(){
        if(! saveClicked) {
            return 'Your work is not saved. Are you sure you want to continue?';
        }
    });
    
    $(window).blur(function() {
        updateLog("Changed window");
    });
    
    $(window).focus(function() {
        updateLog("Switched back");
    });
    
    $('#submit-result').click(function() {
        saveClicked = true;
        updateLog("Submitted solution");
    });
});

function updateLog(s) {
    var d = new Date();
    console.log("At " + d.getHours() + ":" + d.getMinutes() 
            + ":" + d.getSeconds() + ":" + s);
}

/* Inspired from https://css-tricks.com/snippets/jquery/shuffle-children/ */
$.fn.shuffleChildren = function() {
    $.each(this.get(), function(index, el) {
        var $el = $(el);
        var $find = $el.children();

        /*
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