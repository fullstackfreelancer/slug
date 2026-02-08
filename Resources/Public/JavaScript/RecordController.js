import AjaxRequest from "@typo3/core/ajax/ajax-request.js";
import { SlugHelper } from '@kohlercode/slug/Classes/SlugHelper.js';

// 1. Fetch the configuration from the DOM
const container = document.getElementById('slug-list-wrap');

// 2. Extract the values (with fallbacks just in case)
const recordTable = container.dataset.table || 'pages';
const titleField = container.dataset.titleField || 'title';
const slugField = container.dataset.slugField || 'slug';
const pid = container.dataset.pid || 0;

SlugHelper.initFilterFields(loadHTML,recordTable,'slug_record');
SlugHelper.loadList(recordTable,titleField,slugField,0,null,loadHTML,'slug_record',pid);

function loadHTML(responseText) {
    document.getElementById('slug-list-wrap').innerHTML = responseText;
    initInterface();
    console.log('initInterface: success');
}

function initInterface(){
    const rows = document.querySelectorAll('.slug-record');
    const button_generate_all = document.getElementById('btn-generate-all');
    const button_save_all = document.getElementById('btn-save-all');
    rows.forEach((row, i) => {
        const slug_input = row.querySelector('.slug-input');
        const slug_preview = row.querySelector('.slug-preview');
        const button_save = row.querySelector('.btn-save');
        const button_generate = row.querySelector('.btn-generate');
        const button_info = row.querySelector('.btn-info');
        const uid = row.getAttribute('data-record');
        const sitePrefix = row.getAttribute('data-siteprefix');
        const record_title = row.querySelector('.record-title');
        const table = row.getAttribute('data-table');
        const slugField = row.getAttribute('data-slug-field');

        record_title.addEventListener('dblclick', function () {
            record_title.setAttribute('contenteditable', 'true');
            record_title.setAttribute('data-originaltext',record_title.textContent);
            record_title.focus();
        });

        record_title.addEventListener('blur', function(){
            record_title.setAttribute('contenteditable', 'false');
            SlugHelper.updateRecordTitle(record_title);
        });

        if(button_save){
            button_save.addEventListener('click',function(e){
                const slug = row.querySelector('input[type="text"]').value;
                SlugHelper.saveSlug(uid,slug,sitePrefix,table,slugField);
            });
        }

        if(button_generate){
            button_generate.addEventListener('click',function(e){
                console.log([uid,sitePrefix,table]);
                SlugHelper.generateSlug(uid,sitePrefix,table);
            });
        }

        if(button_info){
            button_info.addEventListener('click',function(e){
                SlugHelper.loadSlugInfo(uid,table);
            });
        }

        if(slug_input){
            slug_input.addEventListener('input',function(e){
                slug_preview.innerHTML = sitePrefix + slug_input.value;
                SlugHelper.updateGooglePreviewUrl(sitePrefix + slug_input.value,uid);
            });
        }

    });

    document.querySelectorAll('.slug-input').forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('[id^="record-"]');
            if (row) {
                row.classList.add('not-saved');
            }
        });
    });

    button_save_all.addEventListener('click',function(e){
        SlugHelper.saveAllSlugs();
    });

    button_generate_all.addEventListener('click',function(e){
        SlugHelper.generateAllSlugs();
    });
}
