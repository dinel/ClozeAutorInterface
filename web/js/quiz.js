/* 
 * Copyright 2017 dinel.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/* global idQuiz */

var questions_answered = [];

$( document ).ready(function() {
    $('#btn-instructions').click(function() {
        $('#instructions').hide();
        if(prereading === 1) {
            $('#prereading').show();
            logAction("Opened the prereading questions for " + idQuiz);
        } else {
            logAction("Opened the text for " + idQuiz);
            $('#text').show();
        }
    });
    
    $('#btn-prereading').click(function() {
        $('#prereading').hide();
        logAction("Opened the text for " + idQuiz);
        $('#text').show();
    });
    
    $('#btn-text').click(function() {
        $('#btn-text').hide();
        logAction("Started rating " + idQuiz);
        $('#rating').show();
    });
    
    $('#btn-rate').click(function() {
        $('#rating').hide();
        logAction("Started answering MCQ " + idQuiz);
        $('#mcq').show();
    });
    
    $('#text-difficulty').change(function() {
        logAction("Rated text as: " + $('#text-difficulty option:checked').val());
        $('#btn-rate').removeClass("disabled");
    });
    
    $('#btn-submit-mcq').click(function() {
        logAction("Finished with quiz " + idQuiz);
    });
    
    $('.open-answer').change(function () {
        var name = $(this).attr('name');
        logAction("Answer for question " + name + " is " + $(this).val());
    });
    
    $('.answer').click(function() {        
        var name = $(this).attr('name');
        logAction("Selected answer " + $(this).attr("value") + " for question " + name);
        opts_sel = $('input:checkbox[name=' + name + ']:checked').map(function() { return this.value; }).get();
        if(opts_sel.length === parseInt($(this).data('correct'))) {
            opts = $('input:checkbox[name=' + name + ']').map(function() { return this.value; }).get();
            for(var i = 0; i < opts.length; i++) {
                if(! $('#'+opts[i]).is(':checked')) {
                    $('#'+opts[i]).prop("disabled", true);
                }
            }
            $('#help-' + name).hide();
            $('#helpchange-' + name).show();
            questions_answered.push(name);
            enable_buttons();
        }
        
        if(opts_sel.length < parseInt($(this).data('correct'))) {
            opts = $('input:checkbox[name=' + name + ']').map(function() { return this.value; }).get();
            for(var i = 0; i < opts.length; i++) {
                $('#'+opts[i]).prop("disabled", false);
            }
            $('#help-' + name).show();
            $('#helpchange-' + name).hide();
            
            // remove element from list
            var index = questions_answered.indexOf(name);
            if(index > -1) {
                questions_answered.splice(index, 1);
            }
            
            enable_buttons();
        }
    });
    
    logAction("Opened instructions for ID:" + idQuiz);
});

function enable_buttons() {
    if(questions_answered.length > 3 * prereading) {
        $('#btn-prereading').removeClass('disabled');
    } else {
        $('#btn-prereading').addClass('disabled');
    }
    
    if(questions_answered.length > 4 * prereading + 7) {
        $('#btn-submit-mcq').removeClass('disabled');
    } else {
        $('#btn-submit-mcq').addClass('disabled');
    }
}

function logAction(message) {
    var d = new Date();
    
    $.ajax({
       type: 'POST',
       url: '/log_action',
       data: {
           'timeMiliseconds': d.getTime(),
           'timeClear': d.toString(),
           'message': message
       },
       success: function(msg) {
       }
    });
}