if (document.readyState !== 'loading') {
    console.log('document is already ready, just execute code here');
    myInitCode();
} else {
    window.addEventListener('DOMContentLoaded', function () {
        console.log('document was not ready, place code here');
        myInitCode();
    });
}

function myInitCode() {

    let page = new SlugPage()
    let slugHelper = new SlugHelper()
    let generateLinks = document.querySelectorAll('[data-slug-generate]')
    if(generateLinks) generateLinks.forEach((link, i) => {
        link.addEventListener('click',function(e){
            //top.TYPO3.Modal.confirm('The title of the modal', 'This the the body of the modal');
            let pageUid = link.getAttribute('data-slug-generate')
            page.generateSlug(pageUid,'https://localhost/_typo3_development')
            e.preventDefault()
        })
    });

}
