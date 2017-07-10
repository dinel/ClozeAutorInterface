/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* global filled_gaps, no_gaps */

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
    
    updateLog("M:" + idWord + ":" + idGap);
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
        saveClicked = true;
        updateLog("Submit");
        
        var params = [];
        params["operations"] = operations;
        post("/thank-you", params, "post");
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

/**
 * From https://stackoverflow.com/questions/133925/javascript-post-request-like-a-form-submit/5533477
 */
function post(path, params, method) {
    method = method || "post"; // Set method to post by default if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }

    document.body.appendChild(form);
    form.submit();
}