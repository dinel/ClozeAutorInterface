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

$( document ).ready(function() {
    $('#select-voucher').change(function() {
        $('#submit-option').removeClass('disabled');
    });
    
    $('#submit-option').click(function() {
        $.ajax({
            type: 'POST',
            url: '/save-voucher',
            data: {
                'voucher': $('#select-voucher option:checked').val(),
            },
            success: function(msg) {
                $('#div-voucher').hide();
                $('#div-end').show();
            }
        });        
    });
    
    $('#contact-future').click(function() {
        var contact = 0;
        if($('#contact-future').is(':checked')) {
            contact = 1;
        }
        
        $.ajax({
            type: 'POST',
            url: '/save-contact',
            data: {
                'value': contact,
            },
            success: function(msg) {
            }
        });        
    })
});