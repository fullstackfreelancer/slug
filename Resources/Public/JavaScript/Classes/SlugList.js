class SlugList{

    preloader(){
        return '<div class="d-flex justify-content-center mb-4">Loading...</div>';
    }

    save(uid,slug,sitePrefix,type){
        let url = TYPO3.settings.ajaxUrls['savePageSlug']+'&slug='+slug+'&uid='+uid;
        let req = new XMLHttpRequest();
        let slugRow = document.getElementById('record-'+uid);
        let slugInputField = slugRow.querySelector('.slug-input');
        let slugPreview = slugRow.querySelector('.slug-preview');
        slugInputField.setAttribute('disabled','disabled');

        req.open("GET", url, true);
        req.setRequestHeader("Content-type", "application/json; charset=utf-8");
        req.onreadystatechange = function() {
            if(req.readyState === 4) {
                if(req.status == 200) {
                    let response = JSON.parse(req.responseText);
                    if(response.status === '1'){
                        top.TYPO3.Notification.success(slugNotes['notes.success.saved'], response.slug);
                        slugRow.classList.remove('not-saved');
                        slugPreview.setAttribute('href',sitePrefix + response.slug);
                    }
                    else{
                        top.TYPO3.Notification.info(slugNotes['notes.info.nochanges'], response.slug);
                    }
                    slugInputField.removeAttribute('disabled');
                }
                else{
                    top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + req.statusText);
                }
            }
        }
        req.send();
    }

    generate(uid, sitePrefix){

        const url = `${TYPO3.settings.ajaxUrls['slug_generate']}&uid=${uid}`;
        const req = new XMLHttpRequest();
        req.open("GET", url, true);
        req.setRequestHeader("Content-type", "application/json; charset=utf-8");
        req.onreadystatechange = () => {
            if (req.readyState === 4) {
                if (req.status === 200) {
                    try {
                        const response = JSON.parse(req.responseText);
                        this.handleResponse(response)
                        this.updateGooglePreviewUrl(sitePrefix + response.slug,uid)
                    } catch (error) {
                        top.TYPO3.Notification.error('JSON Parsing Error', error.message);
                    }
                } else {
                    top.TYPO3.Notification.error('Ajax Error', `${slugNotes['notes.error.ajax']} ${req.statusText}`);
                }
            }
        };
        req.send()
    };

    handleResponse(response){
        if (response.slug) {
            //slugInputField.value = response.slug
            //slugInputField.classList.add('not-saved')

            top.TYPO3.Notification.success('success!!', response.slug)
        } else {
            top.TYPO3.Notification.info('nothing changed...', response.slug)
        }
        //slugInputField.removeAttribute('disabled')
    }

    getPageIconByType(doktype,isroot){
        if(isroot === 1){
            return 'globe text-primary';
        }
        else{
            switch(doktype) {
                case 3:
                    return 'link';
                break;
                case 199:
                    return 'minus';
                break;
                case 254:
                    return 'folder';
                break;
                case 4:
                    return 'link';
                break;
                default:
                    return 'file';
            }
        }
    }

    slugInfo(uid,type){
        let url = TYPO3.settings.ajaxUrls['slug_info']+'&type='+type+'&uid='+uid;
        let req = new XMLHttpRequest();
        let slugRow = document.getElementById('record-'+uid);
        let infoContainer = slugRow.querySelector('.info-container');

        // Preloader spinner
        infoContainer.innerHTML = this.preloader();

        req.open("GET", url, true);
        req.setRequestHeader("Content-type", "application/json; charset=utf-8");
        req.onreadystatechange = function() {
            if(req.readyState === 4) {
                if(req.status == 200) {
                    if(req.responseText){
                        infoContainer.innerHTML = req.responseText;
                        let closeButton = infoContainer.querySelector('a.close');
                        closeButton.addEventListener('click',function(){
                            document.getElementById('record-'+uid).querySelector('.info-container').innerHTML = '';
                        });
                    }
                    else{
                        top.TYPO3.Notification.info(slugNotes['notes.info.nochanges'], response.slug);
                    }
                }
                else{
                    top.TYPO3.Notification.error('Ajax Error', slugNotes['notes.error.ajax'] + '' + req.statusText);
                }
            }
        }
        req.send();
    }

    updateGooglePreviewUrl(url,uid){
        let obj = document.querySelector('div[data-googleurl="'+uid+'"]');
        let obj_exists = obj !== null;
        if(obj_exists){ obj.innerHTML = url; }
    }

}
