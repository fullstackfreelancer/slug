export class SlugHelper{

    static loadList(table, titleField, slugField, page, filterFields = null, callbackFunction = null) {

        const orderby = filterFields !== null ? filterFields.orderby.value : 'crdate';
        const order = filterFields !== null ? filterFields.order.value : 'DESC';
        const maxentries = filterFields !== null ? filterFields.maxentries.value : '20';
        const key = filterFields !== null ? filterFields.key.value : '';
        const status = filterFields !== null ? filterFields.status.value : 'visible';

        const url = top.TYPO3.settings.ajaxUrls['slug_list'] +
                  //'&site=' + encodeURIComponent(site) +
                  '&table=' + encodeURIComponent(table) +
                  '&page=' + encodeURIComponent(page) +
                  '&orderby=' + encodeURIComponent(orderby) +
                  '&order=' + encodeURIComponent(order) +
                  '&maxentries=' + encodeURIComponent(maxentries) +
                  '&key=' + encodeURIComponent(key) +
                  '&status=' + encodeURIComponent(status);

        const req = new XMLHttpRequest();
        const target = document.getElementById('slug-list-wrap');
        target.innerHTML = this.preloader(); // Optional: keep if you want to show a loading indicator

        req.open("GET", url, true);
        req.setRequestHeader("Content-type", "text/html; charset=utf-8"); // changed content-type

        req.onreadystatechange = function () {
            if (req.readyState === 4) {
                if (req.status === 200) {
                    // Replace content with returned HTML instead of parsing JSON
                    if (callbackFunction !== null) {
                        callbackFunction(req.responseText); // HTML as string
                    } else {
                        target.innerHTML = req.responseText; // Directly inject HTML
                    }
                }
            }
        };
        req.send();
    }

    static initFilterFields(callbackFunction,recordTable){

        const filterFields = {
            'site': document.getElementById('filter_site'),
            'key': document.getElementById('filter_key'),
            'maxentries': document.getElementById('filter_maxentries'),
            'orderby': document.getElementById('filter_orderby'),
            'order': document.getElementById('filter_order'),
            'status': document.getElementById('filter_status')
        }

        // filterFields.site.addEventListener('change',function(e){
        //     this.loadList(recordTable,'title','slug',0,filterFields,callbackFunction);
        // }.bind(this));

        filterFields.maxentries.addEventListener('change',function(e){
            this.loadList(recordTable,'title','slug',0,filterFields,callbackFunction);
        }.bind(this));

        filterFields.key.addEventListener('input',function(e){
            this.loadList(recordTable,'title','slug',0,filterFields,callbackFunction);
        }.bind(this));

        filterFields.orderby.addEventListener('change',function(e){
            this.loadList(recordTable,'title','slug',0,filterFields,callbackFunction);
        }.bind(this));

        filterFields.order.addEventListener('change',function(e){
            this.loadList(recordTable,'title','slug',0,filterFields,callbackFunction);
        }.bind(this));

        filterFields.status.addEventListener('change',function(e){
            this.loadList(recordTable,'title','slug',0,filterFields,callbackFunction);
        }.bind(this));
    }

    static preloader(){
        return '<div class="d-flex justify-content-center mb-4">Loading...</div>';
    }

    static async saveSlug(uid, slug, sitePrefix, type) {
        const url = TYPO3.settings.ajaxUrls['slug_save_page'] + '&slug=' + encodeURIComponent(slug) + '&uid=' + uid;
        const slugRow = document.getElementById('record-' + uid);
        const slugInputField = slugRow.querySelector('.slug-input');
        const slugPreview = slugRow.querySelector('.slug-preview');

        slugInputField.setAttribute('disabled', 'disabled');

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json; charset=utf-8'
                }
            });

            if (!response.ok) {
                throw new Error(response.statusText);
            }

            const data = await response.json();

            if (data.status === '1') {
                top.TYPO3.Notification.success(window.slugNotes['notes.success.saved'], data.slug);
                slugRow.classList.remove('not-saved');
                slugPreview.setAttribute('href', sitePrefix + data.slug);
            } else {
                top.TYPO3.Notification.info(window.slugNotes['notes.info.nochanges'], data.slug);
            }
        } catch (error) {
            top.TYPO3.Notification.error('Ajax Error', window.slugNotes['notes.error.ajax'] + ' ' + error.message);
        } finally {
            slugInputField.removeAttribute('disabled');
        }
    }

    static async saveAllSlugs() {
        const allRecords = document.querySelectorAll('.slug-record.not-saved[data-locked="0"]');
        const saveButton = document.getElementById('btn-save-all');
        const statusContainer = document.getElementById('slug-status');

        if (allRecords.length === 0) {
            top.TYPO3.Notification.info('Hinweis', 'Keine Slugs gefunden.');
            return;
        }

        saveButton.setAttribute('disabled', 'disabled');
        saveButton.textContent = 'Speichern...';
        statusContainer.innerHTML = `<span style="color:#666;">Saving Slugs... (${allRecords.length})</span>`;

        let savedCount = 0;

        for (const record of allRecords) {
            const uid = record.id.replace('record-', '');
            const slugInput = record.querySelector('.slug-input');
            const slug = slugInput.value;
            const sitePrefix = record.dataset.sitePrefix || '';
            await this.saveSlug(uid, slug, sitePrefix, 'page');
            savedCount++;
        }

        statusContainer.innerHTML = `<span style="color:green;">✔ ${savedCount} Slugs saved</span>`;

        setTimeout(() => {
            statusContainer.innerHTML = '';
        }, 2000);

        saveButton.removeAttribute('disabled');
    }

    static async generateSlug(uid, sitePrefix, recordTable = 'page', titleField = 'title', slugField = 'slug') {
        let url = recordTable === 'page'
            ? `${TYPO3.settings.ajaxUrls['slug_generate']}&uid=${uid}`
            : `${TYPO3.settings.ajaxUrls['slug_generate_record']}&uid=${uid}&table=${recordTable}&slugField=${slugField}&titleField=${titleField}`;

        try {
            const res = await fetch(url);
            if (!res.ok) {
                throw new Error(res.statusText);
            }

            const response = await res.json();
            this.generateResponse(response, uid);
            this.updateGooglePreviewUrl(sitePrefix + response.slug, uid);

            const row = document.getElementById(`record-${uid}`);
            if (row) row.classList.add('not-saved');

        } catch (error) {
            top.TYPO3.Notification.error('Ajax Error', error.message);
        }

    }

    static async generateAllSlugs(){
        const allRecords = document.querySelectorAll('.slug-record[data-locked="0"]');
        const generateButton = document.getElementById('btn-generate-all');
        const statusContainer = document.getElementById('slug-status');
        let savedCount = 0;
        for (const record of allRecords) {
            const uid = record.id.replace('record-', '');
            const slugInput = record.querySelector('.slug-input');
            const slug = slugInput.value;
            const sitePrefix = record.dataset.sitePrefix || '';
            await this.generateSlug(uid, sitePrefix);
            savedCount++;
        }
        statusContainer.innerHTML = `<span style="color:green;">✔ ${savedCount} Slugs generated</span>`;
        setTimeout(() => {
            statusContainer.innerHTML = '';
        }, 2000);
    }

    static generateResponse(response,uid){
        let slugInputField = document.getElementById('slug-input-field-'+uid);
        if (response.slug) {
            slugInputField.value = response.slug
            top.TYPO3.Notification.success(window.slugNotes['notes.success.generated'], response.slug)
        } else {
            top.TYPO3.Notification.info(window.slugNotes['notes.info.nochanges'], response.slug)
        }
        slugInputField.removeAttribute('disabled')
    }

    static loadSlugInfo(uid,type){
        const url = TYPO3.settings.ajaxUrls['slug_info']+'&type='+type+'&uid='+uid;
        const req = new XMLHttpRequest();
        const slugRow = document.getElementById('record-'+uid);
        const infoContainer = slugRow.querySelector('.info-container');
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
                        top.TYPO3.Notification.info(window.slugNotes['notes.info.nochanges'], response.slug);
                    }
                }
                else{
                    top.TYPO3.Notification.error('Ajax Error', window.slugNotes['notes.error.ajax'] + '' + req.statusText);
                }
            }
        }
        req.send();
    }

    static updateGooglePreviewUrl(url,uid){
        let obj = document.querySelector('div[data-googleurl="'+uid+'"]');
        let obj_exists = obj !== null;
        if(obj_exists){ obj.innerHTML = url; }
    }

    static updatePageTitle(titleField){
        const newTitle = titleField.textContent;
        const pageUid = titleField.getAttribute('data-pageuid');
        const originalText = titleField.getAttribute('data-originaltext');

        if (newTitle === originalText.trim()) return;

        const params = new URLSearchParams({ title: newTitle, uid: pageUid });

        fetch(TYPO3.settings.ajaxUrls['slug_update_page_title'] + '&' + params.toString(), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success'){
                top.TYPO3.Notification.success('Page title updated', data.status);
            }
            else if (data.status === 'error') {
                top.TYPO3.Notification.error('Ooops...',  data.status);
            }
            titleField.setAttribute('contenteditable','false');
        })
        .catch(error => {
            top.TYPO3.Notification.error('Ooops...', error);
            titleField.setAttribute('contenteditable','false');
        });

    }

}
