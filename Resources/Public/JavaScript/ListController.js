document.addEventListener("DOMContentLoaded", function() {

    let titleField = 'title'
    let slugField = 'slug'
    let recordTable = 'pages'

    const slugHelper = new SlugHelper()
          slugHelper.initFilterFields(buildHTML,recordTable)
          slugHelper.loadList(recordTable,titleField,slugField,0,null,buildHTML)

    function buildHTML(records){

        let target = document.getElementById('slug-list-wrap');
        let output = '<div class="container">';

        for (var i = 0; i < records.length; i++) {
            let title = records[i][titleField];
            let slug = records[i][slugField];
            let sitePrefix = records[i]['sitePrefix'];
            let fullUrl = sitePrefix + slug;
            let disabledAttribute = records[i]['tx_slug_locked'] ? 'disabled' : '';
            output += '<div id="record-'+records[i]['uid']+'" data-siteprefix="'+sitePrefix+'" data-record="'+records[i]['uid']+'" class="slug-record row mb-2 border-bottom">';
            output += '<div class="col py-1">';
                output += '<div>';
                output += '<h5 class="slug-title"><i class="bi bi-'+slugHelper.getPageIconByType(records[i]['doktype'],records[i]['is_siteroot'])+' fa-fw" title="id='+records[i]['uid']+'"></i> '+title+'</h5>';
                output += '<a href="'+fullUrl+'" target="_blank" class="slug-preview">'+ fullUrl + '</a>';
                output += '</div>';
            output += '</div>';
            output += '<div class="col py-1">';
                output += '<div class="input-group"><span class="input-group-addon">'+records[i]['sys_language_uid']+'</span><input type="text" class="form-control slug-input" value="'+slug+'" '+disabledAttribute+'/></div>';
            output += '</div>';
            output += '<div class="col-sm-2 py-1 d-flex justify-content-end">';
                if(records[i]['tx_slug_locked'] === 1){
                    output += '<div class="button-group ml-auto"><a class="btn btn-danger btn-sm" title="locked"><i class="bi bi-lock"></i></a><a class="btn btn-default btn-sm btn-info"><i class="bi bi-info-circle"></i></a></div>';
                }
                else{
                    output += '<div class="button-group ml-auto"><a class="btn btn-default btn-sm btn-save"><i class="bi bi-save"></i></a><a class="btn btn-default btn-sm btn-generate"><i class="bi bi-arrow-repeat"></i></a><a class="btn btn-default btn-sm btn-info"><i class="bi bi-info-circle"></i></a></div>';
                }
            output += '</div>';
            output += '<div class="info-container"></div>';
            output += '</div>';
        }
        target.innerHTML = output + '</div>';
        initInterface();
    }

    function initInterface(){
        let rows = document.querySelectorAll('.slug-record');
        rows.forEach((row, i) => {
            let slug_input = row.querySelector('.slug-input');
            let slug_preview = row.querySelector('.slug-preview');
            let button_save = row.querySelector('.btn-save');
            let button_generate = row.querySelector('.btn-generate');
            let button_info = row.querySelector('.btn-info');
            let uid = row.getAttribute('data-record');
            let sitePrefix = row.getAttribute('data-siteprefix');

            if(button_save){
                button_save.addEventListener('click',function(e){
                    let slug = row.querySelector('input[type="text"]').value;
                    slugHelper.save(uid,slug,sitePrefix,'page');
                });
            }

            if(button_generate){
                button_generate.addEventListener('click',function(e){
                    slugHelper.generate(uid,sitePrefix,'page');
                });
            }

            if(button_info){
                button_info.addEventListener('click',function(e){
                    slugHelper.slugInfo(uid,'page');
                });
            }

            if(slug_input){
                slug_input.addEventListener('input',function(e){
                    slug_preview.innerHTML = sitePrefix + slug_input.value;
                    slugHelper.updateGooglePreviewUrl(sitePrefix + slug_input.value,uid);
                });
            }

            // Find PRO Scripts and initialize functions
            if(typeof SLUGPRO !== 'undefined'){
                SLUGPRO.makeSlugTitlesEditable();
            }

        });
    }

});
