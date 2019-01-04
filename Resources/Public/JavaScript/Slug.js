/* 
 * This file was created by Simon Köhler
 * at GOCHILLA s.a.
 * www.gochilla.com
 */

jQuery(document).ready(function(){
    
    // Checks, if a slug already exists
    function slugExists(slug){
        $.ajax({
            url: TYPO3.settings.ajaxUrls['slugExists'],
            method: 'GET',
            dataType: 'html',
            data: { 
                slug : slug
            },
            success: function(response) {
                if(response === '1'){
                    top.TYPO3.Notification.info('', slugNotes['notes.info.slugexists']);
                }
            },
            fail: function(response){
                top.TYPO3.Notification.error('Ajax Fail', slugNotes['notes.error.ajax'] + '' + response.statusText);
                console.log("jQuery Ajax: " + response.statusText);
            },
            error: function(response){
                top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + response.statusText);
                console.log("jQuery Ajax: " + response.statusText);
            }
        });
    }
    
    // Generates a single page slug and puts it into the slug text input field with the same id
    function generatePageSlug(uid){
        $('#generatePageSlug-'+uid).prop('disabled', true);
        $.ajax({
            url: TYPO3.settings.ajaxUrls['generatePageSlug'],
            method: 'GET',
            dataType: 'html',
            data: { 
                uid : uid
            },
            success: function(response) {
                $('#generatePageSlug-'+uid).prop('disabled', false);
                $('.slug-input.page-'+uid).val(response);
            },
            fail: function(response){
                top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + response.statusText);
                console.log("jQuery Ajax: " + response.statusText);
            },
            error: function(response){
                top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + response.statusText);
                console.log("jQuery Ajax: " + response.statusText);
            }
        });
    }
    
    // Saves a single page slug and refreshes all dynamic slug containers
    function savePageSlug(slug,field,uid,btn){
        btn.prop('disabled', true);
        field.prop('disabled', true);                
        $.ajax({
            url: TYPO3.settings.ajaxUrls['savePageSlug'],
            method: 'GET',
            dataType: 'html',
            data: { 
                uid : uid, 
                slug : slug
            },
            success: function(response) {
                if(response === '1'){
                    top.TYPO3.Notification.success(slugNotes['notes.success.saved'], slug);
                }
                else{
                    top.TYPO3.Notification.info(slugNotes['notes.info.nochanges'], slug);
                }
                btn.prop('disabled', false);
                field.prop('disabled', false);
                field.removeClass('has-been-changed');
                field.attr('value',slug);
                $('.dynamic-slug-'+uid).find('.slug').html(slug);
                $('.dynamic-slug-'+uid).attr('href', $('.dynamic-slug-'+uid).data('baselink')+slug);
            },
            fail: function(response){
                top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + response.statusText);
                console.log("jQuery Ajax: " + response.statusText);
            },
            error: function(response){
                top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + response.statusText);
                console.log("jQuery Ajax: " + response.statusText);
            }
        });
        
    }
    
    // Generates a single news slug and puts it into the slug text input field
    function generateNewsSlug(uid){
        $('#generateNewsSlug-'+uid).prop('disabled', true);                
        $.ajax({
            url: TYPO3.settings.ajaxUrls['generateNewsSlug'],
            method: 'GET',
            dataType: 'html',
            data: {
                uid: uid
            },
            success: function(response) {
                $('#generateNewsSlug-'+uid).prop('disabled', false);
                $('.slug-input-news.news-'+uid).val(response);
            },
            fail: function(response){
                top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + response.statusText);
                console.log("jQuery Ajax: " + response.statusText);
            },
            error: function(response){
                top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + response.statusText);
                console.log("jQuery Ajax: " + response.statusText);
            }
        });
    }
    
    // Saves a single news record slug
    function saveNewsSlug(slug,field,uid,btn){
        btn.prop('disabled', true);
        field.prop('disabled', true);            
        $.ajax({
            url: TYPO3.settings.ajaxUrls['saveNewsSlug'],
            method: 'GET',
            dataType: 'html',
            data: { 
                uid : uid, 
                slug : slug
            },
            success: function(response) {
                if(response === '1'){
                    top.TYPO3.Notification.success(slugNotes['notes.success.saved'], slug);
                }
                else{
                    top.TYPO3.Notification.info(slugNotes['notes.info.nochanges'], slug);
                }
                btn.prop('disabled', false);
                field.prop('disabled', false);
                field.removeClass('has-been-changed');
                field.attr('value',slug);
            },
            fail: function(response){
                top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + response.statusText);
                console.log("jQuery Ajax: " + response.statusText);
            },
            error: function(response){
                top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + response.statusText);
                console.log("jQuery Ajax: " + response.statusText);
            }
        });
        
    }
    
    function updateDynamicPageUrls(uid,slug){
        $('.dynamic-slug-'+uid).find('.slug').html(slug);
    }
    
    $('button.generateAllNewsSlugs').on({
        click: function(){
            var timer = 0;
            $(this,'button.saveAllNewsSlugs').prop('disabled', true);
            $('button.generateNewsSlug').each(function(){
                var btn = $(this);
                setTimeout(function() {
                    btn.trigger('click');
                }, (timer*1000));
                timer++;
            });
            setTimeout(function() {
                $('button.generateAllNewsSlugs','button.saveAllNewsSlugs').prop('disabled', false);
            }, (timer*1000));
        }
    });
    
    $('button.saveAllNewsSlugs').on({
        click: function(){
            var timer = 0;
            $(this).prop('disabled', true);
            $('button.saveNewsSlug').each(function(){
                var btn = $(this);
                setTimeout(function() {
                    btn.trigger('click');
                }, (timer*1000));
                timer++;
            });
            setTimeout(function() {
                $('button.saveAllNewsSlugs').prop('disabled', false);
            }, (timer*1000));
        }
    });
        
    $('button.savePageSlug').on({
        click: function(){
            uid = $(this).data('uid');
            slugInputField = $('.slug-input.page-'+uid);
            slugInputValue = slugInputField.val();
            if(slugInputValue.trim().substr(0, 1) !== "/"){
                top.TYPO3.Notification.error('Missing Slash!', 'Please enter a "/" at the beginning of the slug!');
            }
            else if(slugInputValue.trim() === ''){
                top.TYPO3.Notification.error('', slugNotes['notes.error.noslug']);
            }
            else if(slugInputValue.trim() === '/'){
                if(confirm(slugNotes['notes.confirm.slashonly']) === false){
                    top.TYPO3.Notification.info('Action aborted!', 'Maybe this was a good decision...');
                }
                else{
                    savePageSlug(slugInputValue,slugInputField,uid,$(this));
                }
            }
            else{
                savePageSlug(slugInputValue,slugInputField,uid,$(this));
            }
        }        
    });
    
    $('button.saveNewsSlug').on({
        click: function(){            
            uid = $(this).data('uid');
            slugInputField = $('.slug-input-news.news-'+uid);
            slugInputValue = slugInputField.val();
            if(slugInputValue.trim() === ''){
                top.TYPO3.Notification.error('', slugNotes['notes.error.noslug']);
            }
            else if(slugInputValue.trim() === '/'){
                if(confirm(slugNotes['notes.confirm.slashonly']) === false){
                    top.TYPO3.Notification.info('Action aborted!', 'Maybe this was a good decision...');
                }
                else{
                    saveNewsSlug(slugInputValue,slugInputField,uid,$(this));
                }
            }
            else{
                saveNewsSlug(slugInputValue,slugInputField,uid,$(this));
            }
        }        
    });
    
    $('button.generatePageSlug').on({
        click: function(){
            generatePageSlug($(this).data('uid'));
        }
    });
    
    $('button.generateNewsSlug').on({
        click: function(){
            generateNewsSlug($(this).data('uid'));
        }
    });
    
    $('button.slugInfo').on({
        click: function(){
            var uid = $(this).data('uid');
            $('.record-info-container[data-uid="'+uid+'"]').parent().parent().toggleClass('visible');
        }
    });
    
    $('input.slug-input').change(function(){
        $(this).addClass('has-been-changed');
        slugExists($(this).val());
    });
    
    // Update all dynamic URLs when the slug textfield has been changed
    $('input.slug-input').keyup(function(){
        updateDynamicPageUrls($(this).data('uid'),$(this).val());
    });
    
});