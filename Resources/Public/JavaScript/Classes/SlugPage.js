class SlugPage{

    saveSlug(uid,slug,sitePrefix,type){
        let url = TYPO3.settings.ajaxUrls['slug_save_page']+'&slug='+slug+'&uid='+uid;
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

    generateSlug(uid, sitePrefix){

        const url = `${TYPO3.settings.ajaxUrls['slug_generate']}&uid=${uid}`;
        console.log(url)

        const req = new XMLHttpRequest();
        const slugInputField = document.getElementById('slug-input');
        const slugPreview = document.getElementById('slug-preview');
        const slugValue = slugInputField.value;

        slugInputField.setAttribute('disabled', 'disabled');
        req.open("GET", url, true);
        req.setRequestHeader("Content-type", "application/json; charset=utf-8");
        req.onreadystatechange = () => {
            if (req.readyState === 4) {
                if (req.status === 200) {
                    try {
                        const response = JSON.parse(req.responseText);
                        this.handleResponse(response, slugInputField, slugPreview, sitePrefix);
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

    handleResponse(response, slugInputField, slugPreview, sitePrefix){
        if (response.slug !== slugInputField.value) {
            slugPreview.innerHTML = `${sitePrefix}${response.slug}`
            slugInputField.value = response.slug
            slugInputField.classList.add('not-saved')
            top.TYPO3.Notification.success('success!!', response.slug)
        } else {
            top.TYPO3.Notification.info('nothing changed...', response.slug)
        }
        slugInputField.removeAttribute('disabled')
    }

    slugInfo(uid,type){
        let url = TYPO3.settings.ajaxUrls['slugInfo']+'&type='+type+'&uid='+uid;
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

}
