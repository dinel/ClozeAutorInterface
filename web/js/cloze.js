/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var saveClicked = false;

function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {
    ev.preventDefault();
    var data = ev.dataTransfer.getData("text");
    ev.target.appendChild(document.getElementById(data));
    document.getElementById(data).classList.remove("filler-in-list");
    document.getElementById(data).classList.add("filler-in-text");
    ev.target.classList.remove("gap");
    ev.target.classList.add("filled-gap");
    filled_gaps++;
    
    if(filled_gaps === no_gaps) {
        $('#submit-result').removeClass("disabled");
    }
}

$( document ).ready(function() {        
    $('#text').on('click', '.filled-gap', function() {
        $(this).addClass('gap');
        $(this).removeClass('filled-gap');
        $(this).children().last().removeClass('filler-in-text');
        $(this).children().last().addClass('filler filler-in-list');
        $('#words').append($(this).children().last());
        filled_gaps--;
    
        if(filled_gaps !== no_gaps) {
            $('#submit-result').addClass("disabled");
        }
    });
    
    $(window).bind('beforeunload', function(){
        if(! saveClicked) {
            return 'Your work is not saved. Are you sure you want to continue?';
        }
    });
    
    $('#submit-result').click(function() {
        saveClicked = true;
    });
});