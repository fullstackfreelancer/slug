class SlugHelper{

    // Loads any kind of record that has a title and a slug field
    loadList(table,titleField,slugField,page,filterField = null,callbackFunction = null){
        let orderby = filterField !== null ? (filterField.orderby).value : 'crdate'
        let order = filterField !== null ? filterField.order.value : 'DESC'
        let maxentries = filterField !== null ? filterField.maxentries.value : '10'
        let key = filterField !== null ? filterField.key.value : ''

        let url = top.TYPO3.settings.ajaxUrls['slug_list']+'&table='+table+'&page='+page+'&orderby='+orderby+'&order='+order+'&maxentries='+maxentries+'&key='+key;
        let req = new XMLHttpRequest();
        let target = document.getElementById('slug-list-wrap');
        let output = '<div class="container">';
        target.innerHTML = this.preloader();
        req.open("GET", url, true);
        req.setRequestHeader("Content-type", "application/json; charset=utf-8");
        req.onreadystatechange = function() {
            if(req.readyState === 4) {
                if(req.status == 200) {
                    let records = JSON.parse(req.responseText)
                    if(callbackFunction !== null){
                        callbackFunction(records)
                    }
                }
            }
        }
        req.send();
    }

    initFilterFields(callbackFunction,recordTable){

        let filterField = {
            'key': document.getElementById('filter_key'),
            'maxentries': document.getElementById('filter_maxentries'),
            'orderby': document.getElementById('filter_orderby'),
            'order': document.getElementById('filter_order'),
            'status': document.getElementById('filter_status')
        }

        filterField.maxentries.addEventListener('change',function(e){
            this.loadList(recordTable,'title','slug',0,filterField,callbackFunction);
        }.bind(this));

        filterField.key.addEventListener('input',function(e){
            this.loadList(recordTable,'title','slug',0,filterField,callbackFunction);
        }.bind(this));

        filterField.orderby.addEventListener('change',function(e){
            this.loadList(recordTable,'title','slug',0,filterField,callbackFunction);
        }.bind(this));

        filterField.order.addEventListener('change',function(e){
            this.loadList(recordTable,'title','slug',0,filterField,callbackFunction);
        }.bind(this));

        filterField.status.addEventListener('change',function(e){
            this.loadList(recordTable,'title','slug',0,filterField,callbackFunction);
        }.bind(this));
    }

    preloader(){
        return '<div class="d-flex justify-content-center mb-4"><span class="icon icon-size-large icon-state-default icon-spin"><span class="icon-markup"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><g fill="#212121"><path d="M8 15c-3.86 0-7-3.141-7-7 0-3.86 3.14-7 7-7 3.859 0 7 3.14 7 7 0 3.859-3.141 7-7 7zM8 3C5.243 3 3 5.243 3 8s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" opacity=".3"/><path d="M14 9a1 1 0 0 1-1-1c0-2.757-2.243-5-5-5a1 1 0 0 1 0-2c3.859 0 7 3.14 7 7a1 1 0 0 1-1 1z"/></g></svg></span></span></div>';
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

    generate(uid, sitePrefix, recordTable = 'page', titleField = 'title', slugField = 'slug'){
        let url = '';
        switch (recordTable) {
            case 'page':
                url = `${TYPO3.settings.ajaxUrls['slug_generate']}&uid=${uid}`;
                break;
            default:
                url = `${TYPO3.settings.ajaxUrls['slug_generate_record']}&uid=${uid}&table=${recordTable}&slugField=${slugField}&titleField=${titleField}`;
        }
        const req = new XMLHttpRequest();
        req.open("GET", url, true);
        req.setRequestHeader("Content-type", "application/json; charset=utf-8");
        req.onreadystatechange = () => {
            if (req.readyState === 4) {
                if (req.status === 200) {
                    try {
                        const response = JSON.parse(req.responseText);
                        this.generateResponse(response)
                        this.updateGooglePreviewUrl(sitePrefix + response.slug,uid)
                    } catch (error) {
                        top.TYPO3.Notification.error('JSON Parsing Error', error.message);
                    }
                } else {
                    top.TYPO3.Notification.error('Ajax Error', `${req.statusText}`);
                }
            }
        };
        req.send()
    }

    generateResponse(response){
        console.log(response)
        if (response.slug) {
            //slugInputField.value = response.slug
            //slugInputField.classList.add('not-saved')
            top.TYPO3.Notification.success('success!!', response.slug)
        } else {
            top.TYPO3.Notification.info('nothing changed...', response.slug)
        }
        //slugInputField.removeAttribute('disabled')
    }

}
