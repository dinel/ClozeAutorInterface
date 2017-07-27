/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( document ).ready(function() {
    $('#lang-known-container').hide();
    $('#reading-container').hide();
    
    $('#add-language').click(function() {
        $('#list-langs').append('<span class="lang-known">' + $('#language_spoken').val() 
                + ' (' + $('#level option:checked').val() + ')</span>');                
    });
    
    $('#lang-known-container').on('click', '.lang-known', function() {
        $(this).remove();
    });
    
    $('#other_lang_yes').click( function() {
        $('#lang-known-container').show();
    });
    
    $('#other_lang_no').click( function() {
        $('#lang-known-container').hide();
    });
    
    $('#read_yes').click(function() {
        $('#reading-container').show();
    });
    
    $('#read_no').click(function() {
        $('#reading-container').hide();
    })
    
})