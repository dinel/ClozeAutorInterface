/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var enable = [];
var participant  = [];

$( document ).ready(function() {
    $('#lang-known-container').hide();
    $('#reading-container').hide();
    
    $('#add-language').click(function() {
        $('#list-langs').append('<span class="lang-known">' + $('#language_spoken').val() 
                + ' (' + $('#level option:checked').val() + ')</span>');
        $('#level :nth-child(1)').prop('selected', true);
        $('#add-language').addClass("disabled");
        $('#language_spoken').val("");
    });
    
    $('#lang-known-container').on('click', '.lang-known', function() {
        $(this).remove();
    });
    
    $('#other_lang_yes').click( function() {
        enable[8] = 1;
        $('#lang-known-container').show();
        check_enable();
    });
    
    $('#other_lang_no').click( function() {
        enable[8] = 1;
        $('#lang-known-container').hide();
        check_enable();
    });
    
    $('#read_yes').click(function() {
        enable[9] = 1;
        participant["read"] = "yes";
        $('#reading-container').show();
        check_enable();
    });
    
    $('#read_no').click(function() {
        enable[9] = 1;
        participant["read"] = "no";
        $('#reading-container').hide();
        check_enable();
    });
    
    $('#participant_name').change(function() {
        enable[0] = 1;
        participant["name"] = $('#participant_name').val();
        check_enable();
    });   
    
    $('#participant_email').change(function() {
        enable[1] = 1;
        participant["email"] = $('#participant_email').val();
        check_enable();
    });  
    
    $('#participant_age').change(function() {
        enable[2] = 1;
        participant["age"] = $('#participant_age').val();
        check_enable();
    }); 
    
    $('#participant_years_edu').change(function() {
        enable[3] = 1;
        participant["year_edu"] = $('#participant_years_edu').val();
        check_enable();
    });
    
    $('input:radio[name=asd]').change(function() {
        enable[4] = 1;
        participant["asd"] = $(this).val();
        check_enable();
    });
    
    $('input:radio[name=dys]').change(function() {
        enable[5] = 1;
        participant["dys"] = $(this).val();
        check_enable();
    });
    
    $('input:radio[name=aph]').change(function() {
        enable[6] = 1;
        participant["aph"] = $(this).val();
        check_enable();
    });
    
    $('input:radio[name=en_nat]').change(function() {
        enable[7] = 1;
        participant["en_nat"] = $(this).val();
        check_enable();
    });
    
    $('#level').change(function() {
        if($('#language_spoken').val()) {
            $('#add-language').removeClass("disabled");
        }
    });
    
    $('#language_spoken').change(function() {
        if($('#level').prop('selectedIndex')) {
            $('#add-language').removeClass("disabled");
        }
    });
    
    $('input:radio[name=employed]').change(function() {
        enable[10] = 1;
        participant["employed"] = $(this).val();
        check_enable();
    });
    
    $('input:radio[name=past_employed]').change(function() {
        enable[11] = 1;
        participant["past_employed"] = $(this).val();
        check_enable();
    });
    
    $('#submit-questionnaire').click(function () {
        var participant_info = "";
        participant_info += "Name: " + participant["name"] + "\n";
        participant_info += "Email: " + participant["email"] + "\n";
        participant_info += "Age: " + participant["age"] + "\n";
        participant_info += "Years in education: " + participant["year_edu"] + "\n";
        participant_info += "ASD: " + participant["asd"] + "\n";
        participant_info += "Dyslexia: " + participant["dys"] + "\n";
        participant_info += "Aphasia: " + participant["aph"] + "\n";
        participant_info += "Other conditions: " + $('#other-conditions').val() + "\n";
        participant_info += "Diagnosis date: " + $('#diag-date').val() + "\n";
        participant_info += "English native: " + participant["en_nat"] + "\n";
        participant_info += "Other languages: " + $('#list-langs').text() + "\n";
        if(participant["read"] === "no") {
            participant_info += "Reading: no \n";
        } else {
            participant_info += "Reading: " + $('#freq option:checked').val() + "\n";
        }
        participant_info += "Current employment: " + participant["employed"]+ "\n";
        participant_info += "Past employment: " + participant["past_employed"]+ "\n";        
        
        options = {
            data: participant_info,                             // input as String (or Uint8Array)
            publicKeys: openpgp.key.readArmored(publicKeyString).keys  // for encryption
        };

        openpgp.encrypt(options).then(function(ciphertext) {
            var encrypted = ciphertext.data; // '-----BEGIN PGP MESSAGE ... END PGP MESSAGE-----'
            
            var params = [];
            params["encrypted"] = encrypted;
            params['clear'] = "";
            post("/save_details", params, "POST");
        });       
    });
});

function check_enable() {
    var sum = enable.reduce(function(sum, value) {
        return sum + value;
    }, 0);
    
    if(sum === 12) {
        $('#submit-questionnaire').removeClass("disabled");
    } 
}