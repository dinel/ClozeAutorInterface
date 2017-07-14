/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$( document ).ready(function() {
    $("input:radio").change(function() {
        var input = $( "input:radio" ).toArray();
        var counter = 0;

        for(var i = 0; i < input.length; i++) {
            if(input[i].checked === true) {
                if(input[i].nextSibling.textContent.startsWith("Yes")) counter++;
            }
        }

        if(counter === 6) {
            $('#confirm').removeClass("disabled");
        } else {
            $('#confirm').addClass("disabled");
        }
    })    
});
